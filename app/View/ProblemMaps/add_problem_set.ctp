<!-- app/View/ProblemMaps/add_problem_set.ctp -->
<div class="row-fluid" xmlns="http://www.w3.org/1999/html">
    <div class="span5 offset3">
        <?php echo $this->Form->create('ProblemSet', array(
            'class' => 'form-horizontal',
            'inputDefaults' => array(
                'format' => array('before', 'label', 'between', 'input', 'error',
                    'after'),
                'div' => array('class' => 'control-group'),
                'label' => array('class' => 'control-label'),
                'between' => '<div class="controls">',
                'after' => '</div>',
                'error' => array('attributes' => array('wrap' => 'span', 'class' =>
                    'help-inline')),
            )));
        ?>
        <fieldset>
            <legend><?php echo __('Create Problem Set'); ?></legend>
            <?php
            echo $this->Form->input('name');
            echo $this->Form->input('judge_count',array('type' => 'number', 'default' => 0, 'min' => 0));
            echo '<div class="controls"><button type="submit" class="btn btn-primary">Create
                Problem Set</button> ';
            echo $this->Html->link('Cancel', array('controller' => 'problem_maps', 'action' =>
                'index'), array('class' => 'btn'));
            echo '</div>';
            ?>
        </fieldset>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
