<?php
App::uses('AppModel', 'Model');
/**
 * ProblemMap Model
 *
 * @property User $User
 * @property TutorialType $TutorialType
 * @property ProblemSet $ProblemSet
 */
class ProblemMap extends AppModel {

	// model name
	public $name = 'ProblemMap';
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		),
		'TutorialType' => array(
			'className' => 'TutorialType',
			'foreignKey' => 'tutorial_type_id'
		),
		'ProblemSet' => array(
			'className' => 'ProblemSet',
			'foreignKey' => 'problem_set_id'
		)
	);

	public $hasMany = array(
		'Entities' => array(
			'className' => 'Entity',
			'dependent' => true
		),
		'Links' => array(
			'className' => 'Link',
			'dependent' => true
		),
		'Decompositions' => array(
			'className' => 'Decomposition',
			'dependent' => true
		),
		'PartialOrderings' => array(
			'className' => 'PartialOrdering',
			'dependent' => true
		),
		'Attributes' => array(
			'className' => 'Attribute',
			'dependent' => true
		),
		'LogEntries' => array(
			'className' 	=> 'LogEntry',
			'dependent'		=> true
		)
	);

	// model validation
	public $validate = array(
		'name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'A name is required'
			)
		)
	);

	// method to determine if a user owns a problem map
	public function isOwnedBy($pmap, $user) {
		//return true;
		return $this->field('id', array('id' => $pmap, 'user_id' => $user)) === $pmap;
	}
}
