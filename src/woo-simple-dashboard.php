<?php

namespace Niteoweb\WooSimpleDashboard;

/**
 * Plugin Name: Woo Simple Dashboard
 * Description: Simple Store Dashboard switcher for WordPress providing quick access to WooCommerce Store Dashboard.
 * Version:     1.0.0
 * Runtime:     5.3+
 * Author:      WooCart
 * Text Domain: woo-simple-dashboard
 * Domain Path: /langs/
 * Author URI:  www.woocart.com
 */

/**
 * Checks for PHP version and stop the plugin if the version is < 5.3.0.
 */
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	?>
	<div id="error-page">
		<p>
		<?php
		esc_html_e(
			'This plugin requires PHP 5.3.0 or higher. Please contact your hosting provider about upgrading your
			server software. Your PHP version is', 'woo-simple-dashboard'
		);
		?>
		<b><?php echo esc_html( PHP_VERSION ); ?></b></p>
	</div>
	<?php
	die();
}

/**
 * WooSimpleDashboard class where all the action happens.
 *
 * @package WordPress
 * @subpackage woo-simple-dashboard
 * @since 1.0.0
 */
class WooSimpleDashboard {

	const OPTIONNAME    = 'Niteoweb.WooDashboard.View';
	const SLUGPREFIX    = 'woodashboard_';
	const DEFAULTSTATUS = 'woocart';

	private $admin_url       = '';
	private $status          = '';
	private $hide_menu_items = array(
		'index.php'   => array(
			'sub' => 'update-core.php',
		),
		'comments'    => array(
			'main' => 'edit-comments.php',
		),
		'themes'      => array(
			'main' => 'themes.php',
		),
		'plugins'     => array(
			'main' => 'plugins.php',
		),
		'tools'       => array(
			'main' => 'tools.php',
		),
		'options'     => array(
			'main' => 'options-general.php',
		),
		'customers'   => array(
			'main' => 'edit.php?post_type=customer',
		),
		'woocommerce' => array(
			'main' => 'woocommerce',
		),
		'wpcf'        => array(
			'main' => 'wpcf7',
		),
	);

	private $add_menu_items = array(
		'orders'    => array(
			'name'     => 'Orders',
			'link'     => 'edit.php?post_type=shop_order',
			'priority' => 101,
			'icon'     => 'dashicons-heart',
		),
		'stock'     => array(
			'name'     => 'Stock',
			'link'     => 'admin.php?page=wc-reports&tab=stock',
			'priority' => 102,
			'icon'     => 'dashicons-archive',
		),
		'customers' => array(
			'name'     => 'Customers',
			'link'     => 'admin.php?page=wc-reports&tab=customers&report=customer_list',
			'priority' => 103,
			'icon'     => 'dashicons-groups',
		),
		'taxes'     => array(
			'name'     => 'Taxes',
			'link'     => 'admin.php?page=wc-reports&tab=taxes',
			'priority' => 104,
			'icon'     => 'dashicons-feedback',
		),
		'reports'   => array(
			'name'     => 'All Reports',
			'link'     => 'admin.php?page=wc-reports',
			'priority' => 105,
			'icon'     => 'dashicons-chart-area',
		),
	);

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			// Set admin URL.
			$this->admin_url = get_admin_url();

			// Set status.
			$this->status = get_option( self::OPTIONNAME, self::DEFAULTSTATUS );

			// Check permissions.
			add_action( 'plugins_loaded', array( &$this, 'check_permissions' ), 10 );
		}
	}

	/**
	 * Attached to the activation hook.
	 */
	public function activate_plugin() {
		// Add to `wp_options` table.
		update_option( self::OPTIONNAME, 'woocart' );

		// Update usermeta table for dashboard widgets.
		$this->dashboard_meta_order();
	}

	/**
	 * Attached to the de-activation hook.
	 */
	public function deactivate_plugin() {
		// Remove from `wp_options` table.
		delete_option( self::OPTIONNAME );

		// Reverse usermeta table for dashboard widgets.
		$this->reverse_dashboard_meta_order();

		// Also, remove the usermeta backup.
		$this->remove_meta_backup();
	}

	/**
	 * Check for user permissions and then proceed accordingly.
	 */
	public function check_permissions() {
		if ( current_user_can( 'administrator' ) ) {
			// Initiate Dashboard.
			$this->woo_dashboard();
		}
	}

	/**
	 * Function which activates the dashboard.
	 */
	public function woo_dashboard() {
		// Actions.
		// Hide menu items.
		add_action( 'admin_menu', array( &$this, 'change_admin_menu' ), PHP_INT_MAX );

		// Dashboard widgets setup.
		add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widgets' ), PHP_INT_MAX );

		// Switch dashboards.
		add_action( 'init', array( &$this, 'switch_dashboards' ), 10 );

		// Filters.
		// Re-arrange admin menu.
		add_filter( 'custom_menu_order', array( &$this, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
		add_filter( 'menu_order', array( &$this, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
	}

	/**
	 * This let's us switch the dashboards.
	 * Nothing fancy. Just database and usermeta update.
	 */
	public function switch_dashboards() {
		if ( isset( $_GET['woo_dashboard'] ) ) {
			$switch = esc_html( $_GET['woo_dashboard'] );

			if ( ! empty( $switch ) ) {
				if ( $switch !== $this->status ) {
					if ( 'woocart' === $switch ) {
						update_option( self::OPTIONNAME, 'woocart' );

						// Update usermeta.
						$this->dashboard_meta_order();
					} else {
						update_option( self::OPTIONNAME, 'normal' );

						// Reverse usermeta.
						$this->reverse_dashboard_meta_order();
					}

					header( 'Location:' . $this->admin_url );
				}
			}
		}
	}

	/**
	 * Admin notice which gets fired on plugin activation.
	 *
	 * @codeCoverageIgnore
	 */
	public function activate_plugin_notice() {
		?>
		<div class="notice notice-success">
			<p><?php esc_html_e( 'Woo Simple Dashboard plugin has been activated and provides an easy switcher for two different Dashboards.', 'woo-simple-dashboard' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Removes the menu items for the Store Dashboard.
	 */
	public function change_admin_menu() {
		global $menu;

		if ( 'woocart' === $this->status ) {
			// Hide menu items.
			foreach ( $this->hide_menu_items as $menu_key => $menu_value ) {
				foreach ( $menu_value as $remove_key => $remove_value ) {
					if ( 'sub' === $remove_key ) {
						remove_submenu_page( $menu_key, $remove_value );
					} else {
						remove_menu_page( $remove_value );
					}
				}
			}

			// Add menu items.
			foreach ( $this->add_menu_items as $menu_key => $menu_value ) {
				add_menu_page( sanitize_text_field( $menu_value['name'] ), sanitize_text_field( $menu_value['name'] ), 'manage_options', self::SLUGPREFIX . $menu_key, '', sanitize_text_field( $menu_value['icon'] ), absint( $menu_value['priority'] ) );

				$menu[ $menu_value['priority'] ][2] = $this->admin_url . $menu_value['link'];
			}

			/**
			 * Change label for Dashboard.
			 * Dashboard has `2` priority.
			 */
			$menu[2][0] = esc_html__( 'My Store', 'woo-simple-dashboard' );
		}

		/**
		 * We are adding two additional menu separators over here.
		 * Adding 4 separators between values 5 and 10 (6,7,8,9)
		 */
		for ( $i = 6; $i < 10; $i++ ) {
			$this->admin_menu_separator( $i );
		}

		/**
		 * Add switcher.
		 * With priority `9999`.
		 */
		add_menu_page( esc_html__( 'Switch Dashboard', 'woo-simple-dashboard' ), esc_html__( 'Switch Dashboard', 'woo-simple-dashboard' ), 'manage_options', self::SLUGPREFIX . 'switch', '', 'dashicons-image-rotate', 9999 );

		if ( 'woocart' === $this->status ) {
			$menu[9999][2] = $this->admin_url . 'index.php?woo_dashboard=normal';
		} else {
			$menu[9999][2] = $this->admin_url . 'index.php?woo_dashboard=woocart';
		}
	}

	/**
	 * Modify the menu items for the WP admin.
	 *
	 * @param array $menu_order Array with list of menu items.
	 */
	public function rearrange_admin_menu( $menu_order ) {
		if ( 'woocart' === $this->status ) {
			if ( ! $menu_order ) {
				return true;
			}

			$switch = 'woocart';

			if ( 'woocart' === $this->status ) {
				$switch = 'normal';
			}

			return array(
				'index.php',
				'separator6',
				$this->admin_url . 'edit.php?post_type=shop_order',
				$this->admin_url . 'admin.php?page=wc-reports&tab=stock',
				$this->admin_url . 'admin.php?page=wc-reports&tab=customers&report=customer_list',
				'edit.php?post_type=product',
				'separator7',
				$this->admin_url . 'admin.php?page=wc-reports&tab=taxes',
				$this->admin_url . 'admin.php?page=wc-reports',
				'separator8',
				'edit.php',
				'edit.php?post_type=page',
				'upload.php',
				'separator9',
				'users.php',
				'separator-last',
				$this->admin_url . 'index.php?woo_dashboard=' . $switch,
			);
		}
	}

	/**
	 * Re-arrange dashboard widgets.
	 */
	public function dashboard_widgets() {
		if ( 'woocart' === $this->status ) {
			global $wp_meta_boxes, $pagenow;

			if ( 'index.php' === $pagenow ) {
				unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] );
				unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] );
				unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );

				// Side Meta Boxes.
				add_meta_box( 'dashboard_right_now', esc_html__( 'At a Glance', 'woo-simple-dashboard' ), 'wp_dashboard_right_now', 'dashboard', 'side', 'high' );
				add_meta_box( 'dashboard_activity', esc_html__( 'Activity', 'woo-simple-dashboard' ), 'wp_dashboard_site_activity', 'dashboard', 'side' );
			}
		}
	}

	/**
	 * Update usermeta for dashboard widgets.
	 * Over here we are forcing the user to view dashboard widgets in a
	 * specific order if the `Simple Store Dashboard` view is ON.
	 */
	public function dashboard_meta_order() {
		$id             = get_current_user_id();
		$new_meta_value = array(
			'normal'  => 'woocommerce_dashboard_status,woocommerce_dashboard_recent_reviews',
			'side'    => 'dashboard_right_now,dashboard_activity,dashboard_quick_press',
			'column3' => '',
			'column4' => '',
		);

		// Update usermeta.
		$user_meta = get_user_meta( $id, 'meta-box-order_dashboard', true );

		if ( ! empty( $user_meta ) ) {
			if ( $user_meta !== maybe_serialize( $new_meta_value ) ) {
				// Backing up current meta value.
				update_user_meta( $id, 'meta-box-order_dashboard_old', $user_meta );
				// Adding the new meta value.
				update_user_meta( $id, 'meta-box-order_dashboard', $new_meta_value );
			} else {
				/**
				 * Usermeta matches backup value.
				 * So, no need to backup again.
				 */
				update_user_meta( $id, 'meta-box-order_dashboard', $new_meta_value );
			}
		} else {
			/**
			 * Usermeta does not exist.
			 * So, simply adding the new value.
			 */
			update_user_meta( $id, 'meta-box-order_dashboard', $new_meta_value );
		}
	}

	/**
	 * Reverse usermeta for dashboard widgets.
	 * Let's get back to the original dashboard :)
	 */
	public function reverse_dashboard_meta_order() {
		$id = get_current_user_id();

		// Check if the old usermeta exists.
		$old_user_meta = get_user_meta( $id, 'meta-box-order_dashboard_old', true );

		if ( ! empty( $old_user_meta ) ) {
			update_user_meta( $id, 'meta-box-order_dashboard', $old_user_meta );
		} else {
			delete_user_meta( $id, 'meta-box-order_dashboard' );
		}
	}

	/**
	 * Remove the backup entry from the `usermeta` table.
	 */
	public function remove_meta_backup() {
		$id = get_current_user_id();
		delete_user_meta( $id, 'meta-box-order_dashboard_old' );
	}

	/**
	 * Adds separator to the admin menu.
	 */
	public function admin_menu_separator( $position ) {
		global $menu;

		$menu[ $position ] = array(
			0	=>	'',
			1	=>	'read',
			2	=>	'separator' . $position,
			3	=>	'',
			4	=>	'wp-menu-separator'
		);
	}

	/**
	 * Function to debug the admin menu.
	 */
	public function debug_admin_menus() {
		global $submenu, $menu, $pagenow;

		if ( 'index.php' === $pagenow ) {
			echo '<pre>'; print_r( $menu ); echo '</pre>';
			echo '<pre>'; print_r( $submenu ); echo '</pre>';
		}
	}

}

// Initialize Plugin.
if ( defined( 'ABSPATH' ) ) {
	$niteo_woo_dashboard = new WooSimpleDashboard();

	// Activation Hook.
	register_activation_hook( __FILE__, array( &$niteo_woo_dashboard, 'activate_plugin' ) );

	// Deactivation Hook.
	register_deactivation_hook( __FILE__, array( &$niteo_woo_dashboard, 'deactivate_plugin' ) );
}
