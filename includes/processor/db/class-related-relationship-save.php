<?php
/**
 * Saves Relationship Between Parent and Child
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

class Related_Relationship_Save {

	protected $related_ids = [];

	protected $parent_id;

	/**
	 * Configurable_Relationship_Data_Save constructor.
	 *
	 * @param array $related_ids
	 * @param int   $parent_id
	 */
	public function __construct( array $related_ids, $parent_id ) {
		$this->related_ids = $related_ids;
		$this->parent_id   = $parent_id;
	}

	public function save() {
		global $wpdb;

		if ( $this->verify_relationship() ) {
			return;
		}

		/** Delete Existing Records */
		$wpdb->delete( \Magento_Bridge::get_table_name( 'related_products' ), [ 'parent_id' => $this->parent_id ] );

		foreach ( $this->related_ids as $child_id ) {
			$wpdb->insert(
				\Magento_Bridge::get_table_name( 'related_products' ),
				[
					'parent_id' => $this->parent_id,
					'related_id'  => $child_id
				]
			);
		}

	}

	protected function verify_relationship() {
		global $wpdb;

		$table = \Magento_Bridge::get_table_name( 'related_products' );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT related_id FROM ${table} WHERE parent_id = %d", $this->parent_id ), ARRAY_N );

		$existing_ids = array_reduce( $results, function ( $carry, $result ) {
			$carry[] = (int) $result[0];
			return $carry;
		}, [] );

		return 0 === count( array_merge( array_diff( $existing_ids, $this->related_ids ), array_diff( $this->related_ids, $existing_ids ) ) );
	}
}
