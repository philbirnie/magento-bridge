<?php
/**
 * Tests Activation Script
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

class ActivationTest extends WP_UnitTestCase {

	public function testCreatesTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_products', $tables, 'Table Exists');
	}
}
