<?php
/**
 * Unit tests for `Menu` class.
 */

namespace Niteo\WooCart\WooDash\Tests;

use Niteo\WooCart\WooDash\Menu;
use PHPUnit\Framework\TestCase;

/**
 * Tests Menu class functions in isolation.
 *
 * @package Niteo\WooCart\WooDash
 * @coversDefaultClass \Niteo\WooCart\WooDash\Menu
 */
class MenuTest extends TestCase {

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
		$menu = new Menu();

		$this->assertEquals(
			array(
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
				'reports'   => array(
					'name'     => 'All Reports',
					'link'     => 'admin.php?page=wc-reports',
					'priority' => 105,
					'icon'     => 'dashicons-chart-area',
				),
			),
			$menu->__construct()
		);
	}

}
