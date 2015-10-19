<?php
App::uses('CentriodScore', 'Model');

/**
 * CentriodScore Test Case
 *
 */
class CentriodScoreTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.centriod_score'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CentriodScore = ClassRegistry::init('CentriodScore');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CentriodScore);

		parent::tearDown();
	}

}
