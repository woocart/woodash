<?php

/**
 * Plugin Name: WooDash
 * Description: WooDash creates a store-focused sidebar menu and home dashboard to make it easier to access the common WooCommerce features.
 * Version:     @##VERSION##@
 * Runtime:     5.6+
 * Author:      WooCart
 * Text Domain: woodash
 * Domain Path: i18n
 * Author URI:  www.woocart.com
 */

namespace Niteo\WooCart\WooDash {

	// exit if called directly
	defined( 'ABSPATH' ) || exit;

	// composer autoloader
	require_once __DIR__ . '/vendor/autoload.php';

	use Niteo\WooCart\WooDash\Config;
	use Niteo\WooCart\WooDash\Admin;
	use Niteo\WooCart\WooDash\Dashboard;

	/**
	 * WooDash class where all the action happens.
	 *
	 * @category   Plugins
	 * @package    Niteo\WooCart\WooDash
	 * @since      1.0.0
	 */
	class WooDash {

		protected $admin;
		protected $dashboard;


		/**
		 * Class Constructor.
		 */
		public function __construct() {

			// initialize classes
			$this->admin = new Admin();

		}


		/**
		 * Attached to the activation hook.
		 */
		public function activate_plugin() {
			// add to `wp_options` table
			update_option( Config::DB_OPTION, Config::DEFAULT_STATUS );

			// update usermeta table for dashboard widgets
			$this->admin->dashboard_meta_order();
		}


		/**
		 * Attached to the de-activation hook.
		 */
		public function deactivate_plugin() {
			// remove from `wp_options` table
			delete_option( Config::DB_OPTION );

			// reverse usermeta table for dashboard widgets
			$this->admin->reverse_dashboard_meta_order();

			// also, remove the usermeta backup
			$this->admin->remove_meta_backup();
		}

	}


	// initialize plugin
	$woodash = new WooDash();


	/**
	 * Hooks for plugin activation & deactivation
	 */
	register_activation_hook( __FILE__, [ &$woodash, 'activate_plugin' ] );
	register_deactivation_hook( __FILE__, [ &$woodash, 'deactivate_plugin' ] );

}
