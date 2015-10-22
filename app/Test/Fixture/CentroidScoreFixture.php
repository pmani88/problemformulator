<?php
/**
 * CentroidScoreFixture
 *
 */
class CentroidScoreFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'entity_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'score' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'threshold' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => '2,1'),
		'entity_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entity_subtype' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'problem_set_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'entity_name' => 'Lorem ipsum dolor sit amet',
			'score' => 1,
			'threshold' => 1,
			'entity_type' => 'Lorem ipsum dolor sit amet',
			'entity_subtype' => 'Lorem ipsum dolor sit amet',
			'problem_set_id' => 1,
			'created' => '2015-10-22 01:09:17'
		),
	);

}
