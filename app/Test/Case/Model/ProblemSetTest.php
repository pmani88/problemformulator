<?php
App::uses('ProblemSet', 'Model');

/**
 * ProblemSet Test Case
 *
 */
class ProblemSetTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.problem_set',
		'app.centroid_score',
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
		$this->ProblemSet = ClassRegistry::init('ProblemSet');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProblemSet);

		parent::tearDown();
	}

}
