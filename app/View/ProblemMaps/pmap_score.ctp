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


//if($is_scored == 0) {
//    echo $scores_to_display;
////    echo "<h4>Total Score: $total_score</h4>";
//    echo '<br/>';
//    echo '<h4> Total No. of entities: ' . ($cnt_ent_scored + $cnt_ent_not_scored) . '</h4>';
//    echo '<h4> No. of entities scored: ' . $cnt_ent_scored . '</h4>';
//    echo '<h4> No. of entities not scored: ' . $cnt_ent_not_scored . '</h4>';
//}

?>
<h3 class="sub-heading">P-Map Score</h3>
<table>
    <tr>
        <th>Total Score</th>
        <th>PMap Skill</th>
        <th>Score</th>
    </tr>
    <tr class="odd">
        <td class="merged-cell" rowspan="5">50</td>
        <td>Requirement Elicitation</td>
        <td>10</td>
    </tr>
    <tr class="even">
        <td>Requirement Elicitation</td>
        <td>10</td>
    </tr>
    <tr class="odd">
        <td>Requirement Elicitation</td>
        <td>10</td>
    </tr>
    <tr class="even">
        <td>Requirement Elicitation</td>
        <td>10</td>
    </tr>
    <tr class="odd">
        <td>Requirement Elicitation</td>
        <td>10</td>
    </tr>
</table>



<h3 class="sub-heading">Manual Scoring</h3>
<?php echo $this->Form->create('Manual_Score', array(
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
        echo $this->Form->input($n . '.id', array('type' => 'hidden', 'value' => $entity['Entity']['id']));
        echo $this->Form->input($n . '.ent_score', array(
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
?>
