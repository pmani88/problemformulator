<?php
App::uses('AppModel', 'Model');
/**
 * ProblemSet Model
 *
 * @property CentroidScore $CentroidScore
 * @property ProblemMap $ProblemMap
 */
class ProblemSet extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'nameRule-1' => array(
				'rule' => 'isUnique',
				'message' => 'Problem Set name already exist.'
			),
			'nameRule-2' => array(
				'rule' => 'notEmpty',
				'message' => 'Problem Set name is required.'
			)
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'CentroidScore' => array(
			'className' => 'CentroidScore',
			'foreignKey' => 'problem_set_id',
			'dependent' => false
		),
		'ProblemMap' => array(
			'className' => 'ProblemMap',
			'foreignKey' => 'problem_set_id',
			'dependent' => false
		)
	);

}
