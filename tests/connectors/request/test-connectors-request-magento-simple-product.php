<?php

use Magento_Bridge\Connector\Request\Magento_Simple_Product;

/**
 * Tests Simple Product
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */
class MagentoSimpleProductAPITest extends WP_UnitTestCase {

	protected $connector;

	public function setUp() {
		$this->connector = new Magento_Simple_Product( 'some-sku' );
		parent::setUp();
	}

	public function testShouldNotBeAbleToChangeRequestType() {
		$this->connector->set_request_type( 'POST' );

		$this->assertEquals( 'GET', $this->connector->get_request_type() );
	}

	public function testSetRequestAddsCorrectParameter() {
		$this->connector->set_request( '/product/%s/%d', 'some-other-sku', 5 );

		$this->assertEquals( '/product/some-other-sku/5', $this->connector->get_request() );
	}
}
