<?php

$this->CSV->addRow($header);
foreach ($table_rows as $table_row) {
    $this->CSV->addRow($table_row);
}

$this->CSV->addRow($total_entities);
$this->CSV->addRow($total_entities_scored);
$this->CSV->addRow($total_entities_not_scored);

$fn = 'RawScore_'.$problem_map['ProblemMap']['id'];
echo  $this->CSV->render($fn);