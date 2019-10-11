<?php
/**
 * Saves Relationship Between Parent and Child
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

class Child_Attribute_Save {

	protected $attribute_code;

	protected $result;

	/**
	 * Child_Attribute_Save constructor.
	 *
	 * @param $attribute_code
	 * @param $result
	 */
	public function __construct( $attribute_code, $result ) {
		$this->attribute_code = $attribute_code;
		$this->result         = $result;
	}


	public function save() {
		global $wpdb;

		if ( ! $this->result || ! $this->get_attribute_value() ) {
			return;
		}

		$attribute_table = \Magento_Bridge::get_table_name( 'child_attributes' );

		$existing_record = $this->get_existing_record();

		$new_value = $this->get_attribute_value();

		if ( $existing_record ) {
			if ( (int) $existing_record->value === (int) $new_value ) {
				return;
			}
			$wpdb->update(
				$attribute_table,
				[
					'value' => $new_value
				],
				[
					'product_id'     => $this->result->id,
					'attribute_code' => $this->attribute_code
				],
				[ '%d' ]
			);
			return;
		}

		$wpdb->insert(
			$attribute_table,
			[
				'product_id'     => $this->result->id,
				'attribute_code' => $this->attribute_code,
				'value'          => $new_value
			],
			$this->get_format()
		);
	}

	public function get_format() {
		return [
			'%d', '%s', '%d'
		];
	}

	public function get_existing_record() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'child_attributes' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * from ${table} WHERE product_id = '%d' AND attribute_code = '%s'",
				$this->result->id ?? 0,
				$this->attribute_code
			)
		);
	}

	protected function get_attribute_value() {

		$attribute_code = $this->attribute_code;

		return array_reduce( $this->result->custom_attributes ?? [], function ( $value, $attribute ) use ( $attribute_code ) {

			if ( $value ) {
				return $value;
			}

			if ( $attribute->attribute_code === $attribute_code ) {
				return $attribute->value;
			}
			return false;

		}, false );
	}
}
