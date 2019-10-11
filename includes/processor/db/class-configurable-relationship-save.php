<?php
/**
 * Saves Relationship Between Parent and Child
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

class Configurable_Relationship_Save {

	protected $children_ids = [];

	protected $parent_id;

	/**
	 * Configurable_Relationship_Data_Save constructor.
	 *
	 * @param array $children_ids
	 * @param int   $parent_id
	 */
	public function __construct( array $children_ids, $parent_id ) {
		$this->children_ids = $children_ids;
		$this->parent_id    = $parent_id;
	}

	public function save() {
		global $wpdb;

		if ( $this->verify_relationship() ) {
			return;
		}

		/** Delete Existing Records */
		$wpdb->delete( \Magento_Bridge::get_table_name( 'configurable_children' ), [ 'parent_id' => $this->parent_id ] );

		foreach ( $this->children_ids as $child_id ) {
			$wpdb->insert(
				\Magento_Bridge::get_table_name( 'configurable_children' ),
				[
					'parent_id' => $this->parent_id,
					'child_id'  => $child_id
				]
			);
		}

	}

	protected function verify_relationship() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'configurable_children' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT child_id FROM ${table} WHERE parent_id = %d", $this->parent_id ), ARRAY_N );

		$existing_ids = array_reduce( $results, function ( $carry, $result ) {
			$carry[] = (int) $result[0];
			return $carry;
		}, [] );

		return 0 === count( array_merge( array_diff( $existing_ids, $this->children_ids ), array_diff( $this->children_ids, $existing_ids ) ) );
	}
}
