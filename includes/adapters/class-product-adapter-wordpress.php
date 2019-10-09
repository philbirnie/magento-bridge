<?php
/**
 * WordPress Product Adapter
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Adapters;

use Magento_Bridge\Product\Attribute;
use Magento_Bridge\Product\Attribute_Value;
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
	 * Get Product from Database
	 *
	 * @return Product
	 */
	protected function get_product_from_database(): Product {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_products';

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
			$result = $this->add_child_products( $result );
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
		$product->additional_photos = isset( $result->additional_photos ) && strlen( $result->additional_photos ) > 0 ? explode( ',', $result->additional_photos ) : [];
		$product->type              = $result->type ?? 'simple';
		$product->attributes        = $this->get_product_attributes( $product );

		return $product;
	}

	protected function add_child_products( Product $current_product ): Product {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$configurable_pivot_table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_configurable_children';

		$product_table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_products';

		/** @var  $query */
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * 
				FROM ${product_table} as products
				INNER JOIN ${configurable_pivot_table} as configurable_children
				ON products.mage_id = configurable_children.child_id
				WHERE configurable_children.parent_id=%d",
				$current_product->mage_id )
		);

		if ( ! $result ) {
			return $current_product;
		}

		foreach ( $result as $child_product_result ) {
			$current_product->children[] = $this->transpose( $child_product_result );
		}

		return $current_product;
	}

	protected function add_configurable_attributes( Product $current_product ): Product {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$configurable_attribute_table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_configurable_attributes';
		$attribute_table              = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_attribute_label';

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

			if ( ! isset( $current_product->configurable_attributes[ $configurable_attribute_label->attribute_id ] ) ) {
				$attribute                                                                               = new Attribute();
				$attribute->code                                                                         = $configurable_attribute_label->attribute_code;
				$attribute->id                                                                           = $configurable_attribute_label->attribute_id;
				$attribute->label                                                                        = $configurable_attribute_label->attribute_label;
				$current_product->configurable_attributes[ $configurable_attribute_label->attribute_id ] = $attribute;
			}

			$value        = new Attribute_Value();
			$value->label = $configurable_attribute_label->attribute_value_label;
			$value->value = $configurable_attribute_label->value;

			$current_product->configurable_attributes[ $configurable_attribute_label->attribute_id ]->values[] = $value;
		}

		return $current_product;
	}

	protected function get_product_attributes( Product $product ): array {
		if ( ! $product->mage_id || 'simple' !== $product->type ) {
			return [];
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		$child_attributes_table       = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_child_attributes';
		$configurable_attribute_table = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_configurable_attributes';
		$attribute_table              = $wpdb->prefix . \Magento_Bridge::BRIDGE_TABLE . '_attribute_label';

		/** @var  $query */

		/** @var  $query */
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT child_attributes.attribute_code,child_attributes.value,attribute_label.attribute_value_label as label
					FROM ${child_attributes_table} as child_attributes
					INNER JOIN ${configurable_attribute_table} as configurable_attributes 
					ON child_attributes.attribute_code = configurable_attributes.attribute_code
					INNER JOIN ${attribute_table} as attribute_label
					ON child_attributes.value = attribute_label.value
				WHERE child_attributes.product_id=%d",
				$product->mage_id )
		);

		$attributes = [];

		foreach ( $result as $attribute ) {
			$attributes[ $attribute->attribute_code ] = $attribute->label;
		}

		return $attributes;
	}
}
