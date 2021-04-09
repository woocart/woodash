<?php
/**
 * Admin class for the plugin.
 */

namespace Niteo\WooCart\WooDash;

/**
 * Admin functionality of the plugin.
 *
 * @package Niteo\WooCart\WooDash
 */
class Admin {

	/**
	 * @var string
	 */
	public $admin_url;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'check_permissions' ), 10 );
	}

	/**
	 * Check for user permissions and then proceed accordingly.
	 *
	 * @return void
	 */
	public function check_permissions() : void {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		// Set admin URL and status.
		$this->admin_url = esc_url( get_admin_url() );
		$this->status    = sanitize_text_field( get_option( Config::DB_OPTION, Config::DEFAULT_STATUS ) );

		// Initialize dashboard.
		$this->woo_dashboard();
	}

	/**
	 * Function which activates the dashboard.
	 *
	 * @return void
	 */
	public function woo_dashboard() : void {
		// Hide menu items.
		add_action( 'admin_menu', array( $this, 'change_admin_menu' ), PHP_INT_MAX );

		// Setup dashboard widgets.
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widgets' ), PHP_INT_MAX );

		// Switch dashboard.
		add_action( 'admin_init', array( $this, 'switch_dashboards' ), 10 );

		// Re-arrange admin menu.
		add_filter( 'custom_menu_order', array( $this, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
		add_filter( 'menu_order', array( $this, 'rearrange_admin_menu' ), PHP_INT_MAX, 1 );
	}

	/**
	 * Switch dashboard and update user meta.
	 *
	 * @return void
	 */
	public function switch_dashboards() : void {
		if ( ! isset( $_GET['woo_dashboard'] ) ) {
			return;
		}

		$switch = esc_html( $_GET['woo_dashboard'] );

		if ( empty( $switch ) ) {
			return;
		}

		if ( $switch === $this->status ) {
			return;
		}

		if ( Config::DEFAULT_STATUS === $switch ) {
			update_option( Config::DB_OPTION, Config::DEFAULT_STATUS );

			// update usermeta
			$this->dashboard_meta_order();
		} else {
			update_option( Config::DB_OPTION, 'regular' );

			// reverse usermeta
			$this->reverse_dashboard_meta_order();
		}

		wp_redirect( $this->admin_url );
	}

	/**
	 * Removes the menu items for the Store Dashboard.
	 *
	 * @return void
	 */
	public function change_admin_menu() : void {
		global $menu;

		if ( Config::DEFAULT_STATUS === $this->status ) {
			// hide menu items
			foreach ( $GLOBALS['menu'] as $key => $value ) {
				if ( ! in_array( $value[2], Menu::$default_menus ) ) {
					remove_menu_page( $value[2] );
				}
			}

			// Check if taxes are enabled in WooCommerce
			if ( 'yes' === get_option( 'woocommerce_calc_taxes', 'no' ) ) {
				Menu::$add_items['taxes'] = array(
					'name'     => esc_html__( 'Taxes', 'woodash' ),
					'link'     => 'admin.php?page=wc-reports&tab=taxes',
					'priority' => 104,
					'icon'     => 'dashicons-feedback',
				);
			}

			// Add menu items.
			foreach ( Menu::$add_items as $menu_key => $menu_value ) {
				add_menu_page(
					sanitize_text_field( $menu_value['name'] ),
					sanitize_text_field( $menu_value['name'] ),
					'manage_options',
					Config::PREFIX . $menu_key,
					'',
					sanitize_text_field( $menu_value['icon'] ),
					absint( $menu_value['priority'] )
				);

				$menu[ $menu_value['priority'] ][2] = $this->admin_url . $menu_value['link'];
			}

			/**
			 * Change label for Dashboard.
			 * Dashboard has `2` priority.
			 */
			$menu[2][0] = esc_html__( 'My Store', 'woodash' );
		}

		/**
		 * We are adding two additional menu separators over here.
		 * Adding 4 separators between values 5 and 10 (6,7,8,9)
		 * This is required for the separators to show in the admin menu
		 */
		for ( $i = 35; $i < 40; $i++ ) {
			$this->admin_menu_separator( $i );
		}

		// Add switcher with priority `9999`.
		add_menu_page(
			esc_html__( 'Switch Dashboard', 'woodash' ),
			esc_html__( 'Switch Dashboard', 'woodash' ),
			'manage_options',
			Config::PREFIX . 'switch',
			'',
			'dashicons-image-rotate',
			9999
		);

		if ( Config::DEFAULT_STATUS === $this->status ) {
			$menu[9999][2] = $this->admin_url . 'index.php?woo_dashboard=regular';
		} else {
			$menu[9999][2] = $this->admin_url . 'index.php?woo_dashboard=' . Config::DEFAULT_STATUS;
		}
	}

	/**
	 * Modify the menu items for the WP admin.
	 *
	 * @param array $menu_order Array with list of menu items.
	 */
	public function rearrange_admin_menu( $menu_order ) {
		if ( ! $menu_order ) {
			return;
		}

		if ( Config::DEFAULT_STATUS !== $this->status ) {
			return;
		}

		// Switch to regular mode.
		$switch = 'regular';

		return array(
			'index.php',
			'separator35',
			$this->admin_url . 'edit.php?post_type=shop_order',
			$this->admin_url . 'admin.php?page=wc-reports&tab=stock',
			$this->admin_url . 'admin.php?page=wc-reports&tab=customers&report=customer_list',
			'edit.php?post_type=product',
			'separator36',
			$this->admin_url . 'admin.php?page=wc-reports&tab=taxes',
			$this->admin_url . 'admin.php?page=wc-reports',
			'wc-admin&path=/analytics/revenue',
			'separator37',
			'edit.php',
			'edit.php?post_type=page',
			'upload.php',
			'separator38',
			'users.php',
			'separator39',
			$this->admin_url . 'index.php?woo_dashboard=' . $switch,
		);
	}

	/**
	 * Re-arrange dashboard widgets.
	 */
	public function dashboard_widgets() {
		global $wp_meta_boxes, $pagenow;

		if ( Config::DEFAULT_STATUS !== $this->status ) {
			return;
		}

		if ( 'index.php' === $pagenow ) {
			unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] );
			unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] );
			unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );

			// Side meta boxes.
			add_meta_box( 'dashboard_right_now', esc_html__( 'At a Glance', 'woodash' ), 'wp_dashboard_right_now', 'dashboard', 'side', 'high' );
			add_meta_box( 'dashboard_activity', esc_html__( 'Activity', 'woodash' ), 'wp_dashboard_site_activity', 'dashboard', 'side' );
		}

		return $wp_meta_boxes;
	}

	/**
	 * Update usermeta for dashboard widgets.
	 *
	 * Over here we are forcing the user to view dashboard widgets in a
	 * specific order if the `Simple Store Dashboard` view is ON.
	 *
	 * @return void
	 */
	public function dashboard_meta_order() : void {
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
				// User meta matches backup value.
				update_user_meta( $id, 'meta-box-order_dashboard', $new_meta_value );
			}
		} else {
			// Add a new value as user meta does not exist.
			update_user_meta( $id, 'meta-box-order_dashboard', $new_meta_value );
		}
	}

	/**
	 * Reverse user meta for dashboard widgets.
	 *
	 * @return void
	 */
	public function reverse_dashboard_meta_order() : void {
		$id = get_current_user_id();

		// Check if the old user meta exists.
		$old_user_meta = get_user_meta( $id, 'meta-box-order_dashboard_old', true );

		if ( ! empty( $old_user_meta ) ) {
			update_user_meta( $id, 'meta-box-order_dashboard', $old_user_meta );
		} else {
			delete_user_meta( $id, 'meta-box-order_dashboard' );
		}
	}

	/**
	 * Remove the backup entry from user meta.
	 *
	 * @return void
	 */
	public function remove_meta_backup() : void {
		$id = get_current_user_id();
		delete_user_meta( $id, 'meta-box-order_dashboard_old' );
	}

	/**
	 * Adds separator to the admin menu.
	 *
	 * @return array
	 */
	public function admin_menu_separator( $position ) : array {
		global $menu;

		$menu[ $position ] = array(
			0 => '',
			1 => 'read',
			2 => 'separator' . $position,
			3 => '',
			4 => 'wp-menu-separator',
		);

		return $menu;
	}

}
