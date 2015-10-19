<?php $this->Html->script('jquery-2.1.3.min', false); ?>
<?php $this->Html->script('underscore.min', false); ?>
<?php $this->Html->script('backbone.min', false); ?>
<?php $this->Html->script('backbone-relational', false); ?>
<?php $this->Html->script('view_entity_depth', false); ?>
<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1><?php echo $ProblemMap['ProblemMap']['name']; ?>
            <small>(<?php echo $this->Html->link("List View", array(
                'controller' => 'problem_maps',
                'action' => 'view_list',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
            <small>(<?php echo $this->Html->link("Tree View", array(
                'controller' => 'problem_maps',
                'action' => 'view_graph',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Retrospection", array(
                'controller' => 'problem_maps',
                'action' => 'view_processreplay',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
        </h1>
    </div>
</div>
<div id='container' class="row-fluid"><ul></ul></div>
