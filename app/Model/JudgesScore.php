<?php
App::uses('AppModel', 'Model');
/**
 * JudgesScore Model
 *
 * @property Entity $Entity
 * @property User $User
 * @property ProblemMap $ProblemMap
 */
class JudgesScore extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Entity' => array(
			'className' => 'Entity',
			'foreignKey' => 'entity_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ProblemMap' => array(
			'className' => 'ProblemMap',
			'foreignKey' => 'problem_map_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
