<?php
App::uses('JudgesScore', 'Model');

/**
 * JudgesScore Test Case
 *
 */
class JudgesScoreTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.judges_score',
		'app.entity',
		'app.problem_map',
		'app.user',
		'app.tutorial_type',
		'app.problem_set',
		'app.centroid_score',
		'app.link',
		'app.decomposition',
		'app.partial_ordering',
		'app.attribute',
		'app.log_entry'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->JudgesScore = ClassRegistry::init('JudgesScore');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->JudgesScore);

		parent::tearDown();
	}

}
