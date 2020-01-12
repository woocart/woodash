<?php

namespace Niteo\WooCart\WooDash {

	/**
	 * Class for menu items.
	 *
	 * @package Niteo\WooCart\WooDash
	 * @since   1.0.0
	 */
	class Menu {


		public static $add_items;
		public static $default_menus = [
			// dashboard
			'index.php',

			// posts
			'edit.php',

			// pages
			'edit.php?post_type=page',

			// media
			'upload.php',

			// products
			'edit.php?post_type=product',

			// users
			'users.php',

			// WC Admin
			'wc-admin&path=/analytics/revenue',
		];


		/**
		 * Class constructor.
		 */
		public function __construct() {
			// add items to be added to admin menu
			// @codeCoverageIgnoreStart
			self::$add_items = [
				// @codeCoverageIgnoreEnd
				'orders'    => [
					'name'     => esc_html__( 'Orders', 'woodash' ),
					'link'     => 'edit.php?post_type=shop_order',
					'priority' => 101,
					'icon'     => 'dashicons-heart',
				],
				'stock'     => [
					'name'     => esc_html__( 'Stock', 'woodash' ),
					'link'     => 'admin.php?page=wc-reports&tab=stock',
					'priority' => 102,
					'icon'     => 'dashicons-archive',
				],
				'customers' => [
					'name'     => esc_html__( 'Customers', 'woodash' ),
					'link'     => 'admin.php?page=wc-reports&tab=customers&report=customer_list',
					'priority' => 103,
					'icon'     => 'dashicons-groups',
				],
				'reports'   => [
					'name'     => esc_html__( 'All Reports', 'woodash' ),
					'link'     => 'admin.php?page=wc-reports',
					'priority' => 105,
					'icon'     => 'dashicons-chart-area',
				],
			];

			return self::$add_items;
		}

	}

	// @codeCoverageIgnoreStart
	new Menu();
	// @codeCoverageIgnoreEnd

}
