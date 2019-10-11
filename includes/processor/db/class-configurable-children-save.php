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

		$children_ids = [];

		foreach ( $this->result ?? [] as $result ) {
			$product_save = new Product_Save( $result );
			$success      = $product_save->save();
			if ( $success ) {
				$children_ids[] = $result->id;
				$configurable_attributes = $product_save->get_configurable_attribute_as_simple($this->parent_id);
				foreach($configurable_attributes as $attribute) {
					$child_attribute_save = new Child_Attribute_Save($attribute, $result);
					$child_attribute_save->save();
				}
			}
		}

		if ( $children_ids ) {
			$relationship_save = new Configurable_Relationship_Save( $children_ids, $this->parent_id );
			$relationship_save->save();
		}

	}

	public function delete_records() {
		//Todo
	}

}
