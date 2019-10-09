<?php
/**
 * Tests Activation Script
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

class ActivationTest extends WP_UnitTestCase {

	public function testCreatesProductTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_products', $tables, 'Product Table Exists');
	}

	public function testCreatesChildTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_configurable_children', $tables, 'Configurable Children Table Exists');
	}


	public function testCreatesConfigurableAttributesTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_configurable_attributes', $tables, 'Configurable Attributes Table Exists');
	}

	public function testCreatesAttributeLabelTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_attribute_label', $tables, 'Attribute Label Table Exists');
	}

	public function testCreatesChildAttributesTable(  ) {

		/** @var wpdb $wpdb  */
		global $wpdb;

		$this->assertNotNull($wpdb, 'Database object exists');

		$tables = $wpdb->get_col('SHOW TABLES');

		$this->assertContains($wpdb->get_blog_prefix() . 'magento_bridge_child_attributes', $tables, 'Child Attribute Table Exists');
	}


}
