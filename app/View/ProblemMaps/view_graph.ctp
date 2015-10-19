<?php $this->Html->css('view_graph', null, array('inline' => false)); ?>
<?php $this->Html->script('underscore.min', false); ?>
<?php $this->Html->script('backbone.min', false); ?>
<?php $this->Html->script('backbone-relational', false); ?>
<?php $this->Html->script('jquery-sortable.min', false); ?>
<?php $this->Html->script('bootstrap-contextmenu', false); ?>
<?php $this->Html->script('d3js/d3.v3.min.js', false); ?>
<?php $this->Html->script('view_graph', false); ?>

<script type="text/template" id="entity-template">
<% if (num_decomps > 0 && Entity.current_decomposition == null) { %>
    <i class="icon icon-folder-close pull-left"></i>
<% } else if (num_decomps > 0) { %>
    <i class="icon icon-folder-open pull-left"></i>
<% } else { %>
    <i class="icon icon-file pull-left"></i>
<% } %>
<% if (num_decomps > 1) { %>
    <span class='sup pull-left'><%= num_decomps %></span>
<% } else { %>
    <span class='sup pull-left'></span>
<% } %>
<div class='name pull-left editable' contenteditable=false>
    <%= Entity.name %>
</div>
<a class='destroy pull-right' href="#"><i class="icon-trash"></i></a>
<div class="clear"></div>
</script>

<script type="text/template" id="entity-tab-template">
<div class="row-fluid">
    <h2>
    <%= title %>
    <a href="#" id="<%= type %>-tooltip"><i class="icon-question-sign"></i></a>
    </h2>
</div>
<div class="row-fluid">
    <div class="input-append entity-dialog">
        <input id='new-<%= type %>' type='text' class='entity-input' 
        placeholder='New <%= type %>'></input>
        <button type='submit' class='btn-primary entity-input'>
            <i class="icon-plus"></i>
        </button>
    </div>
</div>
<hr>
<div class="row-fluid">
    <ul id='<%= type %>' class='entity-list'>
    </ul>
</div>
</script>

<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1><?php echo $ProblemMap['ProblemMap']['name']; ?>
            <small>(<?php echo $this->Html->link("List View", array(
                'controller' => 'problem_maps',
                'action' => 'view_list',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
            <small>(<?php echo $this->Html->link("Network View", array(
                'controller' => 'problem_maps',
                'action' => 'view_graphNew',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
            <small>(<?php echo $this->Html->link("Objective Tree", array(
                'controller' => 'problem_maps',
                'action' => 'view_objtree',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Download Specs", array(
                'controller' => 'problem_maps',
                'action' => 'download_spec',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Retrospection", array(
                'controller' => 'problem_maps',
                'action' => 'view_processreplay',
                $ProblemMap['ProblemMap']['id']
            )); ?>)</small>
            <!--
            <div class="navbar-search pull-right">
                <div class="input-append">
                    <input type="text" class="search-query" placeholder="Search entities">
                    <span class="forsearch"><i class='icon-search'></i></span>
                </div>
            </div>
            -->
        </h1>
    </div>
</div>
<div id='tabs' class="row-fluid">
</div>
