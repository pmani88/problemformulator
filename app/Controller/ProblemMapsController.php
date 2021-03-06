<?php
App::uses('File', 'Utility');
require_once("../Config/database.php");
//require_once("http://localhost:8080/JavaBridge/java/Java.inc");

// Controller/ProblemMapsController.php
class ProblemMapRank
{
    public $id;
    public $decomposition_id;
    public $name;
    public $type;
    public $current_decomposition;
    public $problem_map_id;
    public $thelinks = array();
    public $children1 = array();
}

class ProblemMapsController extends AppController
{
    // used for XML and JSON output
    public $components = array(
        'RequestHandler',
        'Paginator'
    );

    public $paginate = array('limit' => 20);

    public $helpers = array('Html', 'Form', 'Csv');
//    public $helpers = array('Form', 'Html', 'xls');

    // models used
    public $uses = array(
        'ProblemMap',
        'LogEntry',
        'Entity',
        'Decomposition',
        'EntityTypeSubtype',
        'TutorialType',
        'TutorialPrompt',
        'ProcessReplays',
        'Perception',
        'CentroidScore',
        'ProblemSet',
        'JudgesScore'
    );

    // determine if the file extension is prolog and if so set the appropriate layout
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->RequestHandler->setContent('pl', 'text/pl');
    }

    // check if the user is authorized
    public function isAuthorized($user = NULL)
    {
        // if admin full access
        if (parent::isAuthorized($user)) {
            return true;
        }

        // users can view and edit their problem maps descriptions
        if (in_array($this->action, array(
            'index',
            'add'
        ))) {
            return true;
        }

        // only owner of the user can view and/or edit the problem maps contents
        if (in_array($this->action, array(
            'view',
            'edit',
            'delete',
            'view_list',
            'view_graph',
            'view_graphNew',
            'view_objtree',
            'print_objtree',
            'view_processreplay',
            'view_log',
            'getInvalidEntities',
            'pmap_score'
        ))) {
            $pmapId = $this->request->params['pass'][0];

            if ($this->ProblemMap->isOwnedBy($pmapId, $user['id'])) {
                return true;
            }
        }

        // users are allowed to access following methods
        if (in_array($this->action, array(
            'tutorial_prompts',
            'tutorial_switch',
            'tree_traversal_view_objtree',
            'getChildrenEntities',
            'getChildrenDecomps',
            'save_objtree_weights',
            'download_spec',
            'save_action',
            'save_perception',
            'reset_invalid_current_decomposition',
            'get_similarity',
            'calculate_pmap_skills',
            'calculate_raw_pmap_score',
            'manual_pmap_scoring'
        ))) {
            return true;
        }
    }

    // gets the invalid entities using ASP and the checker rules
    public function getInvalidEntities($id)
    {
        $ProblemMap = $this->ProblemMap->findById($id);

        // save problem map to view variables
        $this->set(compact('ProblemMap'));

        // and for XML / JSON display
        $this->set('_serialize', array(
            'ProblemMap'
        ));

        // create a new view
        $view = new View($this);

        // render the problem map as a prolog file
        $viewdata = $view->render('pl/view', 'pl/default', 'pl');

        //set the file name to save the View's output
        $path = WWW_ROOT . 'files/pl/' . $id . '.pl';
        $file = new File($path, true);

        //write the content to the file
        $file->write($viewdata);

        //echo 'clingo -n 0 ' . WWW_ROOT . 'files/pl/' . $id . '.pl' . " " . WWW_ROOT . 'pl/completeness_rules.pl';
        // call clingo on the file and get the invalid entities.

        //$invalid_string = shell_exec('clingo -n 0 ' . WWW_ROOT . 'files/pl/' . $id . '.pl' . " " . WWW_ROOT . 'pl/completeness_rules.pl' . " | grep 'invalid'");

        $invalid_string = shell_exec('ulimit -t 15; clingo -n 0 ' . WWW_ROOT . 'files/pl/' . $id . '.pl' . " " . WWW_ROOT . 'pl/completeness_rules.pl' . " | grep 'invalid'");

        //echo $invalid_string;
        // extract all the invalid ids

        $invalids = explode(" ", trim($invalid_string));

        // clean up the ids
        foreach ($invalids as & $i) {
            $i = ereg_replace("[^0-9]", "", $i);
        }

        //print_r($invalids);
        //return $invalids;

        // set the view variables

        $this->set(compact('invalids'));

        // and XML/JSON display
        $this->set('_serialize', array(
            'invalids'
        ));

        //echo WWW_ROOT;
        //$this->render('view', 'default', 'pl');

        //$this->render();
    }

    // list all the problem maps
    public function index()
    {
        // if admin find all problem maps
        if ($this->Auth->user('admin') == 1) {
            $this->set("admin", 1);
            $ProblemMaps = $this->ProblemMap->find('all', array('recursive' => 0));
        } else {
            $this->set("admin", 0);
            // get all the problem maps belonging to the user
            $ProblemMaps = $this->ProblemMap->find('all', array(
                'conditions' => array(
                    'ProblemMap.user_id' => $this->Auth->user('id')
                )
            ));
        }

        // set them in a variable accessible in the view
        $this->set(compact('ProblemMaps'));

        // save them in a format accessible from JSON / XML
        $this->set('_serialize', array(
            'ProblemMaps'
        ));
    }

    public function view_list($id)
    {
        $this->log_entry($id, "ProblemMapsController, view_list, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);

        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));

        /* Entity Subtypes - Start */
        $EntityTypeSubtype = $this->EntityTypeSubtype->find('all');
        $Entitytypes = $this->EntityTypeSubtype->find('all', array('fields' => array('DISTINCT EntityTypeSubtype.type')));
        $subtypes = [];
        foreach ($Entitytypes as $Entitytype) {
            $subtypes[$Entitytype['EntityTypeSubtype']['type']] = [];
        }
        foreach ($EntityTypeSubtype as $EntitySubtype) {
            if (!empty($EntitySubtype['EntityTypeSubtype']['subtype']))
                array_push($subtypes[$EntitySubtype['EntityTypeSubtype']['type']], $EntitySubtype['EntityTypeSubtype']['subtype']);
        }

        $this->set(compact('subtypes'));
        /* Entity Subtypes - End */
    }

    public function tutorial_prompts($step, $type)
    {
        $TutorialPrompt = $this->TutorialPrompt->find('first', array('conditions' => array('TutorialPrompt.step' => $step, 'TutorialPrompt.tutorial_type_id' => $type)));
        $this->set(compact('TutorialPrompt'));

        $neighbors = $this->TutorialPrompt->find('neighbors', array('field' => 'id', 'value' => $TutorialPrompt['TutorialPrompt']['id']));

        $prompt_html = '';
        $prompt_html .= '<h5>' . $TutorialPrompt['TutorialType']['name'] . ' for Formulating a Problem</h5>';
        $prompt_html .= '<div id="promptBox">';
        $prompt_html .= '<div id="promptMsg">';
        $prompt_html .= '<b>Step</b>: <span>' . $TutorialPrompt['TutorialPrompt']['description'] . '</span>';
        $prompt_html .= '<br><br>';

        $prompt_html .= '<span>';
        if (count($neighbors['prev']))
            $prompt_html .= '<button id="promptButton" class="navButton" onclick="tutorial_prompt(\'' . $neighbors['prev']['TutorialPrompt']['step'] . '\')">Prev</button>';
        else
            $prompt_html .= '<button id="promptButton" class="navButton disabled" disabled>Prev</button>';

        if (count($TutorialPrompt['TutorialPrompt']['yes']) || count($TutorialPrompt['TutorialPrompt']['no'])) {
            $prompt_html .= '<span>';
            $prompt_html .= '<button id="promptButton" class="decisionButton" onclick="tutorial_prompt(\'' . $TutorialPrompt['TutorialPrompt']['yes'] . '\')">Yes</button>';
            $prompt_html .= '<button id="promptButton" class="decisionButton" onclick="tutorial_prompt(\'' . $TutorialPrompt['TutorialPrompt']['no'] . '\')">No</button>';
            $prompt_html .= '</span>';
        }

        if (count($neighbors['next']))
            $prompt_html .= '<button id="promptButton" class="navButton" onclick="tutorial_prompt(\'' . $neighbors['next']['TutorialPrompt']['step'] . '\')">Next</button>';
        else
            $prompt_html .= '<button id="promptButton" class="navButton disabled" disabled>Next</button>';
        $prompt_html .= '</span>';
        $prompt_html .= '</div>';
        $prompt_html .= '</div>';

        echo $prompt_html;
        $this->autoRender = false;
    }

    /* Switch Tutorial Prompts On/Off */
    function tutorial_switch($id, $switch_on, $type)
    {
        $data = array('id' => $id, 'tutorial_on' => $switch_on, 'tutorial_type_id' => $type);
        $this->ProblemMap->save($data);
        $this->autoRender = false;
    }

    public function view_objtree($id)
    {
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));
        $Entities = $this->Entity->find('all', array(
            'conditions' => array(
                'Entity.problem_map_id' => $id,
                'Entity.type' => 'requirement',
                'Entity.subtype' => 'objective' // data whose subtype is 'objective'
            )
        ));

        $Decompositions = $this->Decomposition->find('all', array(
            'conditions' => array(
                'Decomposition.problem_map_id' => $id
            )
        ));

        $ent_arr = [];
        $dec_arr = [];
        foreach ($Entities as $entity) {
            array_push($ent_arr, $entity['Entity']);
        }
        foreach ($Decompositions as $decomposition) {
            array_push($dec_arr, $decomposition['Decomposition']);
        }
        $data = [];
        $data['name'] = $ProblemMap['ProblemMap']['name'];
        $data['children'] = $this->getChildrenEntities(null, $ent_arr, $dec_arr);

        $child_count = count($data['children']);
        if ($child_count)
            $objtree_html = $this->tree_traversal_view_objtree($data['children'], $data['name'], $child_count);

        $this->set('objtree_html', $objtree_html);
    }

    public function tree_traversal_view_objtree($dataArr, $name, $child_count)
    {
        $child_arr = [];
        $objtree_html = '';
        $objtree_html .= "<h3 style='text-align: center;'>" . $name . "</h3>";
        $objtree_html .= "<table cellpadding='5' style='width: auto; margin: 0 auto;'>";

        if (strpos($dataArr[0]['name'], 'Decomp') === FALSE)
            $objtree_html .= "<tr><th>Name</th><th>Weight</th></tr>";

        foreach ($dataArr as $key => $arr) {
            $count = count($arr['children']);
            if (strpos($arr['name'], 'Decomp') !== FALSE) {
                $objtree_html .= $this->tree_traversal_view_objtree($arr['children'], '<small>' . $arr['name'] . '</small>', $count);
            } else {
                if ($count > 0) {
                    array_push($child_arr, $key);
                }
                $objtree_html .= "<tr>";
                $objtree_html .= "<td>" . $arr['name'] . "</td><td>: <span id='" . $arr['id'] . "'>" . $arr['weight'] . "</span></td>";
                $objtree_html .= "<td>";
                $objtree_html .= "<select id='" . $arr['id'] . "' style='width:auto;'>";
                for ($j = 1; $j <= $child_count; $j++) {
                    if ($arr['weight_option'] == $j) {
                        $objtree_html .= "<option value='" . $j . "' selected>" . $j . "</option>";
                    } else
                        $objtree_html .= "<option value='" . $j . "'>" . $j . "</option>";
                }
                $objtree_html .= "</select>";
                $objtree_html .= "</td>";
                $objtree_html .= "</tr>";
            }
        }
        $objtree_html .= "</table>";
        $objtree_html .= "<br>";
        foreach ($child_arr as $key) {
            $objtree_html .= $this->tree_traversal_view_objtree($dataArr[$key]['children'], $dataArr[$key]['name'], $count);
        }
        return $objtree_html;
    }

    public function print_objtree($id)
    {
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));
        $Entities = $this->Entity->find('all', array(
            'conditions' => array(
                'Entity.problem_map_id' => $id,
                'Entity.type' => 'requirement',
                'Entity.subtype' => 'objective',
            )
        ));

        $Decompositions = $this->Decomposition->find('all', array(
            'conditions' => array(
                'Decomposition.problem_map_id' => $id
            )
        ));

        $ent_arr = [];
        $dec_arr = [];
        foreach ($Entities as $entity) {
            array_push($ent_arr, $entity['Entity']);
        }
        foreach ($Decompositions as $decomposition) {
            array_push($dec_arr, $decomposition['Decomposition']);
        }

        $data = [];
        $data['id'] = 0;
        $data['parent_id'] = null;
        $data['name'] = $ProblemMap['ProblemMap']['name'];
        $data['children'] = $this->getChildrenEntities(null, $ent_arr, $dec_arr);

        $this->set('treedata', $data);
    }

    public function getChildrenEntities($id, $ent_arr, $dec_arr)
    {
        $children = [];
        if ($id == null) {
            foreach ($ent_arr as $ent) {
                if ($ent['decomposition_id'] == null) {
                    $data = [];
                    $data['id'] = $ent['id'];
                    $data['parent_id'] = 0;
                    $data['weight'] = $ent['weight'];
                    $data['weight_option'] = $ent['weight_option'];
                    $data['name'] = $ent['name'];
                    $data['children'] = $this->getChildrenDecomps($ent['id'], $ent_arr, $dec_arr);
                    array_push($children, $data);
                }
            }
        } else {
            foreach ($ent_arr as $ent) {
                if ($ent['decomposition_id'] == $id) {
                    $data = [];
                    $data['id'] = $ent['id'];

                    foreach ($dec_arr as $dec) {
                        if ($dec['id'] == $ent['decomposition_id'])
                            $data['parent_id'] = $dec['entity_id'];
                    }

                    $data['weight'] = $ent['weight'];
                    $data['weight_option'] = $ent['weight_option'];
                    $data['name'] = $ent['name'];
                    $data['children'] = $this->getChildrenDecomps($ent['id'], $ent_arr, $dec_arr);
                    array_push($children, $data);
                }
            }
        }

        return $children;
    }

    public function getChildrenDecomps($id, $ent_arr, $dec_arr)
    {
        $children = [];
        foreach ($dec_arr as $dec) {
            if ($dec['entity_id'] == $id) {
                $data = [];
                $data['name'] = 'Decomp' . $dec['id'];
                $data['children'] = $this->getChildrenEntities($dec['id'], $ent_arr, $dec_arr);
                if (count($data['children']))
                    array_push($children, $data);
            }
        }
        return $children;
    }

    public function save_objtree_weights($id, $weight, $weight_option)
    {
        $data = array('id' => $id, 'weight' => $weight, 'weight_option' => $weight_option);
        $this->Entity->save($data);
        $this->autoRender = false;
    }

    // Tree View
    public function view_graph($id)
    {
        $this->log_entry($id, "ProblemMapsController, view_graph, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    //Create a new graph view by Zongkun - Network View
    public function view_graphNew($id)
    {
        //For nodes and children
        $array = array();

        $dbclass = new DATABASE_CONFIG;
        $conn = $dbclass->getConnection();

        //Requirements----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'requirement'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }
        //User Scenario----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'usescenario'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }
        //Functions----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'function'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }
        //Artifacts----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'artifact'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }
        //Behaviors----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'behavior'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }
        //Issues----------------
        $fetch = mysql_query("SELECT * FROM entities where problem_map_id = $id and type = 'issue'");
        while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
            $e = new ProblemMapRank;
            $e->id = $row['id'];
            $e->decomposition_id = $row['decomposition_id'];
            $e->name = $row['name'];
            $e->type = $row['type'];
            $e->current_decomposition = $row['current_decomposition'];
            $e->problem_map_id = $row['problem_map_id'];
            $array[] = $e;
        }

        foreach ($array as $e) {
            foreach ($array as $tmp) {
                if ($tmp->decomposition_id != null) {
                    if ($e->current_decomposition != null) {
                        if ($tmp->decomposition_id == $e->current_decomposition) {
                            $e->children1[] = $tmp->name;
                        }
                    }
                }
            }
        }
        //For links
        $linkFetch = mysql_query("SELECT * FROM links where problem_map_id = $id");
        while ($row = mysql_fetch_array($linkFetch, MYSQL_ASSOC)) {
            foreach ($array as $e) {
                if ($row['from_entity_id'] == $e->id) {
                    foreach ($array as $tmp) {
                        if ($tmp->id == $row['to_entity_id']) {
                            $e->thelinks[] = $tmp->name;
                        }
                    }
                }
            }
        }
        $outPutJson = json_encode($array);
        file_put_contents('problemMapStructure.json', $outPutJson);

        $this->log_entry($id, "ProblemMapsController, view_graph_2, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view_predicate($id)
    {
        $this->log_entry($id, "ProblemMapsController, view_predicate, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);

        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view_text($id)
    {
        $this->log_entry($id, "ProblemMapsController, view_predicate, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view($id)
    {
        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view_log($id)
    {
        // retrieve the problem map log entries and set it to a variable accessible in the view
        $Log = $this->LogEntry->find('all', array(
            'conditions' => array(
                'LogEntry.problem_map_id =' => $id
            )
        ));
        $this->set(compact('Log'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'Log'
        ));
    }

    public function add()
    {
        $error = false;
        $problem_sets = $this->ProblemSet->find('list', array(
            'order' => array('ProblemSet.id' => 'desc')
        ));
        $this->set(compact("problem_sets"));
        // check if the data is being posted (submitted).
        if ($this->request->is('post')) {

            // get the logged in user id
            $this->request->data['ProblemMap']['user_id'] = $this->Auth->user('id');

            // start database transaction
            $this->ProblemMap->begin();

            // Save Problem Map
            if (!$this->ProblemMap->save($this->request->data)) {
                $error = true;
            }

            // handle transaction and message
            if ($error) {
                // rollback transaction
                $this->ProblemMap->rollback();
                $message = 'Error';

                // set message to be displayed to user via CakePHP flash
                $this->Session->setFlash('Unable to create problem map.');
            } else {
                // commit transaction
                $this->ProblemMap->commit();
                $message = 'Saved';

                // set message to be displayed to user via CakePHP flash
                $this->Session->setFlash('Your Problem Map has been created.');

                // redirect the user
                $this->redirect(array(
                    'action' => 'index'
                ));
            }

            // this is for JSON and XML requests.
            $this->set(compact("message"));
            $this->set('_serialize', array(
                'message'
            ));
        }
    }

    public function view_problem_set()
    {
//        $this->paginate = array('limit' => 20);
////        $problem_sets = $this->ProblemSet->find('all', array('recursive' => -1));
////        $this->set(compact("problem_sets"));
//        $this->set('problem_sets', $this->paginate('ProblemSet'));

        $this->Paginator->settings = $this->paginate;

        // similar to findAll(), but fetches paged results
//        $data = $this->Paginator->paginate('Recipe');
        $this->ProblemSet->recursive = -1;
        $this->set('problem_sets', $this->Paginator->paginate('ProblemSet'));
    }

    public function add_problem_set()
    {
        $error = false;
        // check if the data is being posted (submitted).
        if ($this->request->is('post')) {

            // start database transaction
            $this->ProblemSet->begin();
            if (!$this->ProblemSet->save($this->request->data)) {
                $error = true;
            }

            // handle transaction and message
            if ($error) {
                // rollback transaction
                $this->ProblemSet->rollback();
                // set message to be displayed to user via CakePHP flash
                $this->Session->setFlash('Unable to create problem set.');
            } else {
                // commit transaction
                $this->ProblemSet->commit();
                $message = 'Saved';

                // set message to be displayed to user via CakePHP flash
                $this->Session->setFlash('Problem Set has been created.');

                // redirect the user
                $this->redirect(array(
                    'action' => 'index'
                ));
            }

            // this is for JSON and XML requests.
            $this->set(compact("message"));
            $this->set('_serialize', array(
                'message'
            ));
        }
    }

    // edit the problem map
    public function edit($id)
    {
        $this->log_entry($id, "ProblemMapsController, edit, " . $id);

        // retrieve the current problem map if loading the form.
        $this->ProblemMap->id = $id;

        // check if get request (not submitting)
        if ($this->request->is('get')) {
            $this->request->data = $this->ProblemMap->read();
        } else {
            // here if the data has been posted. Save the new data and return result.
            if ($this->ProblemMap->save($this->request->data)) {
                $this->Session->setFlash('Your problem map has been updated.');
                $this->redirect(array(
                    'action' => 'index'
                ));
                $message = 'Saved';
            } else {
                $this->Session->setFlash('Unable to update your post.');
                $message = 'Error';
            }
        }

        // this is for JSON and XML requests.
        $this->set(compact("message"));
        $this->set('_serialize', array(
            'message'
        ));
    }

    // delete problem map
    public function delete($id)
    {
        $this->log_entry($id, "ProblemMapsController, delete, " . $id);

        // cannot delete with a get request (only POST).
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }

        // delete the problem map and return the result.
        if ($this->ProblemMap->delete($id)) {

            // set message to display to user
            $this->Session->setFlash('The problem map with id: ' . $id . ' has been deleted.');

            // redirect user back to index
            $this->redirect(array(
                'action' => 'index'
            ));
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }

        // this is for JSON and XML requests.
        $this->set(compact("message"));
        $this->set('_serialize', array(
            'message'
        ));
    }

    /* Download specifications as text file */
    public function download_spec($id)
    {
        App::import('Helper', 'Plaintext');
        $Plaintext = new PlaintextHelper(new View(null));

        $Entities = $this->Entity->find('all', array(
            'conditions' => array(
                'Entity.problem_map_id' => $id,
                'Entity.type' => 'requirement',
                'Entity.subtype' => 'specification'
            )
        ));

        if (count($Entities)) {
            $count = 0;
            $htmldata = "";
            $htmldata .= "S.No. \r\t Specifications \r\n";
            foreach ($Entities as $entity) {
                $count++;
                $htmldata .= $count . " \r\t " . $entity['Entity']['name'] . "\r\t" . $entity['Entity']['name'] . "\r\n";
            }
        } else {
            $htmldata = "No data available";
        }
        $this->ProblemMap->recursive = -1;
        $ProblemMap = $this->ProblemMap->findById($id);
        $filename = $ProblemMap['ProblemMap']['name'] . '_Specifications_' . date('Y-m-d') . '.txt';
        Configure::write('debug', 0);
        $this->layout = 'ajax';
        $Plaintext->setFilename($filename);
        echo $Plaintext->render($htmldata);

        $this->autoRender = false;
    }

    /* Display Process Replays data */
    public function view_processreplay($id)
    {
        // retrieve the Process Replays data and set it to a variable accessible in the view
        $ProcessReplays = $this->ProcessReplays->find('all', array(
            'conditions' => array('ProcessReplays.problem_map_id' => $id),
            'recursive' => -1
        ));

        $this->set(compact('ProcessReplays'));
        $this->set('ProblemMapId', $id);
    }

    /* Save HTML data of each action */
    public function save_action()
    {
        if ($this->request->is('post')) {
            if ($this->ProcessReplays->save($this->request->data))
                $error = true;
        }
        $this->autoRender = false;
    }

    /* Save Perception for each action */
    public function save_perception($id, $perception)
    {
        $data = array('id' => $id, 'perception' => $perception);
        $this->ProcessReplays->save($data);
        $this->autoRender = false;
    }

    /* Delete invalid current decomposition id in the table */
    public function reset_invalid_current_decomposition($id)
    {
        $sql = 'Select id from decompositions where problem_map_id = ' . $id;
        $decomp_ids = $this->Decomposition->query($sql);
        if (count($decomp_ids) > 0) {
            $valid_decompids = [];
            foreach ($decomp_ids as $decomp) {
                array_push($valid_decompids, $decomp['decompositions']['id']);
            }

            $sql = 'Select id from entities where problem_map_id = ' . $id . ' and current_decomposition not in (' . join(",", $valid_decompids) . ')';
            $inv_cur_dec_entity = $this->Entity->query($sql);

            if (count($inv_cur_dec_entity) > 0) {
                $entity_id = [];
                foreach ($inv_cur_dec_entity as $id) {
                    array_push($entity_id, $id['entities']['id']);
                }
                $sql = 'Update entities set current_decomposition = NULL where id in (' . join(",", $entity_id) . ')';
                $this->Entity->query($sql);
            }

            $sql = 'Select id from entities where problem_map_id = ' . $id . ' and decomposition_id not in (' . join(",", $valid_decompids) . ')';
            $inv_decompId_entity = $this->Entity->query($sql);

            if (count($inv_decompId_entity) > 0) {
                $entity_id = [];
                foreach ($inv_decompId_entity as $id) {
                    array_push($entity_id, $id['entities']['id']);
                }
                $sql = 'Update entities set decomposition_id = NULL where id in (' . join(",", $entity_id) . ')';
                $this->Entity->query($sql);
            }
        }
        $this->autoRender = false;
    }

    // Tree View
    public function view_entity_depth($id)
    {
        $this->log_entry($id, "ProblemMapsController, view_graph, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function pmap_score($pmapid)
    {
        //  TODO: function - category_suggestion
        //        $obj = new Java("Classifiers.MainClassifier");
        //        $obj->fetchScore($pmapid);
        //        $this->calculate_pmap_skills($pmapid);
        //        return;

        $this->ProblemMap->recursive = -1;
        $problem_map = $this->ProblemMap->findById($pmapid);
        $this->set(compact('problem_map'));

        if ($this->request->is('post')) {
            if (isset($this->request->data['Judge_Scores']) and !is_null($this->request->data['Judge_Scores'])) {
                $pmap = $this->ProblemMap->findById($pmapid);
                $pmap_judge_count = $pmap['ProblemMap']['judge_count'];

                $this->ProblemSet->recursive = -1;
                $pmapset = $this->ProblemSet->findById($pmap['ProblemMap']['problem_set_id']);
                $pmapset_judge_count = $pmapset['ProblemSet']['judge_count'];

//                print_r($this->request->data['Judge_Scores']);
//                return;

//              save judges score
                if ($pmap_judge_count < $pmapset_judge_count) {
                    $pmap_judge_count++;
                    $this->ProblemMap->save(array('id' => $pmapid, 'judge_count' => $pmap_judge_count));
                    $this->JudgesScore->saveAll($this->request->data['Judge_Scores']);
                }

//              if all the judges completed manual grading
                if ($pmap_judge_count == $pmapset_judge_count) {
//                    TODO: Calculate Pmap skills - post required?
//                    TODO: calculate raw score from judges_score and update entities table
//                    TODO: Update centroid table from judges_score table
//                    TODO: then calc pmap skills
//                    TODO: update is_scored = 1
                }
            }
        }

        $is_judge = $this->Auth->user('is_judge');
        $is_scored = $problem_map['ProblemMap']['is_scored'];

        $this->set(compact('is_judge'));
        $this->set(compact('is_scored'));

        if ($is_scored == 0) $this->calculate_raw_pmap_score($problem_map);

        $this->calculate_pmap_skills($pmapid);

        // check if the judge has already scored this pmap
        $judge_scored = $this->JudgesScore->hasAny(array('JudgesScore.problem_map_id' => $pmapid,
            'JudgesScore.user_id' => $this->Auth->user('id')));
        $this->set(compact('judge_scored'));

        // create form for manual scoring
        if (!$judge_scored and $is_scored == 2 and $is_judge == 1) $this->manual_pmap_scoring($problem_map);

    }

    // calculate scores of pmap entities
    public function calculate_raw_pmap_score($problem_map)
    {
        $pmapid = $problem_map['ProblemMap']['id'];
        $prob_set_id = $problem_map['ProblemMap']['problem_set_id']; //Ex: D1 Challenge

        $EntityTypeSubtypes = $this->EntityTypeSubtype->find('all', array(
                'fields' => array('EntityTypeSubtype.type', 'EntityTypeSubtype.subtype'))
        );

        $cnt_ent_scored = 0;
        $cnt_ent_not_scored = 0;
        $total_score = 0;

        $entities_scored = [];
        $entity_scored = [];

        foreach ($EntityTypeSubtypes as $EntityTypeSubtype) {
            $entity_type = $EntityTypeSubtype['EntityTypeSubtype']['type'];
            $entity_subtype = $EntityTypeSubtype['EntityTypeSubtype']['subtype'];

            $entity_cat_score = 0;

            $cnt_ent_pos_score = 0;
            $cnt_ent_neg_score = 0;

            $entities = $this->Entity->find('list', array(
                'conditions' => array('Entity.problem_map_id' => $pmapid, 'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype),
                'fields' => array('Entity.name'),
                'recursive' => -1
            ));

            $threshold = 0.6;
            $centroids = $this->CentroidScore->find('list', array(
                'conditions' => array(
                    'CentroidScore.problem_set_id' => $prob_set_id,
                    'CentroidScore.entity_type' => $entity_type,
                    'CentroidScore.entity_subtype' => $entity_subtype,
                    'CentroidScore.threshold' => $threshold
                ),
                'fields' => array('CentroidScore.entity_name', 'CentroidScore.score'),
                'recursive' => -1
            ));

            if (empty($centroids)) {
                $threshold = 0.5;
                $centroids = $this->CentroidScore->find('list', array(
                    'conditions' => array(
                        'CentroidScore.problem_set_id' => $prob_set_id,
                        'CentroidScore.entity_type' => $entity_type,
                        'CentroidScore.entity_subtype' => $entity_subtype,
                        'CentroidScore.threshold' => $threshold
                    ),
                    'fields' => array('CentroidScore.entity_name', 'CentroidScore.score'),
                    'recursive' => -1
                ));;
            }

            foreach ($entities as $ent_id => $entity) {
                $max_similarity = 0;
                $max_score = -100;
                $centroid_matched = null;
                foreach ($centroids as $centroid => $score) {
                    $similarity = $this->get_similarity($entity, $centroid);
                    if ($similarity >= $threshold) {
                        if ($similarity > $max_similarity || is_null($centroid_matched)) {
                            $max_similarity = $similarity;
                            $max_score = $score;
                            $centroid_matched = $centroid;
                        }
                    }
                }

                if ($max_score > -100) {
                    $cnt_ent_scored++;
                    $entity_scored['id'] = $ent_id;
                    $entity_scored['ent_score'] = $max_score;
                    array_push($entities_scored, $entity_scored);
                    $entity_cat_score = $entity_cat_score + $max_score;

                    ($max_score < 0) ? $cnt_ent_neg_score++ : $cnt_ent_pos_score++;
                } else {
                    $cnt_ent_not_scored++;
                }
            }
            $total_score = $total_score + $entity_cat_score;
        }

        if (count($entities_scored) > 0) {
            print_r($entities_scored);
            $this->Entity->saveAll($entities_scored);
        }

        if ($cnt_ent_not_scored > 0) {
            $this->ProblemMap->save(array("id" => $pmapid, "is_scored" => 2)); // requires manual grading
        } else {
            $this->ProblemMap->save(array("id" => $pmapid, "is_scored" => 1)); // pmap has final score
        }
    }

    public function get_similarity($phrase1, $phrase2)
    {
        $url = $url = 'http://localhost:8080/StsService/GetStsSim';
        $fields_string = 'operation=api&phrase1=' . $phrase1 . '&phrase2=' . $phrase2 . '&sim_type=0&query=Get+Similarity';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $similarity = curl_exec($ch);
        curl_close($ch);
        return $similarity;
    }

    public function calculate_pmap_skills($pmapid)
    {
        $this->Entity->recursive = -1;

//        $pmapid_arr = array(210, 211, 212, 213, 215, 216, 217, 220, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 236, 237, 238, 239);

//        echo '<table border="1">';
//        echo '<tr>';
//        echo '<th>Pmap ID</th>';
//        echo '<th>Requirement Elicitation</th>';
//        echo '<th>Relationship Identification</th>';
//        echo '<th>Information Seeking</th>';
//        echo '<th>Use Description</th>';
//        echo '<th>Key Objective Identification</th>';
//        echo '<th>Challenging Issue</th>';
//        echo '<th>Delight Addition</th>';
//        echo '<th>Specification</th>';
//        echo '<th>Decomposition</th>';
//        echo '</tr>';
//
//        foreach ($pmapid_arr as $pmapid) {
//          print_r($pmapid);

//          Requirement without subcategories
        $req_elicitation = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="requirement" and subtype=""');

        $use_desc = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="usescenario"');

        $key_obj = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="requirement" and subtype="objective" and ent_score > 1');

        $challenging_issue = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="issue" and ent_score > 1');

        $delight_addition = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type != "issue" and ent_score > 1');

        $specification = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="requirement" and subtype="specification"');

        $decomposition = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' AND
                            (decomposition_id IN (SELECT id FROM decompositions WHERE problem_map_id = ' . $pmapid . ') OR
                            current_decomposition IN (SELECT id FROM decompositions WHERE problem_map_id = ' . $pmapid . '))');

//          Relationship - Links
        $link_ent_ids = $this->Entity->query('
                            SELECT from_entity_id AS entity_id FROM links
                            WHERE problem_map_id = ' . $pmapid . '
                            UNION ALL
                            SELECT to_entity_id AS entity_id FROM links
                            WHERE problem_map_id = ' . $pmapid . '
                            ORDER BY entity_id');

        $prev_ent_id = -1;
        $prev_ent_score = 0;
        $relationship = 0;
        foreach ($link_ent_ids as $curr_ent_id) {
            if ($prev_ent_id == $curr_ent_id[0]['entity_id']) {
                $relationship += $prev_ent_score;
            } else {
                $curr_ent_score = $this->Entity->find('first', array(
                    'conditions' => array('Entity.id' => $curr_ent_id[0]['entity_id']),
                    'fields' => array('ent_score')))['Entity']['ent_score'];

                $relationship += $curr_ent_score;

                $prev_ent_id = $curr_ent_id[0]['entity_id'];
                $prev_ent_score = $curr_ent_score;
            }
        }

//          Information Seeking
        $info_seek = $this->Entity->query('
                            SELECT SUM(ent_score) as score FROM entities
                            WHERE problem_map_id = ' . $pmapid . ' and type="issue" and subtype="question"');

        $req_eli_score = (is_null($req_elicitation[0][0]['score']) ? 0 : $req_elicitation[0][0]['score']);
        $use_des_score = (is_null($use_desc[0][0]['score']) ? 0 : $use_desc[0][0]['score']);
        $key_obj_score = (is_null($key_obj[0][0]['score']) ? 0 : $key_obj[0][0]['score']);
        $chall_iss_score = (is_null($challenging_issue[0][0]['score']) ? 0 : $challenging_issue[0][0]['score']);
        $del_add_score = (is_null($delight_addition[0][0]['score']) ? 0 : $delight_addition[0][0]['score']);
        $specs_score = (is_null($specification[0][0]['score']) ? 0 : $specification[0][0]['score']);
        $decomp_score = (is_null($decomposition[0][0]['score']) ? 0 : $decomposition[0][0]['score']);
        $rel_score = $relationship;
        $info_seek_score = (is_null($info_seek[0][0]['score']) ? 0 : $info_seek[0][0]['score']);

        $total_score = $req_eli_score + $use_des_score + $key_obj_score + $chall_iss_score + $del_add_score + $specs_score +
            $decomp_score + $rel_score + $info_seek_score;

        $this->set(compact('req_eli_score'));
        $this->set(compact('use_des_score'));
        $this->set(compact('key_obj_score'));
        $this->set(compact('chall_iss_score'));
        $this->set(compact('del_add_score'));
        $this->set(compact('specs_score'));
        $this->set(compact('decomp_score'));
        $this->set(compact('rel_score'));
        $this->set(compact('info_seek_score'));
        $this->set(compact('total_score'));

//            echo "<tr>";
//            echo "<td>" . $pmapid . "</td>";
//            echo "<td>" . (is_null($req_elicitation[0][0]['score']) ? 0 : $req_elicitation[0][0]['score']) . "</td>";
//            echo "<td>" . $relationship . "</td>";
//            echo "<td>" . (is_null($info_seek[0][0]['score']) ? 0 : $info_seek[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($use_desc[0][0]['score']) ? 0 : $use_desc[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($key_obj[0][0]['score']) ? 0 : $key_obj[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($challenging_issue[0][0]['score']) ? 0 : $challenging_issue[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($delight_addition[0][0]['score']) ? 0 : $delight_addition[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($specification[0][0]['score']) ? 0 : $specification[0][0]['score']) . "</td>";
//            echo "<td>" . (is_null($decomposition[0][0]['score']) ? 0 : $decomposition[0][0]['score']) . "</td>";
//            echo "</tr>";
//        }
//        echo '</table>';

    }

    public function manual_pmap_scoring($problem_map)
    {
        $pmapid = $problem_map['ProblemMap']['id'];
        $entities_to_score = $this->Entity->find('all', array(
            'conditions' => array(
                'Entity.problem_map_id' => $pmapid,
                'Entity.ent_score' => 0
            ),
            'fields' => array('id', 'name', 'type', 'subtype', 'ent_score'),
            'order' => array('type', 'subtype'),
            'recursive' => -1
        ));
        $this->set(compact('entities_to_score'));
        $this->set('user_id', $this->Auth->user('id'));
        $this->set('pmap_id', $pmapid);
    }

    public function download_scoring_stats($pmapid)
    {
        $this->layout = null;
        $this->autoLayout = false;
        Configure::write('debug', '0');

        $this->ProblemMap->recursive = -1;
        $problem_map = $this->ProblemMap->findById($pmapid);
        $this->set(compact('problem_map'));

        $table_rows = [];
        $table_row = [];

        $this->Entity->recursive = -1;

        $EntityTypeSubtypes = $this->EntityTypeSubtype->find('all', array(
                'fields' => array('EntityTypeSubtype.type', 'EntityTypeSubtype.subtype'))
        );

        $total_num_entities = $this->Entity->find('count', array(
            'conditions' => array('Entity.problem_map_id' => $pmapid)
        ));
        $this->set(compact('total_num_entities'));

        $cnt_total_ent_scored = $this->Entity->find('count', array(
            'conditions' => array('Entity.problem_map_id' => $pmapid, 'NOT' => array('Entity.ent_score' => 0))
        ));
        $this->set(compact('cnt_total_ent_scored'));

        $cnt_total_ent_not_scored = $this->Entity->find('count', array(
            'conditions' => array('Entity.problem_map_id' => $pmapid, 'Entity.ent_score' => 0)
        ));
        $this->set(compact('cnt_total_ent_not_scored'));

        foreach ($EntityTypeSubtypes as $EntityTypeSubtype) {
            $entity_type = $EntityTypeSubtype['EntityTypeSubtype']['type'];
            $entity_subtype = $EntityTypeSubtype['EntityTypeSubtype']['subtype'];

            $cnt_ent = $this->Entity->find('count', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $pmapid,
                    'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype
                )
            ));

            $cnt_ent_scored = $this->Entity->find('count', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $pmapid,
                    'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype,
                    'NOT' => array('Entity.ent_score' => 0)
                )
            ));

            $cnt_ent_not_scored = $this->Entity->find('count', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $pmapid,
                    'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype,
                    'Entity.ent_score' => 0
                )
            ));

            $cnt_ent_pos_score = $this->Entity->find('count', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $pmapid,
                    'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype,
                    'Entity.ent_score >' => 0
                )
            ));

            $cnt_ent_neg_score = $this->Entity->find('count', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $pmapid,
                    'Entity.type' => $entity_type,
                    'Entity.subtype' => $entity_subtype,
                    'Entity.ent_score <' => 0
                )
            ));

            $sum_entity_type_score = $this->Entity->query('SELECT SUM(ent_score) as score FROM entities
                WHERE problem_map_id = ' . $pmapid . ' and type="' . $entity_type . '" and subtype="' . $entity_subtype . '"');

            // add data
            $table_row['type'] = $entity_type;
            $table_row['subtype'] = ($entity_subtype == '') ? 'NA' : $entity_subtype;
            $table_row['cnt_ent'] = $cnt_ent;
            $table_row['cnt_ent_scored'] = $cnt_ent_scored;
            $table_row['cnt_ent_not_scored'] = $cnt_ent_not_scored;
            $table_row['cnt_ent_pos_score'] = $cnt_ent_pos_score;
            $table_row['cnt_ent_neg_score'] = $cnt_ent_neg_score;
            $table_row['sum_entity_type_score'] = ($sum_entity_type_score[0][0]['score'] == '') ? 0 : $sum_entity_type_score[0][0]['score'];

            array_push($table_rows, $table_row);

        }

        $header = ['Type', 'SubType', 'Count of Ent', 'Count of Ent Scored', 'Count of Ent Not Scored', 'Count of +ve Score',
            'Count of -ve Score', 'Score'];

        $total_entities = ['Total number of Entities', $total_num_entities];
        $total_entities_scored = ['Total number of Entities Scored', $cnt_total_ent_scored];
        $total_entities_not_scored = ['Total number of Entities Not Scored', $cnt_total_ent_not_scored];

        $this->set(compact('header'));
        $this->set(compact('table_rows'));
        $this->set(compact('total_entities'));
        $this->set(compact('total_entities_scored'));
        $this->set(compact('total_entities_not_scored'));
    }
}
