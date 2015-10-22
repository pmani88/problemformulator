<?php
App::uses('AppModel', 'Model');
/**
 * CentroidScore Model
 *
 * @property ProblemSet $ProblemSet
 */
class CentroidScore extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'ProblemSet' => array(
			'className' => 'ProblemSet',
			'foreignKey' => 'problem_set_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
