<?php


use Niteo\WooCart\WooDash\WooDash;
use Niteo\WooCart\WooDash\Admin;
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
	 * @covers \Niteo\WooCart\WooDash\WooDash::activate
	 * @covers \Niteo\WooCart\WooDash\Admin::dashboard_meta_order
	 */
	public function testActivate() {
		$woodash = new WooDash();

		\WP_Mock::userFunction(
			'update_option',
			[
				'called' => 1,
				'args'   => [
					'woodash_options',
					'*',
				],
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_current_user_id',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			[
				'called' => 1,
				'args'   => [
					1,
					'meta-box-order_dashboard',
					true,
				],
				'return' => 'Correct Value',
			]
		);

		\WP_Mock::userFunction(
			'set_transient',
			[
				'called' => 1,
				'args'   => [
					'woodash_plugin-activation-notice',
					true,
					60 * 60 * 24,
				],
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'maybe_serialize',
			[
				'called' => 1,
				'return' => 'Incorrect Value',
			]
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			[
				'called' => 1,
				'args'   => [
					1,
					'meta-box-order_dashboard_old',
					'*',
				],
			]
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			[
				'called' => 1,
				'args'   => [
					1,
					'meta-box-order_dashboard',
					'*',
				],
			]
		);

		$woodash->activate();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\WooDash::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\WooDash::deactivate
	 * @covers \Niteo\WooCart\WooDash\Admin::reverse_dashboard_meta_order
	 * @covers \Niteo\WooCart\WooDash\Admin::remove_meta_backup
	 */
	public function testDeactivate() {
		$woodash = new WooDash();

		\WP_Mock::userFunction(
			'delete_option',
			[
				'called' => 1,
				'args'   => [
					'woodash_options',
				],
			]
		);

		\WP_Mock::userFunction(
			'get_current_user_id',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			[
				'return' => true,
				'args'   => [
					1,
					'meta-box-order_dashboard_old',
					true,
				],
				'return' => '',
			]
		);

		\WP_Mock::userFunction(
			'delete_user_meta',
			[
				'return' => true,
				'args'   => [
					1,
					'meta-box-order_dashboard',
				],
			]
		);

		\WP_Mock::userFunction(
			'delete_user_meta',
			[
				'return' => true,
				'args'   => [
					1,
					'meta-box-order_dashboard_old',
				],
			]
		);

		$woodash->deactivate();
	}

}
