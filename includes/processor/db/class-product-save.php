<?php
/**
 * Product Saver
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

use Magento_Bridge\Adapters\Product_Adapter_Wordpress;

class Product_Save {

	use Magento_Db_Helpers_Trait;

	protected $result;

	protected $ignore_if_cache_is_okay;

	public function __construct( $result, $ignore_if_cache_is_okay = false ) {
		$this->result                  = is_string( $result ) ? json_decode( $result ) : $result;
		$this->ignore_if_cache_is_okay = $ignore_if_cache_is_okay;
	}

	public function save() {

		global $wpdb;

		$product_table = \Magento_Bridge::get_table_name( 'products' );

		$action = 'INSERT';

		if ( ! $this->result || ! $this->result->sku ) {
			return false;
		}

		$existing_record = $this->get_existing_record();

		if ( $existing_record ) {
			if ( (int) $existing_record->mage_id === (int) $this->result->id ) {
				$action = 'REPLACE';

				/** If cache is okay, skip; this is frequently used for related products */
				if ( $this->ignore_if_cache_is_okay && $existing_record->cache_time > time() - Product_Adapter_Wordpress::CACHE_AGE ) {
					return true;
				}

			} else {
				$this->delete_product();
			}
		}

		return $wpdb->_insert_replace_helper(
			$product_table,
			$this->transpose(),
			$this->get_format(),
			$action
		);
	}

	public function get_existing_record() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * from ${table} WHERE sku = '%s'", $this->result->sku ) );
	}

	/**
	 * Checks to see if result contains a configurable product
	 *
	 * @return bool
	 */
	public function save_was_configurable(): bool {
		return 'configurable' === $this->result->type_id;
	}

	public function get_configurable_attributes(): array {
		if ( ! $this->save_was_configurable() ) {
			return [];
		}
		return $this->result->extension_attributes->configurable_product_options ?? [];
	}

	/**
	 * Returns an array of related items as \stdClass Objects
	 *
	 * @return array
	 */
	public function get_related_items(): array {
		return array_filter( $this->result->product_links ?? [], function ( $product_link ) {
			return 'related' === $product_link->link_type;
		} );
	}

	public function get_configurable_attribute_as_simple( $parent_id ) {
		global $wpdb;

		if ( $this->save_was_configurable() ) {
			return [];
		}

		$configurable_attributes_table = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		$configurable_attributes = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT attribute_code AS attribute_code FROM ${configurable_attributes_table} WHERE product_id=%d", $parent_id ), ARRAY_N );

		return array_reduce( $configurable_attributes, function ( $carry, $result ) {
			$carry[] = $result[0];
			return $carry;
		}, [] );

	}

	/**
	 * Returns Magento ID for result.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->result->id ?? 0;
	}

	/**
	 * Transpose form API response to Database Fields.
	 *
	 * @return array
	 */
	protected function transpose() {
		return [
			'sku'               => $this->result->sku,
			'mage_id'           => $this->result->id,
			'name'              => $this->result->name,
			'price'             => $this->get_price($this->result),
			'special_price'     => $this->get_special_price_from_attributes( $this->result ),
			'type'              => $this->result->type_id,
			'main_photo_url'    => $this->get_main_image_from_attributes( $this->result ),
			'additional_photos' => json_encode( $this->get_additional_photos_from_attributes( $this->result ) ),
			'cache_time'        => time(),
			'description'       => $this->get_description_from_attributes( $this->result ),
			'url'               => $this->get_url_from_attributes( $this->result ),
		];
	}

	/**
	 * Gets format for fields
	 *
	 * @return array
	 */
	protected function get_format(): array {
		return [
			'%s', '%d', '%s', '%f', '%f', '%s', '%s', '%s', '%d'
		];
	}

	public function delete_product() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		$wpdb->delete( $table, [ 'sku' => $this->result->sku ] );
	}

}
