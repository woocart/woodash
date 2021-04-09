<?php
/**
 * Unit tests for `Admin` class.
 */

namespace Niteo\WooCart\WooDash\Tests;

use Niteo\WooCart\WooDash\Admin;
use PHPUnit\Framework\TestCase;

/**
 * Tests Admin class functions in isolation.
 *
 * @package Niteo\WooCart\WooDash
 * @coversDefaultClass \Niteo\WooCart\WooDash\Admin
 */
class AdminTest extends TestCase {

	function setUp() : void {
		\WP_Mock::setUsePatchwork( true );
		\WP_Mock::setUp();
	}

	function tearDown() : void {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);

		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor() {
		$admin = new Admin();

		\WP_Mock::expectActionAdded( 'wp_loaded', array( $admin, 'check_permissions' ), 10 );

		$admin->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_permissions
	 */
	public function testCheckPermissionsNotAdmin() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => false,
			)
		);

		$this->assertEmpty( $admin->check_permissions() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_permissions
	 */
	public function testCheckPermissionsNoPermissions() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'return' => false,
			)
		);

		$this->assertEmpty( $admin->check_permissions() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_permissions
	 * @covers ::woo_dashboard
	 */
	public function testCheckPermissions() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_admin_url',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'esc_url',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => 'regular',
			)
		);

		$admin->check_permissions();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::woo_dashboard
	 */
	public function testWooDashboard() {
		$admin = new Admin();

		\WP_Mock::expectActionAdded( 'admin_menu', array( $admin, 'change_admin_menu' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_dashboard_setup', array( $admin, 'dashboard_widgets' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'admin_init', array( $admin, 'switch_dashboards' ), 10 );

		\WP_Mock::expectFilterAdded( 'custom_menu_order', array( $admin, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
		\WP_Mock::expectFilterAdded( 'menu_order', array( $admin, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );

		$admin->woo_dashboard();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::reverse_dashboard_meta_order
	 */
	public function testReverseDashboardMetaOrder() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'return' => 1,
			)
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			array(
				'args'   => array(
					1,
					'meta-box-order_dashboard_old',
					true,
				),
				'return' => 'Not empty',
			)
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			array(
				'called' => 1,
				'return' => true,
			)
		);

		$admin->reverse_dashboard_meta_order();
	}

	/**
	 * @covers ::__construct
	 * @covers ::reverse_dashboard_meta_order
	 */
	public function testReverseDashboardMetaOrderEmptyMeta() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'return' => 1,
			)
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			array(
				'times'  => 1,
				'return' => '',
			)
		);

		\WP_Mock::userFunction(
			'delete_user_meta',
			array(
				'called' => 1,
				'return' => true,
			)
		);

		$admin->reverse_dashboard_meta_order();
	}

	/**
	 * @covers ::__construct
	 * @covers ::dashboard_meta_order
	 */
	public function testDashboardMetaOrderEmptyMeta() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'return' => 1,
			)
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			array(
				'args'   => array(
					1,
					'meta-box-order_dashboard',
					true,
				),
				'return' => '',
			)
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			array(
				'called' => 1,
				'return' => true,
			)
		);

		$admin->dashboard_meta_order();
	}

	/**
	 * @covers ::__construct
	 * @covers ::dashboard_meta_order
	 */
	public function testDashboardMetaOrderNotEmptyMeta() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'return' => 1,
			)
		);

		\WP_Mock::userFunction(
			'get_user_meta',
			array(
				'times'  => 1,
				'return' => 'NOT EMPTY',
			)
		);

		\WP_Mock::userFunction(
			'maybe_serialize',
			array(
				'called' => 1,
				'return' => 'DIFFERENT VALUE',
			)
		);

		\WP_Mock::userFunction(
			'update_user_meta',
			array(
				'called' => 2,
				'return' => true,
			)
		);

		$admin->dashboard_meta_order();
	}

	/**
	 * @covers ::__construct
	 * @covers ::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenuNoMenuOrder() {
		$admin         = new Admin();
		$admin->status = 'woodash';

		$this->assertEmpty( $admin->rearrange_admin_menu( false ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenu() {
		$admin         = new Admin();
		$admin->status = 'woodash';

		$this->assertEquals(
			array(
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
			),
			$admin->rearrange_admin_menu( true )
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::rearrange_admin_menu
	 */
	public function testRearrangeAdminMenuWrongStatus() {
		$admin         = new Admin();
		$admin->status = 'regular';

		$this->assertNull( $admin->rearrange_admin_menu( true ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::remove_meta_backup
	 */
	public function testRemoveMetaBackup() {
		$admin = new Admin();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => 1,
			)
		);

		\WP_Mock::userFunction(
			'delete_user_meta',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $admin->remove_meta_backup() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::admin_menu_separator
	 */
	public function testAdminMenuSeparator() {
		$admin = new Admin();
		$menu  = array();

		$this->assertEquals(
			array(
				3 => array(
					0 => '',
					1 => 'read',
					2 => 'separator3',
					3 => '',
					4 => 'wp-menu-separator',
				),
			),
			$admin->admin_menu_separator( 3 )
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::dashboard_widgets
	 */
	public function testDashboardWidgets() {
		global $pagenow, $wp_meta_boxes;

		$admin         = new Admin();
		$admin->status = 'woodash';
		$pagenow       = 'index.php';

		$wp_meta_boxes = array(
			'dashboard' => array(
				'normal' => array(
					'core' => array(
						'dashboard_right_now' => array(),
						'dashboard_activity'  => array(),
					),
				),
				'side'   => array(
					'core' => array(
						'dashboard_primary' => array(),
					),
				),
			),
		);

		\WP_Mock::userFunction(
			'add_meta_box',
			array(
				'called' => 2,
				'return' => true,
			)
		);

		$this->assertEquals(
			array(
				'dashboard' =>
				array(
					'normal' =>
					array(
						'core' => array(),
					),
					'side'   =>
					array(
						'core' => array(),
					),
				),
			),
			$admin->dashboard_widgets()
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::dashboard_widgets
	 */
	public function testDashboardWidgetsEmpty() {
		$admin         = new Admin();
		$admin->status = 'regular';

		$this->assertNull( $admin->dashboard_widgets() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::switch_dashboards
	 */
	public function testSwitchDashboardsSwitchWooDashNoGetValue() {
		$admin = new Admin();

		$this->assertEmpty( $admin->switch_dashboards() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::switch_dashboards
	 */
	public function testSwitchDashboardsSwitchWooDashEmptyGetValue() {
		$admin = new Admin();

		$_GET['woo_dashboard'] = '';
		$this->assertEmpty( $admin->switch_dashboards() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::switch_dashboards
	 */
	public function testSwitchDashboardsSwitchWooDashSameStatus() {
		$admin = new Admin();

		$_GET['woo_dashboard'] = 'woodash';
		$admin->status         = 'woodash';

		$this->assertEmpty( $admin->switch_dashboards() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::switch_dashboards
	 * @covers ::dashboard_meta_order
	 */
	public function testSwitchDashboardsSwitchWooDash() {
		$mock = \Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'dashboard_meta_order' )->andReturns( true );
		$mock->status = 'regular';

		$_GET['woo_dashboard'] = 'woodash';

		\WP_Mock::userFunction(
			'esc_html',
			array(
				'called' => 1,
				'return' => 'woodash',
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'called' => 1,
				'args'   => array(
					'woodash_options',
					'woodash',
				),
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'called' => 1,
				'return' => true,
			)
		);

		$mock->switch_dashboards();
	}

	/**
	 * @covers ::__construct
	 * @covers ::switch_dashboards
	 * @covers ::reverse_dashboard_meta_order
	 */
	public function testSwitchDashboardsSwitchRegular() {
		$mock = \Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'reverse_dashboard_meta_order' )->andReturns( true );
		$mock->status = 'woodash';

		$_GET['woo_dashboard'] = 'regular';

		\WP_Mock::userFunction(
			'update_option',
			array(
				'called' => 1,
				'args'   => array(
					'woodash_options',
					'regular',
				),
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'called' => 1,
				'return' => true,
			)
		);

		$mock->switch_dashboards();
	}

	/**
	 * @covers ::__construct
	 * @covers ::change_admin_menu
	 */
	public function testChangeAdminMenuStatusWooDash() {
		global $menu;

		$mock = \Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'admin_menu_separator' )->andReturns( array() );
		$mock->status = 'woodash';

		$GLOBALS['menu'] = array(
			'page1' => array(
				1,
				'page1',
				'page1.php',
			),
			'page2' => array(
				2,
				'page2',
				'page2.php',
			),
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'called' => 1,
				'args'   => array(
					'woocommerce_calc_taxes',
					'no',
				),
				'return' => 'yes',
			)
		);

		\WP_Mock::userFunction(
			'remove_menu_page',
			array(
				'called' => 2,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'add_menu_page',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'absint',
			array(
				'return' => true,
			)
		);

		$mock->change_admin_menu();
	}

	/**
	 * @covers ::__construct
	 * @covers ::change_admin_menu
	 */
	public function testChangeAdminMenuStatusRegular() {
		global $menu;

		$mock = \Mockery::mock( 'Niteo\WooCart\WooDash\Admin' )->makePartial();
		$mock->shouldReceive( 'admin_menu_separator' )->andReturns( array() );
		$mock->status = 'regular';

		$GLOBALS['menu'] = array(
			'page1' => array(
				1,
				'page1',
				'page1.php',
			),
			'page2' => array(
				2,
				'page2',
				'page2.php',
			),
		);

		\WP_Mock::userFunction(
			'add_menu_page',
			array(
				'return' => true,
			)
		);

		$mock->change_admin_menu();
	}

}
