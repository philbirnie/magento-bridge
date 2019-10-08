<?php
/**
 * Abstract Class for Adapters
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Adapters;

use Magento_Bridge\Product\Product;

abstract class Product_Adapter_Abstract {

	/**
	 * Product Container
	 *
	 * @var Product
	 */
	protected $product;

	/**
	 * Product SKU.
	 *
	 * @var string
	 */
	protected $sku = '';

	/**
	 * Product_Adapter_Wordpress constructor.
	 *
	 * @param string $sku
	 */
	public function __construct( $sku ) {
		$this->sku = $sku;
	}
}
