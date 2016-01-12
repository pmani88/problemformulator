<?php
$this->Html->css('view_list', null, array('inline' => false));
$this->Html->css('pmap_score', null, array('inline' => false));
//$this->Html->css('pmap_score_plain', null, array('inline' => false));
?>
<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1>
            <?php echo $problem_map['ProblemMap']['name']; ?>
            <small>(<?php echo $this->Html->link("List View", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_list',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Tree View", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_graph',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Network View", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_graphNew',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Objective Tree", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_objtree',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Download Specs", array(
                    'controller' => 'problem_maps',
                    'action' => 'download_spec',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Retrospection", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_processreplay',
                    $problem_map['ProblemMap']['id']
                )); ?>)
            </small>
        </h1>
    </div>
</div>
<?php
//    require_once("http://localhost:8080/JavaBridge/java/Java.inc");
//    //echo java("php.java.bridge.Util")->VERSION;
//    $yourObj = java("edu.umbc.web.StsServlet");
//    echo 'TEST!! Similarity Value from Java code: ', $yourObj->getSimilarityScore("this is fun","happy");
//define("JAVA_HOSTS", "localhost:8080");
//define("JAVA_SERVLET", "/StsService");
//require_once("http://localhost:8080/JavaBridge/java/Java.inc");
//
//echo java_context()->getServlet()->GetStsSim();

?>
<?php if ($is_scored == 1) { ?>
    <h3 class="sub-heading">P-Map Score</h3>
    <table>
        <tr>
            <th>Total Score</th>
            <th>PMap Skill</th>
            <th>Score (10)</th>
        </tr>
        <tr class="odd">
            <td class="merged-cell" rowspan="9">
                <span>Your Score: 90</span>
                <span>Max Score: 100</span>
            </td>
            <td>Requirement Elicitation</td>
            <td>10</td>
        </tr>
        <tr class="even">
            <td>Relationship identification</td>
            <td>10</td>
        </tr>
        <tr class="odd">
            <td>Information seeking</td>
            <td>10</td>
        </tr>
        <tr class="even">
            <td>Use description</td>
            <td>10</td>
        </tr>
        <tr class="odd">
            <td>Key objective identification</td>
            <td>10</td>
        </tr>
        <tr class="even">
            <td>Challenging issue</td>
            <td>10</td>
        </tr>
        <tr class="odd">
            <td>Delight addition</td>
            <td>10</td>
        </tr>
        <tr class="even">
            <td>Specification</td>
            <td>10</td>
        </tr>
        <tr class="odd">
            <td>Decomposition</td>
            <td>10</td>
        </tr>
    </table>
<?php } ?>

<?php if (!$judge_scored and $is_scored == 2 and $is_judge == 1) { ?>
    <h3 class="sub-heading">Manual Scoring</h3>
    <?php echo $this->Form->create('Judge_Scores', array(
        'class' => 'form-horizontal',
        'inputDefaults' => array(

            'error' => array('attributes' => array('wrap' => 'span', 'class' =>
                'help-inline')),
        )));
    ?>

    <table>
        <tr>
            <th>Entity</th>
            <th>Type</th>
            <th>Subtype</th>
            <th>Score</th>
        </tr>
        <?php
        $n = 1;
        $is_odd = true;
        foreach ($entities_to_score as $entity) {
            echo '<tr class="' . ($is_odd ? 'odd' : 'even') . '">';
            $is_odd = !$is_odd;
            echo '<td>' . $entity['Entity']['name'] . '</td>';
            echo '<td class="center-text">' . ucwords($entity['Entity']['type']) . '</td>';
            echo '<td class="center-text">' . ucwords($entity['Entity']['subtype']) . '</td>';
            echo '<td>';
            echo $this->Form->input($n . '.entity_id', array('type' => 'hidden', 'value' => $entity['Entity']['id']));
            echo $this->Form->input($n . '.user_id', array('type' => 'hidden', 'value' => $user_id));
            echo $this->Form->input($n . '.problem_map_id', array('type' => 'hidden', 'value' => $pmap_id));
            echo $this->Form->input($n . '.score', array(
                    'type' => 'select',
                    'options' => array_combine(range(-1, 3, 1), range(-1, 3, 1)),
                    'label' => false
                )
            );
            echo '</td>';
            echo '</tr>';
            $n++;
        }
        ?>
    </table>
    <?php
    echo '<div class="controls"><button type="submit" class="btn btn-primary">Save</button> ';
    echo $this->Form->end();
}
?>
