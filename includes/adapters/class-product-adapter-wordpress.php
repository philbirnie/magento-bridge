<?php
/**
 * WordPress Product Adapter
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Adapters;

use Magento_Bridge\Connector\Connector_Magento_API_Abstract;
use Magento_Bridge\Product\Attribute;
use Magento_Bridge\Product\Attribute_Value;
use Magento_Bridge\Product\Product;

class Product_Adapter_Wordpress extends Product_Adapter_Abstract implements Product_Adapter_Interface {

	const CACHE_AGE = 60 * 120;

	/**
	 * @return bool
	 */
	public function is_cache_valid(): bool {
		if ( ! $this->get_product() ) {
			return false;
		}
		return time() - $this->product->cache_time < self::CACHE_AGE;
	}


	/**
	 * Get Product
	 *
	 * @return Product
	 */
	public function get_product(): Product {
		if ( $this->product ) {
			return $this->product;
		}

		$this->product = $this->get_product_from_database();
		return $this->product;
	}

	/**
	 * Returns an array of related products
	 *
	 * @return array
	 */
	public function get_related_products(): array {

		$related_skus = $this->get_related_skus();

		$related_products = [];

		foreach ( $related_skus as $related_sku ) {
			$related_product    = new Product_Adapter_Wordpress( $related_sku );
			$related_products[] = $related_product->get_product();
		}
		return $related_products;
	}

	/**
	 * Get Product from Database
	 *
	 * @return Product
	 */
	protected function get_product_from_database(): Product {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		/** @var  $query */
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * 
				FROM ${table}
				WHERE sku=%s",
				$this->sku )
		);

		$result = $this->transpose( $result );

		if ( 'configurable' == $result->type ) {
			$result = $this->add_configurable_attributes( $result );
		}

		return $result;
	}

	private function transpose( $result ): Product {
		$product = new Product();

		$product->sku               = $result->sku ?? '';
		$product->mage_id           = $result->mage_id ?? 0;
		$product->name              = $result->name ?? '';
		$product->cache_time        = $result->cache_time ?? 0;
		$product->price             = $result->price ?? 0;
		$product->special_price     = $result->special_price ?? null;
		$product->related           = isset( $result->related ) && strlen( $result->related ) > 0 ? explode( ',', $result->related ) : [];
		$product->main_photo_url    = $result->main_photo_url ?? '';
		$product->additional_photos = isset( $result->additional_photos ) && strlen( $result->additional_photos ) > 0 ? json_decode( $result->additional_photos ) : [];
		$product->type              = $result->type ?? 'simple';
		$product->description       = $result->description ?? '';
		$product->url               = $this->get_product_url( $result->url ?? '' );

		return $product;
	}

	/**
	 * $param string $url Stored Url.
	 *
	 * @return string
	 */
	protected function get_product_url( $url ) {
		if ( false !== strpos( $url, 'http' ) ) {
			return $url;
		}

		try {
			$base_url = Connector_Magento_API_Abstract::get_url();
		} catch ( \Exception $e ) {
			error_log( 'Warning: Base URL not set' );
			$base_url = '';
		}

		return sprintf( '%s/%s.html', $base_url, $url );
	}

	protected function add_configurable_attributes( Product $current_product ): Product {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$configurable_attribute_table = \Magento_Bridge::get_table_name( 'configurable_attributes' );
		$attribute_table              = \Magento_Bridge::get_table_name( 'attribute_label' );

		/** @var  $query */
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
						attribute_labels.attribute_id as attribute_id, 
						attribute_labels.value,
						 attribute_labels.attribute_value_label,
						 configurable_attributes.attribute_code as attribute_code,
						 configurable_attributes.attribute_label
				FROM ${attribute_table} as attribute_labels
				INNER JOIN ${configurable_attribute_table} as configurable_attributes
				ON attribute_labels.attribute_id = configurable_attributes.attribute_id 
				WHERE configurable_attributes.product_id=%d",
				$current_product->mage_id )
		);

		if ( ! $result ) {
			return $current_product;
		}

		foreach ( $result as $configurable_attribute_label ) {

			if ( ! isset( $current_product->configurable_attributes[ $configurable_attribute_label->attribute_code ] ) ) {
				$attribute                                                                                 = new Attribute();
				$attribute->code                                                                           = $configurable_attribute_label->attribute_code;
				$attribute->id                                                                             = $configurable_attribute_label->attribute_id;
				$attribute->label                                                                          = $configurable_attribute_label->attribute_label;
				$current_product->configurable_attributes[ $configurable_attribute_label->attribute_code ] = $attribute;
			}

			$value        = new Attribute_Value();
			$value->label = $configurable_attribute_label->attribute_value_label;
			$value->value = $configurable_attribute_label->value;

			$current_product->configurable_attributes[ $configurable_attribute_label->attribute_code ]->values[] = $value;
		}

		return $current_product;
	}

	protected function get_related_skus(): array {
		global $wpdb;

		$product = $this->get_product();

		$related_products = \Magento_Bridge::get_table_name( 'related_products' );

		$products = \Magento_Bridge::get_table_name( 'products' );

		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT sku
				FROM ${related_products} as related_products
				INNER JOIN ${products} as products
				ON products.mage_id = related_products.related_id
				 WHERE parent_id = %d",
				$product->mage_id
			)
		);

	}
}
