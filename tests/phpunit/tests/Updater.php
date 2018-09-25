<?php
/**
 * Class Updater
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP;
use \GP_UnitTestCase;
use \Required\Traduttore\Configuration;
use \Required\Traduttore\Project;
use \Required\Traduttore\Updater as U;

/**
 *  Test cases for \Required\Traduttore\Updater.
 */
class Updater extends GP_UnitTestCase {
	/**
	 * @var P
	 */
	protected $project;

	/**
	 * @var U
	 */
	protected $updater;

	public function setUp() {
		parent::setUp();

		$this->project = new Project(
			$this->factory->project->create(
				[
					'name'                => 'Sample Project',
					'slug'                => 'sample-project',
					'source_url_template' => 'https://github.com/wearerequired/traduttore/blob/master/%file%#L%line%',
				]
			)
		);

		$this->updater = new U( $this->project );
	}

	public function test_update_without_config() {
		$config = new Configuration( dirname( __DIR__ ) . '/data/example-no-config' );

		$result = $this->updater->update( $config );

		$originals = GP::$original->by_project_id( $this->project->get_id() );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $originals );
	}

	public function test_update_with_composer_config() {
		$config = new Configuration( dirname( __DIR__ ) . '/data/example-with-composer' );

		$result = $this->updater->update( $config );

		$originals = GP::$original->by_project_id( $this->project->get_id() );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $originals );
	}

	public function test_update_with_config_file() {
		$config = new Configuration( dirname( __DIR__ ) . '/data/example-with-composer' );

		$result = $this->updater->update( $config );

		$originals = GP::$original->by_project_id( $this->project->get_id() );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $originals );
	}

	public function test_has_no_lock_initially() {
		$this->assertFalse( $this->updater->has_lock() );
	}

	public function test_has_lock_after_adding() {
		$this->updater->add_lock();

		$this->assertTrue( $this->updater->has_lock() );
	}

	public function test_has_no_lock_after_removal() {
		$this->updater->add_lock();
		$this->updater->remove_lock();

		$this->assertFalse( $this->updater->has_lock() );
	}
}