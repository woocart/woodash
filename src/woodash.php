<?php

/**
 * Plugin Name: WooDash
 * Description: WooDash creates a store-focused sidebar menu and home dashboard to make it easier to access the common WooCommerce features.
 * Version:     @##VERSION##@
 * Runtime:     7.3+
 * Author:      WooCart
 * Text Domain: woodash
 * Domain Path: i18n
 * Author URI:  www.woocart.com
 */

namespace Niteo\WooCart\WooDash;

// Stop execution if the file is called directly.
defined( 'ABSPATH' ) || exit;

// Composer autoloder file.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Plugin class where all the action happens.
 *
 * @category   Plugins
 * @package    Niteo\WooCart\WooDash
 */
class WooDash {

	/**
	 * @var stdClass
	 */
	protected $admin;

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		// WooCommerce version check.
		if ( ! WooCheck::is_plugin_active( 'woocommerce.php' ) ) {
			add_action( 'admin_notices', array( 'Niteo\WooCart\WooDash\WooCheck', 'inactive_notice' ) );
			return;
		}

		$this->admin = new Admin();
	}

	/**
	 * Attached to the activation hook.
	 *
	 * @return void
	 */
	public function activate() : void {
		// Add to `wp_options` table.
		update_option( Config::DB_OPTION, Config::DEFAULT_STATUS );

		// Add plugin activation notice.
		set_transient( Config::PREFIX . 'plugin-activation-notice', true, 60 * 60 * 24 );

		// Update usermeta table for dashboard widgets.
		$this->admin->dashboard_meta_order();
	}

	/**
	 * Attached to the de-activation hook.
	 *
	 * @return void
	 */
	public function deactivate() : void {
		// Remove options.
		delete_option( Config::DB_OPTION );

		// Reverse user meta for dashboard widgets.
		$this->admin->reverse_dashboard_meta_order();

		// Remove the user meta backup.
		$this->admin->remove_meta_backup();
	}

}

// Initialize plugin.
$woodash = new WooDash();

// Hooks for plugin activation & deactivation.
register_activation_hook( __FILE__, array( $woodash, 'activate' ) );
register_deactivation_hook( __FILE__, array( $woodash, 'deactivate' ) );
