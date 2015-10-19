<?php $this->Html->script('jquery-sortable.min', false); ?>
<?php $this->Html->script('bootstrap-contextmenu', false); ?>
<?php $this->Html->script('print_objtree_1', false); ?>
<?php $this->Html->script('jquery-ui-1.8.20.custom.min', false); ?>
<?php $this->Html->css('ui-lightness/jquery-ui-1.8.20.custom', null, array('inline' => false)); ?>

<script src="http://d3js.org/d3.v3.min.js"></script>
<style>
	.node {
  cursor: pointer;
}

.node circle {
  fill: #fff;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.node text {
  font: 12px sans-serif;
}

.link {
  fill: none;
  stroke-width: 1.5px;
}
</style>
<textarea id='objtree_data' style='display:none;'>
	<?php echo json_encode($treedata);?>
</textarea>
<div id="objtree_graph">
	<div>
		<h3 style="display: inline-block"><?php echo $treedata['name']." Objective Tree" ?></h3>
		<div id="print_objtree" style="margin-left: 15px;">Print</div>
	</div>
</div>

