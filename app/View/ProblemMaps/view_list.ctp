<?php $this->Html->css('view_list', null, array('inline' => false)); ?>
<?php $this->Html->script('underscore.min', false); ?>
<?php $this->Html->script('backbone.min', false); ?>
<?php $this->Html->script('backbone-relational', false); ?>
<?php $this->Html->script('jquery-sortable.min', false); ?>
<?php $this->Html->script('bootstrap-contextmenu', false); ?>
<?php $this->Html->script('view_list', false); ?>
<?php $this->Html->script('tutorial_prompts', false); ?>
<?php $this->Html->script('view_processreplays', false); ?>
<?php $this->Html->script('raphael-min', false); ?>
<?php $this->Html->script('json2.min', false); ?>
<?php $this->Html->script('raphael.sketchpad', false); ?>
<?php $this->Html->script('view_sketchpad', false); ?>

<script type="text/template" id="entity-template">
<% if (num_decomps > 0 && Entity.current_decomposition == null) { %>
    <i class="icon icon-folder-close pull-left folder"></i>
<% } else if (num_decomps > 0) { %>
    <i class="icon icon-folder-open pull-left folder"></i>
<% } else { %>
    <i class="icon icon-file pull-left"></i>
<% } %>
<% if (num_decomps > 1) { %>
    <span class='sup pull-left'><%= num_decomps %></span>
<% } else { %>
    <span class='sup pull-left'></span>
<% } %>
<div class='name editable' contenteditable=false>
    <%= Entity.name %>
</div>
<a class='destroy pull-right' href="#">X</a>
</script>


<textarea id="entity-subtypes" style="display: none;">
<?php echo json_encode($subtypes);?>
</textarea>

<script type="text/template" id="entity-tab-template">
<div class="row-fluid">
    <h2><%= title %>
    <a href="#" id="<%= type %>-tooltip"><i class="icon-question-sign"></i></a>
    </h2>
    <!--<a href="#" id="<%= type %>-csv" class="download-csv">(download csv)</a>-->
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
<div class="entity-dialog">
	<select id="entity-subtypes" class="<%= type %>-subtypes" type="<%= type %>" style="width: 100%;"></select>
</div>
<!--<hr>-->
<div class="row-fluid scroll" <?php if(!$ProblemMap['ProblemMap']['tutorial_on']) echo 'style="height: 600px;"'?>>
    <ul id='<%= type %>' class='entity-list'>
    </ul>
</div>
</script>

<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1>
			<i class="icon-refresh" style="margin: 8px;" onclick="reset_invalid_decompid();" title="Refresh Data"></i>
			<?php echo $ProblemMap['ProblemMap']['name']; ?>
            <small>(<?php echo $this->Html->link("Tree View", array(
                'controller' => 'problem_maps',
                'action' => 'view_graph',
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
			
            <div class="navbar-search pull-right">
                <div class="input-append">
                    <input type="text" class="search-query" placeholder="Search entities">
                    <span class="forsearch"><i class='icon-search'></i></span>
                </div>
            </div>
			
			<!-- Tutorial Prompt On/Off -->
			<div class="navbar-tutorial pull-right">
				<table id="tutorial">
					<tr id="tutorial_on">
						<td>Enable Wizard: </td>
						<td><input id="tutorial_switch" type="checkbox" onclick="tutorial_enable(<?php echo $ProblemMap['ProblemMap']['id'];?>, <?php echo $ProblemMap['ProblemMap']['tutorial_type_id'];?>);" <?php if($ProblemMap['ProblemMap']['tutorial_on']) echo 'checked';?>></td>
					</tr>
					<tr id="tutorial_type" <?php if(!$ProblemMap['ProblemMap']['tutorial_on']) echo 'style="display: none";';?>>
						<td>Process 1: <input id="tutorial_type" type="radio" value="1" name="tutorial_type" onclick="tutorial_switch(<?php echo $ProblemMap['ProblemMap']['id'];?>, <?php echo $ProblemMap['ProblemMap']['tutorial_on'];?>, 1);" <?php if($ProblemMap['ProblemMap']['tutorial_type_id']==1) echo 'checked';?>></td>
						<td>Process 2: <input id="tutorial_type" type="radio" value="2" name="tutorial_type" onclick="tutorial_switch(<?php echo $ProblemMap['ProblemMap']['id'];?>, <?php echo $ProblemMap['ProblemMap']['tutorial_on'];?>, 2);" <?php if($ProblemMap['ProblemMap']['tutorial_type_id']==2) echo 'checked';?>></td>
					</tr>
				</table>
				
			</div>
        </h1>
    </div>
</div>

<div id="prompt_container" <?php if(!$ProblemMap['ProblemMap']['tutorial_on']) echo 'style="display: none";';?>>
	<div id="tutorial_prompt">
	
	</div>
</div>
<div id='tabs' class="row-fluid">
</div>

<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"
        aria-hidden="true">x</button>
    <h3 id="myModalLabel">Which decomposition?</h3>
  </div>
  <div class="modal-body temp-decomps">
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
  </div>
</div>

<div id="context-menu">
    <ul class="dropdown-menu" role="menu">
    </ul>
</div>

<div id="edit-subtype-overlay" class="overlay">
	<div id="edit-subtype-menu" class="dropdown-menu">
		<label>Choose a subtype:</label>
		<div id="edit-subtype-options">
			<select>
			</select>
		</div>
		<div id="save_subtype_msg" class="hidden" style="margin:5px 0; color:green;">
			Subtype modified successfully.
		</div>
		<button id="save_subtype">Submit</button>
		<button onclick="closeEditSubtype();">Close</button>
	</div>
</div>
<div id="sketchpad-overlay" class="overlay">
	<div id="sketchpad-container" class="dropdown-menu">
		<div id="sketchpad-viewer"></div>
		<input id="sketch-entity-id" type="hidden"/>
		<textarea id="sketchpad-data" cols="30" rows="6"></textarea>
		<button id="sketch-edit">Edit</button>
		<button id="sketch-draw" style="display:none;">Draw</button>
		<!--<button id="sketch-erase" style="display:none;">Erase</button>-->
		<button id="sketch-undo" style="display:none;">Undo</button>
		<button id="sketch-redo" style="display:none;">Redo</button>
		<button id="sketch-clear" style="display:none;">Clear</button>
		<button id="sketch-save" style="display:none;">Save</button>
		<button id="sketch-delete" style="display:none;">Delete</button>
		<button onclick="closeSketchViewer();">Close</button>
	</div>
</div>

<!--
<div class="overlay">
	<div id='facebook' >
		<div id='block_1' class='facebook_block'></div>
		<div id='block_2' class='facebook_block'></div>
		<div id='block_3' class='facebook_block'></div>
	</div>
</div>-->
