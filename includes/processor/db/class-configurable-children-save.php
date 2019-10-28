<?php
/**
 * Product Saver
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;


class Configurable_Children_Save {

	use Magento_Db_Helpers_Trait;

	protected $result;

	protected $parent_id;

	public function __construct( $result, $parent_id ) {
		$this->parent_id = $parent_id;
		$this->result    = is_string( $result ) ? json_decode( $result ) : $result;
	}

	public function save() {

		$parent_price_updated = false;

		$children_ids = [];

		foreach ( $this->result ?? [] as $result ) {
			$product_save = new Product_Save( $result );
			$success      = $product_save->save();
			if ( $success ) {
				$children_ids[]          = $result->id;
				$configurable_attributes = $product_save->get_configurable_attribute_as_simple( $this->parent_id );
				foreach ( $configurable_attributes as $attribute ) {
					$child_attribute_save = new Child_Attribute_Save( $attribute, $result );
					$child_attribute_save->save();
				}

				/** Updates Parent price to first child configurable. */
				if ( ! $parent_price_updated ) {
					$this->update_parent_price( $result->price, $product_save->get_special_price_from_attributes( $result ) );
					$parent_price_updated = true;
				}
			}
		}

		if ( $children_ids ) {
			$relationship_save = new Configurable_Relationship_Save( $children_ids, $this->parent_id );
			$relationship_save->save();
		}
	}

	public function update_parent_price( $price, $special_price ) {
		global $wpdb;

		$products_table = \Magento_Bridge::get_table_name( 'products' );

		$wpdb->update(
			$products_table,
			[
				'price'         => $price,
				'special_price' => $special_price
			],
			[ 'mage_id' => $this->parent_id ]
		);
	}

	public function delete_records() {
		//Todo
	}

}
