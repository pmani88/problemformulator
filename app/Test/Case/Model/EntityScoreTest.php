<?php
App::uses('EntityScore', 'Model');

/**
 * EntityScore Test Case
 *
 */
class EntityScoreTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.entity_score',
		'app.entity',
		'app.problem_map',
		'app.user',
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
		$this->EntityScore = ClassRegistry::init('EntityScore');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->EntityScore);

		parent::tearDown();
	}

}
