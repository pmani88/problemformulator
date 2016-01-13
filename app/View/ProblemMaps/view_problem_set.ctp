<?php
/**
 * Created by PhpStorm.
 * User: Pradeep
 * Date: 11/18/2015
 * Time: 7:54 PM
 */
$this->Html->css('index-pmap', null, array('inline' => false));
?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th><?php echo $this->Paginator->sort('id', 'ID'); ?></th>
            <th><?php echo $this->Paginator->sort('name', 'Name'); ?></th>
            <th><?php echo $this->Paginator->sort('judge_count', 'No. of Judges'); ?></th>
<!--            <th>--><?php //echo $this->Paginator->sort('score_published', 'Score Published?'); ?><!--</th>-->
<!--            <th>Actions</th>-->
            <th><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
        </tr>
        </thead>
        <tbody>

        <!-- Here is where we loop through our $ProblemMaps array, printing out Problem_map info -->
        <?php foreach ($problem_sets as $problem_set) { ?>
            <tr>
                <td><?php echo $problem_set['ProblemSet']['id']; ?></td>
                <td><?php echo $problem_set['ProblemSet']['name']; ?></td>
                <td><?php echo $problem_set['ProblemSet']['judge_count']; ?></td>
<!--                <td>-->
<!--                    --><?php
//                    if ($problem_set['ProblemSet']['score_published'] == 1) echo 'Yes';
//                    else echo 'No'
//                    ?>
<!--                </td>-->
<!--                <td>Edit / Publish Score</td>-->
                <td><?php echo $problem_set['ProblemSet']['created']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php
echo $this->Paginator->first(' << ', array(), null, array('class' => 'first disabled'));
echo $this->Paginator->prev(' prev', array(), null, array('class' => 'prev disabled'));
echo $this->Paginator->next(' next', array(),  null, array('class' => 'next disabled'));
echo $this->Paginator->last(' >>', array(),  null, array('class' => 'last disabled'));
?>
