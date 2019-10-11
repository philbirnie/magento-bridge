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
use Magento_Bridge\Connector\Request\Magento_Configurable_Children;
use Magento_Bridge\Connector\Request\Magento_Product_Attribute;
use Magento_Bridge\Connector\Request\Magento_Simple_Product;
use Magento_Bridge\Processor\Db\Configurable_Attribute_Save;
use Magento_Bridge\Processor\Db\Configurable_Children_Save;
use Magento_Bridge\Processor\Db\Product_Attribute_Save;
use Magento_Bridge\Processor\Db\Product_Save;

class Product_Update {

	/** @var array Connectors */
	public static $connectors = [];

	public static function run() {
		$expired_products = self::get_expired_products();

		/** @var string $expired_product SKU. */
		foreach ( $expired_products as $expired_product ) {
			try {
				self::update_product( $expired_product );
			} catch ( \Exception $e ) {
				error_log( sprintf( 'Import for %s failed: %s', $expired_product, $e->getMessage() ) );
			}
		}
	}

	/** Ability to override a connector if needed. */
	public static function set_connector( $connector, $connector_instance ) {
		self::$connectors[ $connector ] = $connector_instance;
	}

	public static function get_expired_products() {
		global $wpdb;

		$cache_threshold = time() - Product_Adapter_Wordpress::CACHE_AGE;

		$skus = self::get_all_product_skus();

		if ( ! $skus ) {
			return [];
		}

		$table = \Magento_Bridge::get_table_name( 'products' );

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

			// Save Configurable Products
			$configurable_product_connector = self::$connectors['configurable'] ?? new Magento_Configurable_Children( $sku );
			$configurable_children_response = $configurable_product_connector->send_request();

			if ( ! $configurable_children_response ) {
				throw new \Exception( 'Unable to get response for children products for ' . $sku . '; cannot add children products!' );
			}

			/** @var int $parent_id Parent ID. */
			$parent_id = $product_save->get_id();

			//TODO: Add Attribute
			$configurable_children_save = new Configurable_Children_Save( $configurable_children_response, $parent_id );
			$configurable_children_save->save();

		}
	}
}
