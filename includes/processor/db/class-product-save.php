<?php
/**
 * Product Saver
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;


class Product_Save {

	use Magento_Db_Helpers_Trait;

	protected $result;

	public function __construct( $result ) {
		$this->result = is_string( $result ) ? json_decode( $result ) : $result;
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
			'price'             => $this->result->price,
			'special_price'     => $this->get_special_price_from_attributes( $this->result ),
			'type'              => $this->result->type_id,
			'main_photo_url'    => $this->get_main_image_from_attributes( $this->result ),
			'additional_photos' => json_encode( $this->get_additional_photos_from_attributes( $this->result ) ),
			'cache_time'        => time(),
		];
	}

	/**
	 * Gets format for fields
	 *
	 * @return array
	 */
	protected function get_format(): array {
		return [
			'%s', '%d', '%s', '%f', '%f', '%f', '%s', '%s', '%d'
		];
	}

	public function delete_product() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'products' );

		$wpdb->delete( $table, [ 'sku' => $this->result->sku ] );
	}

}
