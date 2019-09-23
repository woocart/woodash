<?php


use Niteo\WooCart\WooDash\Admin;
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


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::initialize
	 */
	public function testInitialize() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'esc_url',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_admin_url',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'sanitize_text_field',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => true,
			]
		);

		\WP_Mock::expectActionAdded( 'plugins_loaded', [ $admin, 'check_permissions' ], 10 );

		$admin->initialize();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::check_permissions
	 * @covers \Niteo\WooCart\WooDash\Admin::woo_dashboard
	 */
	public function testCheckPermissions() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'current_user_can',
			[
				'return' => true,
			]
		);

		\WP_Mock::expectActionAdded( 'admin_menu', [ $admin, 'change_admin_menu' ], PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_dashboard_setup', [ $admin, 'dashboard_widgets' ], PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'init', [ $admin, 'switch_dashboards' ], 10 );

		\WP_Mock::expectFilterAdded( 'custom_menu_order', [ $admin, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );
		\WP_Mock::expectFilterAdded( 'menu_order', [ $admin, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );

		$admin->check_permissions();
		\WP_Mock::assertHooksAdded();
	}

}
