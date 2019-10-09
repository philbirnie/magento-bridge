<?php
/**
 * Magento Product Adapter
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

use Magento_Bridge\Adapters\Product_Adapter_Magento;
use Magento_Bridge\Product\Product;

/**
 * Cache Test
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */
class ProductAdapterMagentoTest extends WP_UnitTestCase {

	protected $adapter;

	public function setUp() {
		parent::setUp();
		$this->adapter = new Product_Adapter_Magento( 'some-product' );

	}

	public function tearDown() {
		parent::tearDown(); // TODO: Change the autogenerated stub
	}
}
