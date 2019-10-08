<?php
/**
 * Product Adapter Interface
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Adapters;

use Magento_Bridge\Product\Product;

interface Product_Adapter_Interface {

	/**
	 * Checks if cache is valid
	 *
	 * @return bool
	 */
	public function is_cache_valid() :bool;

	/**
	 * Returns Product.
	 *
	 * @return Product
	 */
	public function get_product() : Product;
}
