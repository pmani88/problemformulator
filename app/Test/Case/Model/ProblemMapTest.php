<?php
App::uses('ProblemMap', 'Model');

/**
 * ProblemMap Test Case
 *
 */
class ProblemMapTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		$this->ProblemMap = ClassRegistry::init('ProblemMap');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProblemMap);

		parent::tearDown();
	}

/**
 * testIsOwnedBy method
 *
 * @return void
 */
	public function testIsOwnedBy() {
	}

}
