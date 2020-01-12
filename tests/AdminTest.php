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

		\WP_Mock::expectActionAdded( 'wp_loaded', [ $admin, 'check_permissions' ], 10 );

		$admin->__construct();
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
			'is_admin',
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
			'current_user_can',
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
			'esc_url',
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

		$admin->check_permissions();
		\WP_Mock::assertHooksAdded();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::woo_dashboard
	 */
	public function testWooDashboard() {
		$admin = new Admin();

		\WP_Mock::expectActionAdded( 'admin_menu', [ $admin, 'change_admin_menu' ], PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_dashboard_setup', [ $admin, 'dashboard_widgets' ], PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'admin_init', [ $admin, 'switch_dashboards' ], 10 );

		\WP_Mock::expectFilterAdded( 'custom_menu_order', [ $admin, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );
		\WP_Mock::expectFilterAdded( 'menu_order', [ $admin, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );

		$admin->woo_dashboard();
		\WP_Mock::assertHooksAdded();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::reverse_dashboard_meta_order
	 */
	public function testReverseDashboardMetaOrder() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			[
				'return' => 1,
			]
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			[
				'args'   => [
					1,
					'meta-box-order_dashboard_old',
					true,
				],
				'return' => 'Not empty',
			]
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			[
				'called' => 1,
				'return' => true,
			]
		);

		$admin->reverse_dashboard_meta_order();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::dashboard_meta_order
	 */
	public function testDashboardMetaOrderEmptyMeta() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			[
				'return' => 1,
			]
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			[
				'args'   => [
					1,
					'meta-box-order_dashboard',
					true,
				],
				'return' => '',
			]
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			[
				'called' => 1,
				'return' => true,
			]
		);

		$admin->dashboard_meta_order();
	}

	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenuNoMenuOrder() {
		$admin         = new Admin();
		$admin->status = 'woodash';

		$this->assertTrue( $admin->rearrange_admin_menu( false ) );
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenu() {
		$admin         = new Admin();
		$admin->status = 'woodash';

		$this->assertEquals(
			[
				'index.php',
				'separator35',
				'edit.php?post_type=shop_order',
				'admin.php?page=wc-reports&tab=stock',
				'admin.php?page=wc-reports&tab=customers&report=customer_list',
				'edit.php?post_type=product',
				'separator36',
				'admin.php?page=wc-reports&tab=taxes',
				'admin.php?page=wc-reports',
				'wc-admin&path=/analytics/revenue',
				'separator37',
				'edit.php',
				'edit.php?post_type=page',
				'upload.php',
				'separator38',
				'users.php',
				'separator39',
				'index.php?woo_dashboard=regular',
			],
			$admin->rearrange_admin_menu( true )
		);
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenuWrongStatus() {
		$admin         = new Admin();
		$admin->status = 'regular';

		$this->assertNull( $admin->rearrange_admin_menu( true ) );
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::admin_menu_separator
	 */
	public function testAdminMenuSeparator() {
		$admin = new Admin();
		$menu  = [];

		$this->assertEquals(
			[
				3 => [
					0 => '',
					1 => 'read',
					2 => 'separator3',
					3 => '',
					4 => 'wp-menu-separator',
				],
			],
			$admin->admin_menu_separator( 3 )
		);
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::dashboard_widgets
	 */
	public function testDashboardWidgets() {
		global $pagenow, $wp_meta_boxes;

		$admin         = new Admin();
		$admin->status = 'woodash';
		$pagenow       = 'index.php';

		$wp_meta_boxes = [
			'dashboard' => [
				'normal' => [
					'core' => [
						'dashboard_right_now' => [],
						'dashboard_activity'  => [],
					],
				],
				'side'   => [
					'core' => [
						'dashboard_primary' => [],
					],
				],
			],
		];

		\WP_Mock::userFunction(
			'add_meta_box',
			[
				'called' => 2,
				'return' => true,
			]
		);

		$this->assertEquals(
			[
				'dashboard' =>
				[
					'normal' =>
					[
						'core' => [],
					],
					'side'   =>
					[
						'core' => [],
					],
				],
			],
			$admin->dashboard_widgets()
		);
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::dashboard_widgets
	 */
	public function testDashboardWidgetsEmpty() {
		$admin         = new Admin();
		$admin->status = 'regular';

		$this->assertNull( $admin->dashboard_widgets() );
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::switch_dashboards
	 * @covers \Niteo\WooCart\WooDash\Admin::dashboard_meta_order
	 */
	public function testSwitchDashboardsSwitchWooDash() {
		$mock = Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'dashboard_meta_order' )->andReturns( true );
		$mock->status = 'regular';

		$_GET['woo_dashboard'] = 'woodash';

		\WP_Mock::userFunction(
			'esc_html',
			[
				'called' => 1,
				'return' => 'woodash',
			]
		);

		\WP_Mock::userFunction(
			'update_option',
			[
				'called' => 1,
				'args'   => [
					'woodash_options',
					'woodash',
				],
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'wp_redirect',
			[
				'called' => 1,
				'return' => true,
			]
		);

		$mock->switch_dashboards();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::switch_dashboards
	 * @covers \Niteo\WooCart\WooDash\Admin::reverse_dashboard_meta_order
	 */
	public function testSwitchDashboardsSwitchRegular() {
		$mock = Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'reverse_dashboard_meta_order' )->andReturns( true );
		$mock->status = 'woodash';

		$_GET['woo_dashboard'] = 'regular';

		\WP_Mock::userFunction(
			'update_option',
			[
				'called' => 1,
				'args'   => [
					'woodash_options',
					'regular',
				],
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'wp_redirect',
			[
				'called' => 1,
				'return' => true,
			]
		);

		$mock->switch_dashboards();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::change_admin_menu
	 */
	public function testChangeAdminMenuStatusWooDash() {
		global $menu;

		$mock = Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'admin_menu_separator' )->andReturns( true );
		$mock->status = 'woodash';

		$GLOBALS['menu'] = [
			'page1' => [
				1,
				'page1',
				'page1.php',
			],
			'page2' => [
				2,
				'page2',
				'page2.php',
			],
		];

		\WP_Mock::userFunction(
			'remove_menu_page',
			[
				'called' => 2,
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'add_menu_page',
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
			'absint',
			[
				'return' => true,
			]
		);

		$mock->change_admin_menu();
	}

	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::change_admin_menu
	 */
	public function testChangeAdminMenuStatusWooDashTaxesOn() {
		global $menu;

		$mock = Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'admin_menu_separator' )->andReturns( true );
		$mock->status = 'woodash';

		$GLOBALS['menu'] = [
			'page1' => [
				1,
				'page1',
				'page1.php',
			],
			'page2' => [
				2,
				'page2',
				'page2.php',
			],
		];

		\WP_Mock::userFunction(
			'remove_menu_page',
			[
				'called' => 2,
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_option',
			[
				'called' => 1,
				'args'   => [
					'woocommerce_calc_taxes',
					'no',
				],
				'return' => 'yes',
			]
		);

		\WP_Mock::userFunction(
			'add_menu_page',
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
			'absint',
			[
				'return' => true,
			]
		);

		$mock->change_admin_menu();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Admin::__construct
	 * @covers \Niteo\WooCart\WooDash\Admin::change_admin_menu
	 */
	public function testChangeAdminMenuStatusRegular() {
		global $menu;

		$mock = Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'admin_menu_separator' )->andReturns( true );
		$mock->status = 'regular';

		$GLOBALS['menu'] = [
			'page1' => [
				1,
				'page1',
				'page1.php',
			],
			'page2' => [
				2,
				'page2',
				'page2.php',
			],
		];

		\WP_Mock::userFunction(
			'add_menu_page',
			[
				'return' => true,
			]
		);

		$mock->change_admin_menu();
	}

}
