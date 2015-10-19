<?php $this->Html->css('view_list', null, array('inline' => false)); ?>
<?php $this->Html->css('view_processreplay', null, array('inline' => false)); ?>
<?php $this->Html->css('ui-lightness/jquery-ui-1.8.20.custom', null, array('inline' => false)); ?>
<?php $this->Html->css('owl.carousel', null, array('inline' => false)); ?>
<?php $this->Html->css('owl.theme', null, array('inline' => false)); ?>
<?php $this->Html->script('jquery-ui-1.8.20.custom.min', false); ?>
<?php $this->Html->script('jquery-1.9.1.min', false); ?>
<?php $this->Html->script('owl.carousel', false); ?>
<?php $this->Html->script('view_processreplays', false); ?>

<div class="row-fluid">
    <div class="span10 offset1 page-header">
        <h1>Process Replay
            <small>(<?php echo $this->Html->link("List View", array(
                'controller' => 'problem_maps',
                'action' => 'view_list',
                $ProblemMapId
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Tree View", array(
                'controller' => 'problem_maps',
                'action' => 'view_graph',
                $ProblemMapId
            )); ?>)</small>
            <small>(<?php echo $this->Html->link("Network View", array(
                'controller' => 'problem_maps',
                'action' => 'view_graphNew',
                $ProblemMapId
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Objective Tree", array(
                'controller' => 'problem_maps',
                'action' => 'view_objtree',
                $ProblemMapId
            )); ?>)</small>
			<small>(<?php echo $this->Html->link("Download Specs", array(
                'controller' => 'problem_maps',
                'action' => 'download_spec',
                $ProblemMapId
            )); ?>)</small>
		</h1>
		<div class="color-code">
			<small style="font-size: 100%;">
				<u>Actions</u>: &nbsp;
				<font><span style="display: inline-block; padding: 5px; background: #39D339; margin-right: 5px;"></span>Add</font> | 
				<font><span style="display: inline-block; padding: 5px; background: #d3dba8; margin-right: 5px;"></span>Rename</font> | 
				<font><span style="display: inline-block; padding: 5px; background: #FF8500; margin-right: 5px;"></span>Decomposition</font> | 
				<font><span style="display: inline-block; padding: 5px; background: #79D6FF; margin-right: 5px;"></span>Link</font> | 
				<font><span style="display: inline-block; padding: 5px; background: #D4D4D4; margin-right: 5px;"></span>Delete</font>
			</small>
		</div>
    </div>
</div>
<div id="perception-container">
<div class="customNavigation">
	<a class="btn prev">Prev</a>
	<a class="btn next">Next</a>
	<a class="btn play">Play</a>
	<a class="btn stop">Stop</a>
</div>
<div id="select-perception-div">
<h2 style="display:inline; margin-left:30px;">Choose Perception:</h2>

<select class="select-perception" disabled>
  <option value=""></option>
  <optgroup label="I wanted to">
    <option value="I wanted to explore a new idea">explore a new idea</option>
    <option value="I wanted to gather information">gather information</option>
    <option value="I wanted to focus on one aspect of the problem/smaller problem">focus on one aspect of the problem/smaller problem</option>
  </optgroup>
  <optgroup label="I knew this was">
    <option value="I knew this was relevant to what the problem statement said">relevant to what the problem statement said</option>
    <option value="I knew this was not going to work">not going to work</option>
    <option value="I knew this was not necessary">not necessary</option>
    <option value="I knew this was important">important</option>
  </optgroup>
  <optgroup label="I had knowledge">
    <option value="I had knowledge of a similar problem">of a similar problem</option>
    <option value="I had knowledge on the domain / studied the subject">on the domain / studied the subject</option>
  </optgroup>
  <optgroup label="I remembered">
    <option value="I remembered a similar concept relevant to this problem">a similar concept relevant to this problem</option>
    <option value="I remembered an interesting idea that was not relevant but I wanted to keep it for the future">an interesting idea that was not relevant but I wanted to keep it for the future</option>
  </optgroup>
  <optgroup label="I copied this from">
    <option value="I copied this from the problem statement">the problem statement</option>
    <option value="I copied this from another part of the formulation">another part of the formulation</option>
  </optgroup>
  <optgroup label="I didn’t know">
    <option value="I didn’t know what to do">what to do</option>
    <option value="I didn’t know how this subject/didn’t have domain knowledge">how this subject/didn’t have domain knowledge</option>
  </optgroup>
  <optgroup label="I was not sure if">
    <option value="I was not sure if the idea was feasible">the idea was feasible</option>
  </optgroup>
</select>
</div>
<div id="owl-process-replay" class="owl-carousel">
	<?php
		foreach($ProcessReplays as $step){
	?>
	<div>
		<div class="action-message">
			<h2>Action: <?php echo $step['ProcessReplays']['action'];?></h2>
			<h2 class="perception-label">Perception:</h2> 
			<input id="perception-input-<?php echo $step['ProcessReplays']['id'];?>" class="perception-input row_<?php echo $step['ProcessReplays']['id'];?>" type="text" value="<?php echo $step['ProcessReplays']['perception'];?>" disabled=false />
			<button type="submit" class="btn-primary save-perception row_<?php echo $step['ProcessReplays']['id'];?>" onclick="save_perception(<?php echo $step['ProcessReplays']['id'];?>);" style="display: none;">
				<i class="icon-plus"></i>
			</button>
			<button type="submit" class="btn-primary edit-perception row_<?php echo $step['ProcessReplays']['id'];?>" onclick="edit_perception(<?php echo $step['ProcessReplays']['id'];?>);">
				<i class="icon-edit"></i>
			</button>
		</div>
		<div class="html-div"><?php print_r($step['ProcessReplays']['htmlstring']);?></div>
	</div>
	<?php } ?>
</div>
</div>


