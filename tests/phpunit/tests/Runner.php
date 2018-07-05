<?php
/**
 * Class Runner
 *
 * @package Traduttore\Tests
 */

namespace Required\Traduttore\Tests;

use \GP_UnitTestCase;
use \Required\Traduttore\Project;
use \Required\Traduttore\Updater;
use \Required\Traduttore\Runner as R;
use \Required\Traduttore\Loader\GitHub as Loader;

/**
 *  Test cases for \Required\Traduttore\Runner.
 */
class Runner extends GP_UnitTestCase {
	/**
	 * @var P
	 */
	protected $project;

	/**
	 * @var R
	 */
	protected $runner;

	/**
	 * @var Loader
	 */
	protected $loader;

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

		$test_path = get_temp_dir() . 'traduttore-test-dir';

		$this->loader = $this->createMock( Loader::class );
		$this->loader->method( 'get_local_path' )->willReturn( $test_path );
		$this->loader->method( 'download' )->willReturn( $test_path );

		$updater = $this->createMock( Updater::class );
		$updater->method( 'update' )->willReturn( true );

		$this->runner = new R( $this->loader, $updater );
		$this->runner->delete_local_repository();
	}

	public function test_delete_local_repository() {
		mkdir( $this->loader->get_local_path() );
		touch( $this->loader->get_local_path() . '/foo.txt' );

		$this->assertTrue( file_exists( $this->loader->get_local_path() . '/foo.txt' ) );
		$this->assertTrue( $this->runner->delete_local_repository() );
		$this->assertFalse( file_exists( $this->loader->get_local_path() . '/foo.txt' ) );
	}

	public function test_run() {
		$result = $this->runner->run();

		$this->assertTrue( $result );
	}

	public function test_run_with_existing_repository() {
		$result1 = $this->runner->run();
		$result2 = $this->runner->run();

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );
	}

	public function test_run_and_delete_existing_repository() {
		$result1 = $this->runner->run();
		$this->runner->delete_local_repository();
		$result2 = $this->runner->run();

		$this->assertTrue( $result1 );
		$this->assertTrue( $result2 );
	}

	public function test_run_stops_when_project_is_locked() {
		$updater = $this->createMock( Updater::class );
		$updater->method( 'has_lock' )->willReturn( true );

		$this->runner = new R( $this->loader, $updater );

		$result = $this->runner->run();

		$this->assertFalse( $result );
	}

	public function test_run_stops_when_download_fails() {
		$loader = $this->createMock( Loader::class );
		$loader->method( 'download' )->willReturn( null );
		$updater = $this->createMock( Updater::class );

		$this->runner = new R( $loader, $updater );

		$result = $this->runner->run();

		$this->assertFalse( $result );
	}
}
