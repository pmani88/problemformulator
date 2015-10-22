<?php
App::uses('CentroidScore', 'Model');

/**
 * CentroidScore Test Case
 *
 */
class CentroidScoreTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.centroid_score',
		'app.problem_set',
		'app.problem_map',
		'app.user',
		'app.entity',
		'app.decomposition',
		'app.link',
		'app.attribute',
		'app.partial_ordering',
		'app.log_entry'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CentroidScore = ClassRegistry::init('CentroidScore');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CentroidScore);

		parent::tearDown();
	}

}
