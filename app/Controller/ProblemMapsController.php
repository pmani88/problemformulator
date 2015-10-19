<?php
App::uses('File', 'Utility');
require_once("../Config/database.php");

// Controller/ProblemMapsController.php
class ProblemMapRank {
	public $id;
	public $decomposition_id;
	public $name;
	public $type;
	public $current_decomposition;
	public $problem_map_id;
	public $thelinks=array();
	public $children1=array();
}

class ProblemMapsController extends AppController {

    // used for XML and JSON output
    public $components = array(
        'RequestHandler'
    );

    // models used
    public $uses = array(
        'ProblemMap',
        'LogEntry',
		'Entity',
		'Decomposition',
		'EntitySubtypes',
		'TutorialType',
		'TutorialPrompt',
		'ProcessReplays',
		'Perception',
		'CentriodScore'
    );

    // determine if the file extension is prolog and if so set the appropriate layout
    public function beforeFilter() {

        parent::beforeFilter();
        $this->RequestHandler->setContent('pl', 'text/pl');
    }

    // check if the user is authorized
    public function isAuthorized($user = NULL) {
	
		// if admin full access
		if(parent::isAuthorized($user)){
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
			'get_similarity'
        ))) {
            return true;
        }
    }

    // gets the invalid entities using ASP and the checker rules
    public function getInvalidEntities($id) {

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
    public function index() {

        // if admin find all problem maps
        if ($this->Auth->user('admin') == 1) {
			$this->set("admin",1);
            $ProblemMaps = $this->ProblemMap->find('all', array('recursive' => 0));
        }
        else {
			$this->set("admin",0);
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
	
    public function view_list($id) {

        $this->log_entry($id, "ProblemMapsController, view_list, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
		
		$this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));

		/* Entity Subtypes - Start */
		$EntitySubtypes = $this->EntitySubtypes->find('all');
		$Entitytypes = $this->EntitySubtypes->find('all', array('fields' => array('DISTINCT EntitySubtypes.type')));
		$subtypes = [];
		foreach($Entitytypes as $Entitytype){
			$subtypes[$Entitytype['EntitySubtypes']['type']] = [];
		}
		foreach($EntitySubtypes as $EntitySubtype){
			array_push($subtypes[$EntitySubtype['EntitySubtypes']['type']], $EntitySubtype['EntitySubtypes']['subtype']);
		}
	
		$this->set(compact('subtypes'));
		/* Entity Subtypes - End */
    }
	
	public function tutorial_prompts($step, $type){
		$TutorialPrompt = $this->TutorialPrompt->find('first', array('conditions' => array('TutorialPrompt.step' => $step, 'TutorialPrompt.tutorial_type_id' => $type)));
		$this->set(compact('TutorialPrompt'));
		
		$neighbors = $this->TutorialPrompt->find('neighbors', array('field' => 'id', 'value' => $TutorialPrompt['TutorialPrompt']['id']));
		
		// print_r($TutorialPrompt);
		// print_r($neighbors);
		
		$prompt_html = '';
		$prompt_html .= '<h5>'.$TutorialPrompt['TutorialType']['name'].' for Formulating a Problem</h5>';
		//$prompt_html .= '<small><i>'.$TutorialPrompt['TutorialType']['description'].'</i></small><br><br><hr>';
		$prompt_html .= '<div id="promptBox">';
			$prompt_html .= '<div id="promptMsg">';
				$prompt_html .= '<b>Step</b>: <span>'.$TutorialPrompt['TutorialPrompt']['description'].'</span>';
				$prompt_html .=	'<br><br>';
				
				$prompt_html .=	'<span>';
					if(count($neighbors['prev']))
						$prompt_html .=	'<button id="promptButton" class="navButton" onclick="tutorial_prompt(\''.$neighbors['prev']['TutorialPrompt']['step'].'\')">Prev</button>';
					else
						$prompt_html .=	'<button id="promptButton" class="navButton disabled" disabled>Prev</button>';

					if(count($TutorialPrompt['TutorialPrompt']['yes']) || count($TutorialPrompt['TutorialPrompt']['no'])){
						$prompt_html .=	'<span>';
							$prompt_html .=	'<button id="promptButton" class="decisionButton" onclick="tutorial_prompt(\''.$TutorialPrompt['TutorialPrompt']['yes'].'\')">Yes</button>';
							$prompt_html .=	'<button id="promptButton" class="decisionButton" onclick="tutorial_prompt(\''.$TutorialPrompt['TutorialPrompt']['no'].'\')">No</button>';
						$prompt_html .=	'</span>';
					}
					
					if(count($neighbors['next']))
						$prompt_html .=	'<button id="promptButton" class="navButton" onclick="tutorial_prompt(\''.$neighbors['next']['TutorialPrompt']['step'].'\')">Next</button>';
					else
						$prompt_html .=	'<button id="promptButton" class="navButton disabled" disabled>Next</button>';
				$prompt_html .=	'</span>';
			$prompt_html .=	'</div>';
		$prompt_html .=	'</div>';
		
		echo $prompt_html;
		$this->autoRender = false;
	}
	
	/* Switch Tutorial Prompts On/Off */
	function tutorial_switch($id, $switch_on, $type){
		$data = array('id'=> $id, 'tutorial_on' => $switch_on, 'tutorial_type_id' => $type);
		$this->ProblemMap->save($data);
		$this->autoRender = false;
	}
	
	public function view_objtree($id) {
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
		//print_r(json_encode($Entities));
		//print_r(json_encode($Decompositions));
		
		$ent_arr = [];
		$dec_arr = [];
		foreach($Entities as $entity){
			array_push($ent_arr, $entity['Entity']);
		}
		foreach($Decompositions as $decomposition){
			array_push($dec_arr, $decomposition['Decomposition']);
		}
		//print_r(json_encode($ent_arr));
		//print_r(json_encode($dec_arr));
		$data = [];
		$data['name'] = $ProblemMap['ProblemMap']['name'];
		$data['children'] = $this->getChildrenEntities(null, $ent_arr, $dec_arr);
		
//		print_r(json_encode($data));
		
		$child_count = count($data['children']);
		if($child_count)
			$objtree_html = $this->tree_traversal_view_objtree($data['children'], $data['name'], $child_count);
	
		$this->set('objtree_html', $objtree_html);
	}
	
	public function tree_traversal_view_objtree($dataArr, $name, $child_count){
		$child_arr = [];
		$objtree_html = '';
		$objtree_html .= "<h3 style='text-align: center;'>".$name."</h3>";
		$objtree_html .= "<table cellpadding='5' style='width: auto; margin: 0 auto;'>";
		
		if (strpos($dataArr[0]['name'], 'Decomp') === FALSE)
			$objtree_html .= "<tr><th>Name</th><th>Weight</th></tr>";

		foreach($dataArr as $key => $arr){
			$count = count($arr['children']);
			if (strpos($arr['name'], 'Decomp') !== FALSE){
				$objtree_html .= $this->tree_traversal_view_objtree($arr['children'], '<small>'.$arr['name'].'</small>', $count);
			}
			else {
				if($count > 0){
					array_push($child_arr, $key);
				}
				$objtree_html .= "<tr>";
				$objtree_html .= "<td>".$arr['name']."</td><td>: <span id='".$arr['id']."'>".$arr['weight']."</span></td>";
				$objtree_html .= "<td>";
				$objtree_html .= "<select id='".$arr['id']."' style='width:auto;'>";
				for($j = 1; $j <= $child_count ; $j++){
					if($arr['weight_option'] == $j){
						$objtree_html .= "<option value='".$j."' selected>".$j."</option>";
					} else
						$objtree_html .= "<option value='".$j."'>".$j."</option>";
				}
				$objtree_html .= "</select>";
				$objtree_html .= "</td>";
				$objtree_html .= "</tr>";
			}
		}
		$objtree_html .= "</table>";
		$objtree_html .= "<br>";
		foreach($child_arr as $key){
			$objtree_html .= $this->tree_traversal_view_objtree($dataArr[$key]['children'], $dataArr[$key]['name'], $count);
		}
		return $objtree_html;
	}
	
	public function print_objtree($id) {
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
		foreach($Entities as $entity){
			array_push($ent_arr, $entity['Entity']);
		}
		foreach($Decompositions as $decomposition){
			array_push($dec_arr, $decomposition['Decomposition']);
		}

		$data = [];
		$data['id'] = 0;
		$data['parent_id'] = null;
		$data['name'] = $ProblemMap['ProblemMap']['name'];
		$data['children'] = $this->getChildrenEntities(null, $ent_arr, $dec_arr);
		
		$this->set('treedata', $data);
	}
	
	public function getChildrenEntities($id, $ent_arr, $dec_arr){
		$children = [];
			
		if($id == null){
			foreach($ent_arr as $ent){
				if($ent['decomposition_id'] == null){
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
			foreach($ent_arr as $ent){
				if($ent['decomposition_id'] == $id){
					//echo json_encode($ent).'<br><br>';
					$data = [];
					$data['id'] = $ent['id'];
					
					foreach($dec_arr as $dec){
						if($dec['id'] == $ent['decomposition_id'])
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

	public function getChildrenDecomps($id, $ent_arr, $dec_arr){
		//console.log("dec");
		$children = [];
		foreach($dec_arr as $dec){
			if($dec['entity_id'] == $id){
				$data = [];
				$data['name'] = 'Decomp' . $dec['id'];
				$data['children'] = $this->getChildrenEntities($dec['id'], $ent_arr, $dec_arr);
				if(count($data['children']))
					array_push($children, $data);
			}
		}
		return $children;
	}

	public function save_objtree_weights($id, $weight, $weight_option){
		$data = array('id'=> $id, 'weight' => $weight, 'weight_option' => $weight_option);
		$this->Entity->save($data);
		
		$this->autoRender = false;
	}
	
	// Tree View
    public function view_graph($id) {

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
	public function view_graphNew($id) {
		//For nodes and children
		$array = array();
		$return_arr = array();
		
		$dbclass = new DATABASE_CONFIG;
		$conn = $dbclass->getConnection();
		
		//Requirements----------------
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'requirement'"); 
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
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'usescenario'"); 
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
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'function'"); 
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
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'artifact'"); 
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
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'behavior'"); 
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
		$fetch = mysql_query("SELECT * FROM `entities` where problem_map_id = $id and type = 'issue'"); 
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
		//print_r($array);
		//echo json_encode($array);
		foreach ($array as $e) {
    		//unset($array[$i]);
    		
    		foreach ($array as $tmp){
    			if($tmp->decomposition_id!=null){
    				if($e->current_decomposition!=null){
		    			if($tmp->decomposition_id == $e->current_decomposition ){
							$e->children1[] = $tmp->name;
		    			}
					}
    			}
    		}
		}
		//For links
		$linkFetch = mysql_query("SELECT * FROM `links` where problem_map_id = $id"); 
		while ($row = mysql_fetch_array($linkFetch, MYSQL_ASSOC)) {
			//echo json_encode($row['from_entity_id']);
			foreach ($array as $e) {
				if($row['from_entity_id']==$e->id){
					//$e->thelinks[] = $row['to_entity_id'];
					foreach ($array as $tmp) {
						if($tmp->id == $row['to_entity_id']){
							$e->thelinks[] = $tmp->name;
						}
					}
				}
			}
		}
		$outPutJson =  json_encode($array);
		file_put_contents('problemMapStructure.json',$outPutJson);
		
		
         $this->log_entry($id, "ProblemMapsController, view_graph_2, " . $id);

         // retrieve the problem map and set it to a variable accessible in the view
         $ProblemMap = $this->ProblemMap->findById($id);
         $this->set(compact('ProblemMap'));
 		
         // this is for JSON and XML requests.
         $this->set('_serialize', array(
             'ProblemMap'
         ));
    }
		
	public function view_predicate($id) {

        $this->log_entry($id, "ProblemMapsController, view_predicate, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
		
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }
	
	public function view_text($id) {

        $this->log_entry($id, "ProblemMapsController, view_predicate, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view($id) {

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));

        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

    public function view_log($id) {

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

    public function add() {
		$this->Session->setFlash('.....');
        $error = false;

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
            }
            else {

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

    // edit the problem map
    public function edit($id) {

        $this->log_entry($id, "ProblemMapsController, edit, " . $id);

        // retrieve the current problem map if loading the form.
        $this->ProblemMap->id = $id;

        // check if get request (not submitting)
        if ($this->request->is('get')) {
            $this->request->data = $this->ProblemMap->read();
        }
        else {

            // here if the data has been posted. Save the new data and return result.

            if ($this->ProblemMap->save($this->request->data)) {
                $this->Session->setFlash('Your problem map has been updated.');
                $this->redirect(array(
                    'action' => 'index'
                ));
                $message = 'Saved';
            }
            else {
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
    public function delete($id) {

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
        }
        else {
            $message = 'Error';
        }

        // this is for JSON and XML requests.
        $this->set(compact("message"));
        $this->set('_serialize', array(
            'message'
        ));
    }

	/* Download specifications as text file */
	public function download_spec($id){
		App::import('Helper', 'Plaintext');
        $Plaintext = new PlaintextHelper(new View(null));
		
		$Entities = $this->Entity->find('all', array(
                'conditions' => array(
                    'Entity.problem_map_id' => $id,
                    'Entity.type' => 'requirement',
                    'Entity.subtype' => 'specification' // data whose subtype is 'specification'
                )
            ));
		
		if(count($Entities)){
			$count = 0;
			$htmldata = "";		
			$htmldata .= "S.No. \r\t Specifications \r\n";
			foreach($Entities as $entity){	
				$count++;
				$htmldata .= $count." \r\t ".$entity['Entity']['name']."\r\n";
			}
		} else {
			$htmldata = "No data available";
		}
		$ProblemMap = $this->ProblemMap->findById($id);
		$filename = $ProblemMap['ProblemMap']['name'].'_Specifications_'.date('Y-m-d').'.txt';
		Configure::write('debug', 0);
		$this->layout = 'ajax';
		$Plaintext->setFilename($filename);
		echo $Plaintext->render($htmldata);
		
		$this->autoRender = false;
	}
	
	/* Display Process Replays data */
	public function view_processreplay($id) {
        // retrieve the Process Replays data and set it to a variable accessible in the view
        $ProcessReplays = $this->ProcessReplays->find('all', array(
                'conditions' => array('ProcessReplays.problem_map_id' => $id),
				'recursive' => -1
			));
		//print_r($ProcessReplays);
		//$Perceptions = $this->Perception->find('all', array('group' => array('Perception.category', 'Perception.perception')));
		
		$this->set(compact('ProcessReplays'));
		//$this->set(compact('Perceptions'));
		$this->set('ProblemMapId', $id);
	}
	
	/* Save HTML data of each action */
	public function save_action() {
		if ($this->request->is('post')) {
            if ($this->ProcessReplays->save($this->request->data))
                $error = true;
		}
		$this->autoRender = false;
	}
	
	/* Save Perception for each action */
	public function save_perception($id, $perception) {
        $data = array('id'=> $id, 'perception' => $perception);
		$this->ProcessReplays->save($data);
		$this->autoRender = false;
	}
	
	/* Delete invalid current decomposition id in the table */
	public function reset_invalid_current_decomposition($id) {
		$sql = 'Select id from decompositions where problem_map_id = '.$id;
		$decomp_ids = $this->Decomposition->query($sql);
		if(count($decomp_ids) > 0){
			$valid_decompids = [];
			foreach($decomp_ids as $decomp){
				array_push($valid_decompids,$decomp['decompositions']['id']);
			}
			
			$sql = 'Select id from entities where problem_map_id = '.$id.' and current_decomposition not in ('.join(",",$valid_decompids).')';
			$inv_cur_dec_entity = $this->Entity->query($sql);
			
			if(count($inv_cur_dec_entity)>0){
				$entity_id = [];
				foreach($inv_cur_dec_entity as $id){
					array_push($entity_id, $id['entities']['id']);
				}
				$sql = 'Update entities set current_decomposition = NULL where id in ('.join(",",$entity_id).')';
				$this->Entity->query($sql);
			}
			
			$sql = 'Select id from entities where problem_map_id = '.$id.' and decomposition_id not in ('.join(",",$valid_decompids).')';
			$inv_decompId_entity = $this->Entity->query($sql);
			
			if(count($inv_decompId_entity)>0){
				$entity_id = [];
				foreach($inv_decompId_entity as $id){
					array_push($entity_id, $id['entities']['id']);
				}
				$sql = 'Update entities set decomposition_id = NULL where id in ('.join(",",$entity_id).')';
				$this->Entity->query($sql);
			}
		}
		$this->autoRender = false;
	}
	
	// Tree View
    public function view_entity_depth($id) {

        $this->log_entry($id, "ProblemMapsController, view_graph, " . $id);

        // retrieve the problem map and set it to a variable accessible in the view
        $ProblemMap = $this->ProblemMap->findById($id);
        $this->set(compact('ProblemMap'));
		
        // this is for JSON and XML requests.
        $this->set('_serialize', array(
            'ProblemMap'
        ));
    }

	public function pmap_score($pmapid) {
		$this->ProblemMap->recursive = -1;
		$ProblemMap = $this->ProblemMap->findById($pmapid);
		$this->set(compact('ProblemMap'));

		$entity_type = 'function'; 	//TODO - Modify Entity Type dynamically
		$threshold = 0.6; 			//TODO: How to determine the threshold limit?
		$prob_set_id = 1; 			//TODO: D1, D2 or ..?

		$entities = $this->Entity->find('list', array(
			'conditions' => array('Entity.problem_map_id' => $pmapid, 'Entity.type' => $entity_type),
			'fields'=>array('Entity.name'),
			'recursive' => -1
		));

		$centroids = $this->CentriodScore->find('list', array(
			'conditions' => array(
				'CentriodScore.problem_set_id' => $prob_set_id,
				'CentriodScore.threshold' => $threshold,
				'CentriodScore.entity_type' => $entity_type
			),
			'fields'=>array('CentriodScore.centroid', 'CentriodScore.score'),
			'recursive' => -1
		));

//		print_r($centroids);
		foreach($entities as $ent_id => $entity){
//			echo $ent_id.' => '.$entity.'<br/>';
			$max_similarity = 0;
			$max_score = -1;
			$centroid_matched = null;
			echo '<table border="1">';
			echo '<tr><th>Entity</th><th>Centroid</th><th>Judge Score</th><th>Similarity Score</th></tr>';
			foreach($centroids as $centroid => $score){
				$similarity = $this->get_similarity($entity, $centroid);
				if ($similarity >= $threshold){
//					print_r('Entity: '.$entity." Score: ".$score." Threshold: ".$threshold);
					echo '<tr>';
					echo '<td>'.$entity.'</td>';
					echo '<td>'.$centroid.'</td>';
					echo '<td>'.$score.'</td>';
					echo '<td>'.$similarity.'</td>';
					echo '</tr>';
					if($similarity > $max_similarity || is_null($centroid_matched)) {
						$max_similarity = $similarity;
						$max_score = $score;
						$centroid_matched = $centroid;
					}
				}
			}
			echo '</table>';
			echo '<b>Entity: </b>'.$entity.' <b>Centroid: </b>'.$centroid_matched.'<br/>';
			echo '<b>Maximum Similarity:</b> '.$max_similarity.' <b>Score:</b> '.$max_score;
			echo '<br/><br/><br/>';
//			break;
		}

	}

//	public function get_similarity($phrase1, $phrase2) {
//		$url = 'http://swoogle.umbc.edu/SimService/GetSimilarity?operation=api&phrase1='
//				.urlencode($phrase1).'&phrase2='.urlencode($phrase2).'&corpus=webbase&type=relation';
//		echo $url.'<br/><br/>';
//		$curl = curl_init($url);
//		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//		$similarity = curl_exec($curl);
////		echo $result.'<br/><br/>';
//		return $similarity;
////		return 0;
//	}

	public function get_similarity($phrase1, $phrase2) {
		$url = $url = 'http://localhost:8080/StsService/GetStsSim';
		$fields_string = 'operation=api&phrase1='.$phrase1.'&phrase2='.$phrase2.'&sim_type=0&query=Get+Similarity';
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$similarity = curl_exec($ch);
		curl_close($ch);
		return $similarity;
	}
}
