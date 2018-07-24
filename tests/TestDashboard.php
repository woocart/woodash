<?php

use Niteoweb\WooSimpleDashboard\WooSimpleDashboard;

class TestDashboard extends \PHPUnit\Framework\TestCase {

	function setUp() {
		\WP_Mock::setUsePatchwork( true );
		\WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
	}

	/**
	 * Two functions are tested in the __construct() function.
	 * 1. set_admin_url()
	 * 2. set_status()
	 */
	public function test_init_admin() {
		\WP_Mock::wpFunction(
			'is_admin', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_admin_url', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_option', array(
				'called' => 1,
				'return' => 'woocart'
			)
		);

		$plugin = new WooSimpleDashboard();

		\WP_Mock::expectActionAdded( 'plugins_loaded', array( $plugin, 'check_permissions' ), 10 );

		$plugin->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * Testing the permissions check.
	 */
	public function test_check_permissions() {
		\WP_Mock::wpFunction(
			'current_user_can', array(
				'return' => true
			)
		);

		$plugin = new WooSimpleDashboard();

		\WP_Mock::expectActionAdded( 'admin_menu', array( $plugin, 'change_admin_menu' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_dashboard_setup', array( $plugin, 'dashboard_widgets' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'init', array( $plugin, 'switch_dashboards' ), 10 );

		\WP_Mock::expectFilterAdded( 'custom_menu_order', array( $plugin, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
		\WP_Mock::expectFilterAdded( 'menu_order', array( $plugin, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );

		$plugin->check_permissions();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * This also tests `dashboard_meta_order()` function.
	 * Testing with mismatch values.
	 */
	public function test_activate_plugin() {
		\WP_Mock::wpFunction(
			'update_option', array(
				'called' => 1,
				'args' 	 => array( 'Niteoweb.WooDashboard.View', '*' ),
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_current_user_id', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_user_meta', array(
				'called' => 1,
				'args' 	 => array( 1, 'meta-box-order_dashboard', true ),
				'return' => 'Correct Value'
			)
		);

		\WP_Mock::wpFunction(
			'maybe_serialize', array(
				'called' => 1,
				'return' => 'Incorrect Value'
			)
		);

		\WP_Mock::wpFunction(
			'update_user_meta', array(
				'called' => 1,
				'args'   => array( 1, 'meta-box-order_dashboard_old', '*' )
			)
		);

		\WP_Mock::wpFunction(
			'update_user_meta', array(
				'called' => 1,
				'args' 	 => array( 1, 'meta-box-order_dashboard', '*' )
			)
		);

		$plugin = new WooSimpleDashboard();
		$plugin->activate_plugin();
	}

	/**
	 * Two functions are tested in the deactivate_plugin() function.
	 * 1. reverse_dashboard_meta_order()
	 * 2. remove_meta_backup()
	 */
	public function test_deactivate_plugin() {
		\WP_Mock::wpFunction(
			'delete_option', array(
				'called' => 1,
				'args' 	 => array( 'Niteoweb.WooDashboard.View' )
			)
		);

		\WP_Mock::wpFunction(
			'get_current_user_id', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_user_meta', array(
				'return' => true,
				'args' 	 => array( 1, 'meta-box-order_dashboard_old', true ),
				'return' => ''
			)
		);

		\WP_Mock::wpFunction(
			'delete_user_meta', array(
				'return' => true,
				'args' 	 => array( 1, 'meta-box-order_dashboard' )
			)
		);

		\WP_Mock::wpFunction(
			'delete_user_meta', array(
				'return' => true,
				'args' 	 => array( 1, 'meta-box-order_dashboard_old' )
			)
		);

		$plugin = new WooSimpleDashboard();
		$plugin->deactivate_plugin();
	}

	/**
	 * Testing dashboard meta order with empty values.
	 */
	public function test_dashboard_meta_order_empty_values() {
		\WP_Mock::wpFunction(
			'get_current_user_id', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'get_user_meta', array(
				'called' => 1,
				'args' 	 => array( 1, 'meta-box-order_dashboard', true ),
				'return' => false
			)
		);

		\WP_Mock::wpFunction(
			'maybe_serialize', array(
				'called' => 1
			)
		);

		\WP_Mock::wpFunction(
			'update_user_meta', array(
				'called' => 1,
				'args' 	 => array( 1, 'meta-box-order_dashboard', '*' )
			)
		);

		$plugin = new WooSimpleDashboard();
		$plugin->dashboard_meta_order();
	}

	/**
	 * Testing removal of meta backup.
	 */
	public function test_remove_meta_backup() {
		\WP_Mock::wpFunction(
			'get_current_user_id', array(
				'return' => true
			)
		);

		\WP_Mock::wpFunction(
			'delete_user_meta', array(
				'return' => true,
				'args' 	 => array( 1, 'meta-box-order_dashboard_old' )
			)
		);

		$plugin = new WooSimpleDashboard();
		$plugin->remove_meta_backup();
	}

}