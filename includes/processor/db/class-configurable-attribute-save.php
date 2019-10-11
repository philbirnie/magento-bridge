<?php
/**
 * Saves Relationship Between Parent and Child
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

class Configurable_Attribute_Save {

	protected $result;

	/**
	 * Configurable_Attribute_Save constructor.
	 *
	 * @param $result
	 */
	public function __construct( $result ) {
		$this->result = is_string( $result ) ? json_decode( $result ) : $result;
	}

	public function save() {

		global $wpdb;

		$configurable_attributes_table = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		if ( ! $this->result || ! count( $this->result ) ) {
			return;
		}

		foreach ( $this->result as $attribute_result ) {
			$action = 'INSERT';


			$existing_record = $this->get_existing_record( $attribute_result->attribute_id );

			if ( $existing_record ) {
				$action = 'REPLACE';
			}

			$wpdb->_insert_replace_helper(
				$configurable_attributes_table,
				$this->transpose( $attribute_result ),
				$this->get_format(),
				$action
			);
		}
		return;
	}

	protected function transpose( $attribute_result ) {
		return [
			'product_id'      => $attribute_result->product_id,
			'attribute_id'    => $attribute_result->attribute_id,
			'attribute_label' => $attribute_result->label,
			'attribute_code'  => $attribute_result->price ?? '',
		];
	}

	protected function get_format() {
		return [
			'%d', '%d', '%s', '%s'
		];
	}

	public function get_product_attribute_ids() {
		return array_reduce( $this->result, function ( $carry, $attribute_result ) {
			$carry[] = (int) $attribute_result->attribute_id;
			return $carry;
		}, [] );
	}

	public function get_existing_record( $attribute_id ) {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * from ${table} WHERE attribute_id = '%d'", $attribute_id ) );
	}
}
