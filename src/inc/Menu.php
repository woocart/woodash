<?php

namespace Niteo\WooCart\WooDash;

/**
 * Class for menu items.
 *
 * @package Niteo\WooCart\WooDash
 */
class Menu {

	/**
	 * @var array
	 */
	public static $add_items;

	/**
	 * @var array
	 */
	public static $default_menus = array(
		// Dashboard.
		'index.php',

		// Posts.
		'edit.php',

		// Pages.
		'edit.php?post_type=page',

		// Media.
		'upload.php',

		// Products.
		'edit.php?post_type=product',

		// Users.
		'users.php',

		// WC Admin.
		'wc-admin&path=/analytics/revenue',
	);


	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Items to be added to admin menu.
		self::$add_items = array(
			'orders'    => array(
				'name'     => esc_html__( 'Orders', 'woodash' ),
				'link'     => 'edit.php?post_type=shop_order',
				'priority' => 101,
				'icon'     => 'dashicons-heart',
			),
			'stock'     => array(
				'name'     => esc_html__( 'Stock', 'woodash' ),
				'link'     => 'admin.php?page=wc-reports&tab=stock',
				'priority' => 102,
				'icon'     => 'dashicons-archive',
			),
			'customers' => array(
				'name'     => esc_html__( 'Customers', 'woodash' ),
				'link'     => 'admin.php?page=wc-reports&tab=customers&report=customer_list',
				'priority' => 103,
				'icon'     => 'dashicons-groups',
			),
			'reports'   => array(
				'name'     => esc_html__( 'All Reports', 'woodash' ),
				'link'     => 'admin.php?page=wc-reports',
				'priority' => 105,
				'icon'     => 'dashicons-chart-area',
			),
		);

		return self::$add_items;
	}

}

new Menu();
