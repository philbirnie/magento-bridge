<?php
/**
 * WordPress Product Adapter
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Adapters;

use Magento_Bridge\Product\Product;

class Product_Adapter_Wordpress extends Product_Adapter_Abstract implements Product_Adapter_Interface {

	const CACHE_AGE = 600;

	/**
	 * @return bool
	 */
	public function is_cache_valid(): bool {
		if ( ! $this->get_product() ) {
			return false;
		}
		return time() - $this->product->cache_time < self::CACHE_AGE;
	}


	public function get_product(): Product {
		if ( $this->product ) {
			return $this->product;
		}

		$this->product = $this->get_product_from_database();
		return $this->product;
	}

	protected function get_product_from_database() {

		$product = null;

		/** @var \wpdb $wpdb */
		global $wpdb;

		$table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE;

		/** @var  $query */
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
				FROM ${table}
				WHERE sku=%s",
				$this->sku )
		);

		if ( ! $result ) {
			return new Product();
		}

		return $this->transpose( $result );
	}

	private function transpose( $result ): Product {
		$product = new Product();

		$product->sku               = $result->sku ?? '';
		$product->mage_id           = $result->mage_id ?? 0;
		$product->name              = $result->name ?? '';
		$product->cache_time        = $result->cache_time ?? 0;
		$product->price             = $result->price ?? 0;
		$product->special_price     = $result->special_price;
		$product->related           = strlen( $result->related ) > 0 ? explode( ',', $result->related ) : [];
		$product->main_photo_url    = $result->main_photo_url;
		$product->additional_photos = strlen( $result->related ) > 0 ? explode( ',', $result->related ) : [];
		$product->type              = $result->type ?? 'simple';

		return $product;
	}

}
