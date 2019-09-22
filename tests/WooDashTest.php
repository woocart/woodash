<?php


use Niteoweb\WooCart\WooDash\WooDash;
use PHPUnit\Framework\TestCase;

class WooDashTest extends TestCase {

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
	 * @covers \Niteo\WooCart\WooDash\WooDash::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 */
	public function testConstructor() {
		$woodash = new WooDash();

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);

		$woodash->__construct();
	}

}
