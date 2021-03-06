<?php
/**
 * Product Fetcher
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Product;

/**
 * Class Product_Fetch
 * @package Magento_Bridge\Product
 */
class Product_Fetch {

	/**
	 * SKU
	 * @var string
	 */
	protected $sku = '';

	/**
	 * @var null|\Magento_Bridge\Product\Product
	 */
	protected $product;

	/**
	 * Product_Fetch constructor.
	 */
	public function __construct( $sku ) {
		$this->sku = $sku;
	}

	public function fetch() {

	}

	public function set_product( Product $product ) {
		$this->product = $product;
	}
}
