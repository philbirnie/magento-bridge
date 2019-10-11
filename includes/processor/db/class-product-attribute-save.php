<?php
/**
 * Product Saver
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;


class Product_Attribute_Save {

	protected $result;

	public function __construct( $result ) {
		$this->result = is_string( $result ) ? json_decode( $result ) : $result;
	}

	public function save() {

		if ( ! $this->result || ! isset( $this->result->attribute_code ) ) {
			return;
		}
		$this->update_attribute_codes();

	}

	protected function update_attribute_codes() {
		global $wpdb;

		$configurable_attributes = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE ${configurable_attributes} set attribute_code = '%s' WHERE attribute_id = %d",
				$this->result->attribute_code,
				$this->result->attribute_id
			)
		);
	}
}
