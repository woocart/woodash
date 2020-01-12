<?php


use Niteo\WooCart\WooDash\Menu;
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase {


	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}


	/**
	 * @covers \Niteo\WooCart\WooDash\Menu::__construct
	 */
	public function testConstructor() {
		$menu = new Menu();

		$this->assertEquals(
			[
				'orders'    => [
					'name'     => 'Orders',
					'link'     => 'edit.php?post_type=shop_order',
					'priority' => 101,
					'icon'     => 'dashicons-heart',
				],
				'stock'     => [
					'name'     => 'Stock',
					'link'     => 'admin.php?page=wc-reports&tab=stock',
					'priority' => 102,
					'icon'     => 'dashicons-archive',
				],
				'customers' => [
					'name'     => 'Customers',
					'link'     => 'admin.php?page=wc-reports&tab=customers&report=customer_list',
					'priority' => 103,
					'icon'     => 'dashicons-groups',
				],
				'reports'   => [
					'name'     => 'All Reports',
					'link'     => 'admin.php?page=wc-reports',
					'priority' => 105,
					'icon'     => 'dashicons-chart-area',
				],
			],
			$menu->__construct()
		);
	}

}
