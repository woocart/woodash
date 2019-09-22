<?php

namespace Niteo\WooCart\WooDash {

  use Niteo\WooCart\WooDash\Config;

	/**
	 * Admin functionality of the plugin.
	 *
	 * @package    Niteo\WooCart\WooDash
	 * @since      1.0.0
	 */
	class Admin {

		protected $admin_url;
		protected $status;


		/**
		 * Class Constructor.
		 */
		public function __construct() {
			if ( is_admin() ) {
				// set admin url
				$this->admin_url 	= esc_url( get_admin_url() );

				// set status
				$this->status 		= sanitize_text_field( get_option( Config::DB_OPTION, Config::DEFAULT_STATUS ) );

				// check permissions
				add_action( 'plugins_loaded', [ &$this, 'check_permissions' ], 10 );
			}
		}


    /**
		 * Check for user permissions and then proceed accordingly.
		 */
		public function check_permissions() {
			if ( current_user_can( 'administrator' ) ) {
				// initiate dashboard
				$this->woo_dashboard();
			}
		}


		/**
		 * Function which activates the dashboard.
		 */
		public function woo_dashboard() {
			// actions
			// hide menu items
			add_action( 'admin_menu', [ &$this, 'change_admin_menu' ], PHP_INT_MAX );

			// dashboard widgets setup
			add_action( 'wp_dashboard_setup', [ &$this, 'dashboard_widgets' ], PHP_INT_MAX );

			// switch dashboards
			add_action( 'init', [ &$this, 'switch_dashboards' ], 10 );

			// filters
			// re-arrange admin menu
			add_filter( 'custom_menu_order', [ &$this, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );
			add_filter( 'menu_order', [ &$this, 'rearrange_admin_menu' ], PHP_INT_MAX, 1 );
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
							update_option( Config::OPTIONNAME, 'woocart' );

							// update usermeta
							$this->dashboard_meta_order();
						} else {
							update_option( Config::OPTIONNAME, 'normal' );

							// reverse usermeta
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
				<p><?php esc_html_e( 'WooDash plugin has been activated and provides an easy switcher for two different Dashboards.', 'woodash' ); ?></p>
			</div>
			<?php
		}


		/**
		 * Removes the menu items for the Store Dashboard.
		 */
		public function change_admin_menu() {
			global $menu;

			if ( 'woocart' === $this->status ) {
				// hide menu items
				foreach ( $GLOBALS['menu'] as $key => $value ) {
					if ( ! in_array( $value[2], $this->menus_to_stay ) ) {
						remove_menu_page( $value[2] );
					}
				}

				// Add menu items.
				foreach ( $this->add_menu_items as $menu_key => $menu_value ) {
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
			 */
			for ( $i = 35; $i < 40; $i++ ) {
				$this->admin_menu_separator( $i );
			}

			/**
			 * Add switcher.
			 * With priority `9999`.
			 */
			add_menu_page(
				esc_html__( 'Switch Dashboard', 'woodash' ),
				esc_html__( 'Switch Dashboard', 'woodash' ),
				'manage_options',
				Config::PREFIX . 'switch',
				'',
				'dashicons-image-rotate',
				9999
			);

			if ( 'woocart' === $this->status ) {
				$menu[9999][2] = $this->admin_url . 'index.php?dashboard=regular';
			} else {
				$menu[9999][2] = $this->admin_url . 'index.php?dashboard=woodash';
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

				return [
					'index.php',
					'separator35',
					$this->admin_url . 'edit.php?post_type=shop_order',
					$this->admin_url . 'admin.php?page=wc-reports&tab=stock',
					$this->admin_url . 'admin.php?page=wc-reports&tab=customers&report=customer_list',
					'edit.php?post_type=product',
					'separator36',
					$this->admin_url . 'admin.php?page=wc-reports&tab=taxes',
					$this->admin_url . 'admin.php?page=wc-reports',
					'separator37',
					'edit.php',
					'edit.php?post_type=page',
					'upload.php',
					'separator38',
					'users.php',
					'separator39',
					$this->admin_url . 'index.php?woo_dashboard=' . $switch
				];
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
					add_meta_box( 'dashboard_right_now', esc_html__( 'At a Glance', 'woodash' ), 'wp_dashboard_right_now', 'dashboard', 'side', 'high' );
					add_meta_box( 'dashboard_activity', esc_html__( 'Activity', 'woodash' ), 'wp_dashboard_site_activity', 'dashboard', 'side' );
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
			$new_meta_value = [
				'normal'  => 'woocommerce_dashboard_status,woocommerce_dashboard_recent_reviews',
				'side'    => 'dashboard_right_now,dashboard_activity,dashboard_quick_press',
				'column3' => '',
				'column4' => ''
			];

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

			$menu[ $position ] = [
				0	=>	'',
				1	=>	'read',
				2	=>	'separator' . $position,
				3	=>	'',
				4	=>	'wp-menu-separator'
			];
		}


		/**
		 * Function to debug the admin menu.
		 */
		public function debug_admin_menus() {
			global $submenu, $menu, $pagenow;

			if ( 'index.php' === $pagenow ) {
				sprintf( '<pre>%s</pre>', $menu );
				sprintf( '<pre>%s</pre>', $submenu );
			}
		}

  }

}
