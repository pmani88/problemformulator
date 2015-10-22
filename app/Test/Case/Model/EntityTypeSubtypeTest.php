<?php
App::uses('EntityTypeSubtype', 'Model');

/**
 * EntityTypeSubtype Test Case
 *
 */
class EntityTypeSubtypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.entity_type_subtype'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->EntityTypeSubtype = ClassRegistry::init('EntityTypeSubtype');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->EntityTypeSubtype);

		parent::tearDown();
	}

}
