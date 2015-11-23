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
            <th><?php echo $this->Paginator->sort('score_published', 'Score Published?'); ?></th>
            <th>Actions</th>
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
                <td>
                    <?php
                    if ($problem_set['ProblemSet']['score_published'] == 1) echo 'Yes';
                    else echo 'No'
                    ?>
                </td>
                <td>Edit Publish Score</td>
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
    <!---->
<?php
//echo "<div class='page - title'>Users </div >"; //title
////this 'add new user' button will be used for the next tutorial
//echo "<div style ='float:right;'>";
//    $url = "add /";
//    echo $form->button('Add New User', array('onclick' => "location . href ='".$this->Html->url($url) ."'"));
//echo "</div >";
//echo "<div style ='clear:both;'></div >";
//
//if (sizeOf($users) > 0) { //check if there are user records returned
//    ?>
    <!--    <table>-->
    <!--        <tr>-->
    <!--            <!–-->
    <!--            Here on the table heading (-->
    <!--            <th></th>-->
    <!--            ) is where our SORTING occurs,-->
    <!--            User has to click heading label to sort data in ascending or descending order,-->
    <!--            $paginator->sort('Firstname', 'firstname'); is a CakePHP function that builds the link for sorting-->
    <!--            the first parameter 'Firstname' will be the label-->
    <!--            and the second parameter 'firstname' is actually the database field-->
    <!--            –>-->
    <!--            <th style='text-align: left;'>--><?php //echo $paginator->sort('Firstname', 'firstname'); ?><!--</th>-->
    <!--            <th>--><?php //echo $paginator->sort('Lastname', 'lastname'); ?><!--</th>-->
    <!--            <th>--><?php //echo $paginator->sort('Email', 'email'); ?><!--</th>-->
    <!--            <th>--><?php //echo $paginator->sort('Username', 'username'); ?><!--</th>-->
    <!--            <th>Action</th>-->
    <!--        </tr>-->
    <!--        <tr>-->
    <!--            --><?php
//            foreach ($users as $user) { //we wil loop through the records to DISPLAY DATA
//                echo "<tr >";
//                echo "<td >";
//                    //$user is our foreach variable
//                    //['User'] is from our model/alias
//                    //['firstname'] is the database field
//                    echo "{
//                    $user['User']['firstname']}";
//                echo "</td >";
//                echo "<td >{
//                    $user['User']['lastname']}</td >";
//                echo "<td >{
//                    $user['User']['email']}</td >";
//                echo "<td >{
//                    $user['User']['username']}</td >";
//                echo "<td style ='text - align: center;'>";
//                    //'Edit' and 'Delete' link here will be used for our next tutorials
//                    echo $html->link('Edit', array('action'=>'edit /'.$user['User']['id']), null, null);
//                    echo " / ";
//                    echo $html->link('Delete', array('action'=>'delete /'.$user['User']['id']), null, 'Are you sure you want to delete this record ?');
//                echo "</td >";
//            echo "</tr >";
//        }
//            ?>
    <!--        </tr>-->
    <!--    </table>-->
    <!---->
    <!--    --><?php
//    //here is our PAGINATION part
//    echo "<div class='paging'>";
//
//    //for the first page link
//    //the parameter 'First' is the label, same with other pagination links
//    echo $paginator->first('First');
//    echo " ";
//
//    //if there are previous records, prev link will be displayed
//    if ($paginator->hasPrev()) {
//        echo $paginator->prev('<<');
//    }
//
//    echo " ";
//    //modulus => 2 specifies how many page numbers will be displayed
//    echo $paginator->numbers(array('modulus' => 2));
//    echo " ";
//
//    //there are next records, next link will be displayed
//    if ($paginator->hasNext()) {
//        echo $paginator->next('>>');
//    }
//
//    echo " ";
//    //for the last page link
//    echo $paginator->last('Last');
//
//    echo "</div >";
//
//} else { //if there are no records found, display this
//    echo "<div class='no - records - found'>No Users found .</div >";
//}
//
//?>