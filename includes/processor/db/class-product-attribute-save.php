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

		global $wpdb;

		$attribute_labels_table = \Magento_Bridge::get_table_name( 'attribute_label' );

		if ( ! $this->result || ! isset( $this->result->attribute_code ) ) {
			return;
		}
		$this->update_attribute_codes();

		if ( ! $this->verify_relationship() ) {
			$wpdb->delete( $attribute_labels_table, [ 'attribute_id' => $this->result->attribute_id ] );
			$this->update_labels();
		}
	}

	protected function update_attribute_codes() {
		global $wpdb;

		$configurable_attributes_table = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE ${configurable_attributes_table} set attribute_code = '%s' WHERE attribute_id = %d",
				$this->result->attribute_code,
				$this->result->attribute_id
			)
		);
	}

	protected function update_labels() {
		global $wpdb;

		if ( ! isset( $this->result->options ) ) {
			return;
		}

		$attribute_labels_table = \Magento_Bridge::get_table_name( 'attribute_label' );

		$attribute_id = $this->result->attribute_id;

		/** @var \stdClass $option */
		foreach ( $this->result->options as $option ) {
			if ( $option->value && $option->label ) {
				$wpdb->insert( $attribute_labels_table, [
					'attribute_id'          => $attribute_id,
					'attribute_value_label' => $option->label,
					'value'                 => $option->value
				] );
			}
		}
	}

	protected function verify_relationship() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'attribute_label' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT value,attribute_value_label FROM ${table} WHERE attribute_id = %d", $this->result->attribute_id ) );

		$existing_labels = array_reduce( $results, function ( $carry, $result ) {
			$carry[ $result->value ] = $result->attribute_value_label;
			return $carry;
		}, [] );

		$new_options = array_reduce( $this->result->options, function ( $carry, $option ) {

			if ( ! $option->value || ! $option->label ) {
				return $carry;
			}

			$carry[ $option->value ] = $option->label;
			return $carry;
		}, [] );

		return 0 === count( array_merge( array_diff( $new_options, $existing_labels ), array_diff( $existing_labels, $new_options ) ) );
	}

}
