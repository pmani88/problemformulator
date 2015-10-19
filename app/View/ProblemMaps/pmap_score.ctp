<?php $this->Html->css('view_list', null, array('inline' => false)); ?>
<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1>
            <?php echo $ProblemMap['ProblemMap']['name']; ?>
            <small>(<?php echo $this->Html->link("Tree View", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_graph',
                    $ProblemMap['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Network View", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_graphNew',
                    $ProblemMap['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Objective Tree", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_objtree',
                    $ProblemMap['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Download Specs", array(
                    'controller' => 'problem_maps',
                    'action' => 'download_spec',
                    $ProblemMap['ProblemMap']['id']
                )); ?>)
            </small>
            <small>(<?php echo $this->Html->link("Retrospection", array(
                    'controller' => 'problem_maps',
                    'action' => 'view_processreplay',
                    $ProblemMap['ProblemMap']['id']
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
<table border="1">
    <tr>
        <th>Artifacts</th>
    </tr>
    <tr>
        <td>
            <table border="1">
                <tr>
                    <th>Entity</th>
                    <th>Score</th>
                </tr>
                <tr>
                    <td>device</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>thrower</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>wheels</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>slider</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>picker</td>
                    <td>1</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th>5</th>
                </tr>
            </table>
        </td>
    </tr>

</table>
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
