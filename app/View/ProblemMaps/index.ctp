<!-- File: /app/View/ProblemMaps/index.ctp -->
<?php
$this->Html->css('index-pmap', null, array('inline' => false));
//echo $admin;
?>

<div class="page-header text-center">
    <h1>Available Problem Maps</h1>

    <p>
        <?php
        echo $this->Html->link('Add New Problem Map', array('action' => 'add'));
        if ($admin) {
            echo ' | ';
            echo $this->Html->link('View Problem Set', array('action' => 'view_problem_set'));
            echo ' | ';
            echo $this->Html->link('Add New Problem Set', array('action' => 'add_problem_set'));
        }
        ?>
    </p>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Id</th>
        <?php
        if ($admin) {
            echo "<th>Owner Id</th>";
            echo "<th>Predicates</th>";
            echo "<th>Hierarchy Depth</th>";
            echo "<th>Raw Score</th>";
        }
        ?>
        <th>Name</th>
        <th>Description</th>
        <th>Problem Set</th>
        <th>Score</th>
        <th>Actions</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody>

    <!-- Here is where we loop through our $ProblemMaps array, printing out Problem_map info -->

    <?php foreach ($ProblemMaps as $problem_map): ?>
        <tr>
            <td><?php echo $problem_map['ProblemMap']['id']; ?></td>
            <?php
            if ($admin) {
                echo "<td>" . $problem_map['User']['id'] . "</td>";
                echo "<td>" . $this->Html->link("Predicates", array(
                        'controller' => 'problem_maps',
                        'action' => 'view_predicate',
                        $problem_map['ProblemMap']['id']
                    )) . "</td>";
                echo "<td>" . $this->Html->link("Depth", array(
                        'controller' => 'problem_maps',
                        'action' => 'view_entity_depth',
                        $problem_map['ProblemMap']['id']
                    )) . "</td>";
                echo "<td>" . $this->Html->link("Score", array(
                        'controller' => 'problem_maps',
                        'action' => 'pmap_score',
                        $problem_map['ProblemMap']['id']
                    )) . "</td>";
                echo "<td>" . $this->Html->link("CSV Score", array(
                        'controller' => 'problem_maps',
                        'action' => 'download_scoring_stats',
                        $problem_map['ProblemMap']['id']
                    )) . "</td>";
            }
            ?>
            <td>
                <?php echo $this->Html->link($problem_map['ProblemMap']['name'], array(
                    'controller' => 'problem_maps',
                    'action' => 'view_list',
                    $problem_map['ProblemMap']['id']
                )); ?>
            </td>
            <td><?php echo $problem_map['ProblemMap']['description']; ?></td>
            <td><?php echo $problem_map['ProblemSet']['name']; ?></td>
            <td>
                <?php
                $is_scored = $problem_map['ProblemMap']['is_scored'];
                if ($is_scored == 0) echo '<i>Not Scored</i>';
                elseif ($is_scored == 1) echo $problem_map['ProblemMap']['total_score'];
                elseif ($is_scored == 2) echo '<i>In Progress</i>'
                ?>
            </td>
            <td>
                <?php echo $this->Html->link('Edit', array(
                    'action' => 'edit',
                    $problem_map['ProblemMap']['id']
                )); ?>
                <!---
				<?php echo $this->Form->postLink('Delete', array(
                    'action' => 'delete',
                    $problem_map['ProblemMap']['id']
                ), array(
                    'confirm' => 'Are you sure?'
                ));
                ?>
			--->
            </td>
            <td><?php echo $problem_map['ProblemMap']['created']; ?></td>
        </tr>
        <?php
    endforeach; ?>
    </tbody>
</table>
