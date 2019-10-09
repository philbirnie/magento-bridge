<?php

use Magento_Bridge\Adapters\Product_Adapter_Wordpress;
use Magento_Bridge\Product\Product;

/**
 * Cache Test
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */
class ProductAdapterWordpressTest extends WP_UnitTestCase {

	protected $adapter;

	protected $configurable_adapter;

	public function setUp() {

		/** @var wpdb */
		global $wpdb;

		parent::setUp();
		$this->adapter              = new Product_Adapter_Wordpress( 'some-product' );
		$this->configurable_adapter = new Product_Adapter_Wordpress( 'tracer360' );

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_products',
			[
				'sku'           => 'some-product',
				'mage_id'       => 2,
				'name'          => 'Some Product',
				'price'         => 15.09,
				'special_price' => 14.50,
				'type'          => 'simple',
				'related'       => '',
				'cache_time'    => time(),
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_products',
			[
				'sku'           => 'some-expired-product',
				'mage_id'       => 3,
				'name'          => 'Some Expired Product',
				'price'         => 15.09,
				'special_price' => 14.50,
				'type'          => 'simple',
				'related'       => '',
				'cache_time'    => time() - Product_Adapter_Wordpress::CACHE_AGE - 1,
			]
		);

		$this->insertConfigurableProduct();

	}

	public function testShouldReturnFalseIfProductDoesNotExist() {

		$adpater = new Product_Adapter_Wordpress( 'some-non-existent-product' );

		$product = $adpater->get_product();

		$this->assertInstanceOf( Product::class, $product );
	}

	public function testShouldReturnProductIfProductDoesExist() {

		$product = $this->adapter->get_product();

		$this->assertInstanceOf( Product::class, $product );

		$this->assertEquals( 2, $product->mage_id );
		$this->assertEquals( 'Some Product', $product->name );
	}

	public function testShouldReturnTrueIfCacheIsValid() {
		$this->assertTrue( $this->adapter->is_cache_valid() );
	}

	public function testShouldReturnFalseIfCacheIsExpired() {

		$adpater = new Product_Adapter_Wordpress( 'some-expired-product' );

		$this->assertFalse( $adpater->is_cache_valid() );
	}

	public function testShouldReturnChildrenProductsIfConfigurable() {

		/** @var Product $tracer */
		$tracer = $this->configurable_adapter->get_product();

		$this->assertEquals( 2, count( $tracer->children ) );
	}

	public function testShouldReturnConfigurableAttributesIfConfigurable() {
		$tracer = $this->configurable_adapter->get_product();

		$this->assertNotEmpty( $tracer->configurable_attributes );
		$this->assertEquals( 'tracer_size', $tracer->configurable_attributes['138']->code );
		$this->assertEquals( 138, $tracer->configurable_attributes['138']->id );
		$this->assertEquals( 'Size', $tracer->configurable_attributes['138']->label );
	}

	public function testShouldReturnAllConfigurableAttributeValuesForProduct() {
		$tracer           = $this->configurable_adapter->get_product();
		$attribute_values = $tracer->configurable_attributes['138']->values;
		$this->assertNotEmpty( $attribute_values );
		$this->assertEquals( 2, count( $tracer->configurable_attributes['138']->values ) );
	}

	public function testChildProductContainsCorrectAttribute() {
		$tracer = $this->configurable_adapter->get_product();

		/** @var Product $small_product */
		$small_product  = $tracer->children[0];
		$medium_product = $tracer->children[1];

		$this->assertEquals( $small_product->attributes['tracer_size'], 'S' );
		$this->assertEquals( $medium_product->attributes['tracer_size'], 'M' );
	}


	protected function insertConfigurableProduct() {

		global $wpdb;

		/** Set up Product and children */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_products',
			[
				'sku'           => 'tracer360',
				'mage_id'       => 4,
				'name'          => 'Tracer 360',
				'price'         => 0,
				'special_price' => 0,
				'type'          => 'configurable',
				'related'       => '',
				'cache_time'    => time(),
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_products',
			[
				'sku'           => 'tracer360-S',
				'mage_id'       => 5,
				'name'          => 'Tracer 360 S',
				'price'         => 65,
				'special_price' => 0,
				'type'          => 'simple',
				'related'       => '',
				'cache_time'    => time(),
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_products',
			[
				'sku'           => 'tracer360-M',
				'mage_id'       => 6,
				'name'          => 'Tracer 360 M',
				'price'         => 68,
				'special_price' => 0,
				'type'          => 'simple',
				'related'       => '',
				'cache_time'    => time(),
			]
		);

		/** Set up Relationship */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_configurable_children',
			[
				'parent_id' => 4,
				'child_id'  => 5,
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_configurable_children',
			[
				'parent_id' => 4,
				'child_id'  => 6,
			]
		);

		/** Set up Configurable Label */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_configurable_attributes',
			[
				'product_id'      => 4,
				'attribute_code'  => 'tracer_size',
				'attribute_id'    => 138,
				'attribute_label' => 'Size',
			]
		);

		/** Set up Product Attributes */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_attribute_label',
			[
				'attribute_id'          => 138,
				'value'                 => 100,
				'attribute_value_label' => 'S',
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_attribute_label',
			[
				'attribute_id'          => 138,
				'value'                 => 101,
				'attribute_value_label' => 'M',
			]
		);

		/** Set up Child Attributes */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_child_attributes',
			[
				'product_id'     => 5,
				'attribute_code' => 'tracer_size',
				'value'          => 100,
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_child_attributes',
			[
				'product_id'     => 6,
				'attribute_code' => 'tracer_size',
				'value'          => 101,
			]
		);

	}

	public function tearDown() {
		parent::tearDown(); // TODO: Change the autogenerated stub
	}
}
