<?php $this->Html->css('view_list', null, array('inline' => false)); ?>
<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1>
            <?php echo $problem_map['ProblemMap']['name']; ?>
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

<?php echo $this->Form->create('Entity', array(
    'class' => 'form-horizontal',
    'inputDefaults' => array(
        'format' => array('before', 'label', 'between', 'input', 'error',
            'after'),
        'div' => array('class' => 'control-group'),
        'label' => array('class' => 'control-label'),
        'between' => '<div class="controls">',
        'after' => '</div>',
        'error' => array('attributes' => array('wrap' => 'span', 'class' =>
            'help-inline')),
    )));
?>
<table border="1">
    <tr>
        <th>Entity</th>
        <th>Type</th>
        <th>Subtype</th>
        <th>Score</th>
    </tr>
    <?php
    $n = 1;
    foreach ($entities_to_score as $entity) {
        echo '<tr>';
        echo '<td>' . $entity['Entity']['name'] . '</td>';
        echo '<td>' . $entity['Entity']['type'] . '</td>';
        echo '<td>' . $entity['Entity']['subtype'] . '</td>';
        echo '<td>';
        echo $this->Form->input($n.'.id', array('type'=>'hidden', 'value' => $entity['Entity']['id']));
        echo $this->Form->input($n.'.ent_score', array(
                'type' => 'select',
                'options' => array_combine(range(-1, 3, 1), range(-1, 3, 1))
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
<!--<div class="row-fluid scroll active" style="height: 600px;">
    <ul id="artifact" class="entity-list">
        <li class="entity" data-target="#context-menu" entity-id="3022" entity-type="artifact" entity-subtype="">
            <i class="icon icon-folder-open pull-left folder"></i>
            <div class="name editable" contenteditable="false">
                device &nbsp <input type="text" size="5"/>
            </div>
            <ul>
                <li class="entity" data-target="#context-menu" entity-id="3024" entity-type="artifact" entity-subtype="">
                    <i class="icon icon-file pull-left"></i>
                    <span class="sup pull-left"></span>
                    <div class="name editable" contenteditable="false">
                        thrower &nbsp <input type="text" size="5"/>
                    </div>
                    <ul></ul>
                </li>
            </ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3009" entity-type="artifact" entity-subtype="">
            <i class="icon icon-folder-open pull-left folder"></i>
            <div class="name editable" contenteditable="false">
                device &nbsp <input type="text" size="5"/>
            </div>
            <ul>
                <li class="entity" data-target="#context-menu" entity-id="3012" entity-type="artifact" entity-subtype="">
                    <i class="icon icon-file pull-left"></i>
                    <span class="sup pull-left"></span>
                    <div class="name editable" contenteditable="false">
                        wheels &nbsp <input type="text" size="5"/>
                    </div>
                    <ul></ul>
                </li>
                <li class="entity" data-target="#context-menu" entity-id="3010" entity-type="artifact" entity-subtype="">
                    <i class="icon icon-file pull-left"></i>
                    <span class="sup pull-left"></span>
                    <div class="name editable" contenteditable="false">
                        slider &nbsp <input type="text" size="5"/>
                    </div>
                    <ul></ul>
                </li>
                <li class="entity" data-target="#context-menu" entity-id="3011" entity-type="artifact" entity-subtype="">
                    <i class="icon icon-file pull-left"></i>
                    <span class="sup pull-left"></span>
                    <div class="name editable" contenteditable="false">
                        picker tool &nbsp <input type="text" size="5"/>
                    </div>
                    <ul></ul>
                </li>
            </ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3023" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                gripper &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3025" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                wheels &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3026" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                picker tool &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3027" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                slider &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3028" entity-type="artifact" entity-subtype="">
            <i class="icon icon-folder-close pull-left folder"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                device &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3031" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                wheels &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3032" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                gripper &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3033" entity-type="artifact" entity-subtype="">
            <i class="icon icon-file pull-left"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                slider &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
        <li class="entity" data-target="#context-menu" entity-id="3034" entity-type="artifact" entity-subtype="">
            <i class="icon icon-folder-close pull-left folder"></i>
            <span class="sup pull-left"></span>
            <div class="name editable" contenteditable="false">
                device &nbsp <input type="text" size="5"/>
            </div>
            <ul></ul>
        </li>
    </ul>
</div>-->
