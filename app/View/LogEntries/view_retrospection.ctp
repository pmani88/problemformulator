<?php $this->Html->script('jquery-ui-1.8.20.custom.min', false); ?>
<?php $this->Html->css('ui-lightness/jquery-ui-1.8.20.custom', null, array('inline' => false)); ?>
<?php $this->Html->script('jquery-1.9.1.min', false); ?>
<?php $this->Html->script('view_retrospection', false); ?>
<style>
input#perception {
	margin: 0;
}
</style>
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
			<small>(<?php echo $this->Html->link("Download Specs", array(
                'controller' => 'problem_maps',
                'action' => 'download_spec',
                $ProblemMapId
            )); ?>)</small>
		</h1>
    </div>
</div>
<div>
	<table cellpadding="10">
		<?php
		//print_r($ProcessLog);
			$tableHeaders = $this->Html->tableHeaders(array(
				//$this->Paginator->sort('id', 'Id'),
				"Action",
				"Details",
				"Perception"
			));
			echo $tableHeaders;
			//$counter = 1;
			$rows = array();
			foreach($ProcessLog as $step){
				$step_arr = explode(",",$step["LogEntry"]["entry"]);
				$controller = str_replace("Controller", "", $step_arr[0]);
				$details = '';
				//echo count($step_arr);
				if($controller == "Entities"){
					if(array_key_exists(3, $step_arr)){
						$details.= "<b>Entity: </b> <i>".$step_arr[3]."</i><br>";
					}
					if(array_key_exists(4, $step_arr)){
						$details.= "<b>Type: </b> <i>".$step_arr[4]."</i><br>";
					}
					if(array_key_exists(7, $step_arr)){
						$details.= "<b>Subtype: </b> <i>".$step_arr[7]."</i><br>";
					}
					/*
					if(array_key_exists(6, $step_arr)){
						$details.= "<b>Entity: </b> <i>".$step_arr[3]."</i>";
					}
					if(array_key_exists(7, $step_arr)){
						$details.= "<b>Entity: </b> <i>".$step_arr[3]."</i>";
					}
					*/
				} else {
				
				
				}
				
				$rows[] = array(
					//$counter++,
					//$step["LogEntry"]["id"],
					ucfirst(trim($step_arr[1]))." ".$controller,
					$details,
					'<input id="perception" class="row_'.$step["LogEntry"]["id"].'" type="text" value="'.$step["LogEntry"]["perception"].'"><button onclick="save_perception('.$step["LogEntry"]["id"].')">Save</button>'
				);
			}
			
			echo $this->Html->tableCells($rows);
		?>
	</table>
	<div class="paging">
		<?php
			echo "<br>";
			echo $this->Paginator->first('First');
			echo "  ";
			echo $this->Paginator->prev('Prev', null, null, array('class' => 'disable'));
			echo "  ";
			echo $this->Paginator->numbers();
			echo "  ";
			echo $this->Paginator->next('Next', null, null, array('class' => 'disable'));
			echo "  ";
			echo $this->Paginator->last('Last');
			echo "<br><br>";
		?>
	</div>
</div>

