<?php


use Niteoweb\WooCart\WooDash\Admin;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase {

	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 */
	public function testConstructor() {
		$admin = new Admin();

		\WP_Mock::expectActionAdded( 'admin_init', [ $admin, 'initialize' ], 10 );

		$admin->__construct();
		\WP_Mock::assertHooksAdded();
	}

}
