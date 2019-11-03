<?php
/**
 * Product Processor
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor;

use Magento_Bridge\Adapters\Product_Adapter_Wordpress;
use Magento_Bridge\Connector\Connector_Interface;
use Magento_Bridge\Connector\Request\Magento_Product_Attribute;
use Magento_Bridge\Connector\Request\Magento_Simple_Product;
use Magento_Bridge\Processor\Db\Configurable_Attribute_Save;
use Magento_Bridge\Processor\Db\Product_Attribute_Save;
use Magento_Bridge\Processor\Db\Product_Save;
use Magento_Bridge\Processor\Db\Related_Relationship_Save;

class Product_Update {

	/** @var array Connectors */
	public static $connectors = [];

	public static function run( $clear_cache = false ) {

		if ( $clear_cache ) {
			self::clear_cache();
		}

		$expired_products = self::get_expired_products();
		$new_products     = self::get_new_product_skus();

		$products_to_update = array_merge( $expired_products, $new_products );

		/** @var string $expired_product SKU. */
		foreach ( $products_to_update as $product ) {
			try {
				self::update_product( $product );
			} catch ( \Exception $e ) {
				error_log( sprintf( 'Import for %s failed: %s', $product, $e->getMessage() ) );
			}
		}
	}

	/** Ability to override a connector if needed. */
	public static function set_connector( $connector, $connector_instance ) {
		self::$connectors[ $connector ] = $connector_instance;
	}

	public static function get_new_product_skus() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		$all_products = self::get_all_product_skus();

		$result = $wpdb->get_col(
			"SELECT sku
			FROM  ${table}"
		);

		return array_diff( $all_products, $result );
	}

	public static function get_expired_products() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		$cache_threshold = time() - Product_Adapter_Wordpress::CACHE_AGE;

		$skus = self::get_all_product_skus();

		if ( ! $skus ) {
			return [];
		}

		$skus_string = "'" . implode( "','", $skus ) . "'";

		$result = $wpdb->get_col(
			"SELECT sku 
			FROM  ${table} 
			WHERE sku in ( ${skus_string} ) AND 
			cache_time < {$cache_threshold}"
		);

		if ( ! $result ) {
			return [];
		}

		return $result;
	}

	public static function clear_cache() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		return $wpdb->query( "UPDATE {$table} SET cache_time = 0" );
	}


	public static function get_all_product_skus(): array {

		$skus = [];

		$product_query = new \WP_Query(
			[
				'post_type'  => 'product',
				'meta_query' => [
					[
						'meta_key' => 'product_sku',
						'compare'  => '!=',
						'value'    => '',
					]
				]
			]
		);

		if ( $product_query->have_posts() ) {
			while ( $product_query->have_posts() ) {
				$product_query->the_post();
				array_push( $skus, get_post_meta( get_the_ID(), 'product_sku', true ) );
			}
		}

		return $skus;
	}

	/**
	 * Updates Product from Magento & Saves to DB
	 *
	 * @param                     $sku
	 * @param Connector_Interface $connector
	 *
	 * @throws \Exception If unable to update
	 */
	public static function update_product( $sku ) {

		$simple_product_connector = self::$connectors['simple'] ?? new Magento_Simple_Product( $sku );

		$product_response = $simple_product_connector->send_request();

		if ( ! $product_response ) {
			throw new \Exception( 'Unable to get response for product; not updating' );
		}

		$product_save = new Product_Save( $product_response );
		$product_save->save();
		$saved_product_id = $product_save->get_id();

		if ( ! $saved_product_id ) {
			throw new \Exception( sprintf( 'Failure saving product %s', $sku ) );
		}

		if ( $product_save->save_was_configurable() ) {
			//Save Configurable Attributes
			$configurable_attribute_save = new Configurable_Attribute_Save( $product_save->get_configurable_attributes() );
			$configurable_attribute_save->save();
			$product_attribute_ids = $configurable_attribute_save->get_product_attribute_ids();

			//Update Attribute Labels (and shim in attribute_code)
			foreach ( $product_attribute_ids as $attribute_id ) {
				$attribute_label_connector = self::$connectors['product_attribute'] ?? new Magento_Product_Attribute( $attribute_id );
				$attribute_response        = $attribute_label_connector->send_request();

				$attribute_save = new Product_Attribute_Save( $attribute_response );
				$attribute_save->save();
			}
		}

		// Save Related Products
		$related_product_items = $product_save->get_related_items();
		$related_product_ids   = [];
		foreach ( $related_product_items as $related_product_item ) {
			$related_product_connector = self::$connectors['simple'] ?? new Magento_Simple_Product( $related_product_item->linked_product_sku );
			$related_product_response  = $related_product_connector->send_request();
			if ( $related_product_response ) {
				$related_product_save = new Product_Save( $related_product_response, true );
				$related_product_save->save();
				$related_product_id = $related_product_save->get_id();
				if ( $related_product_id ) {
					$related_product_ids[] = $related_product_id;
				}
			}
		}

		$related_relationship_save = new Related_Relationship_Save( $related_product_ids, $saved_product_id );
		$related_relationship_save->save();
	}
}
