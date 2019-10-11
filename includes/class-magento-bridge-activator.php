<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Magento_Bridge
 * @subpackage Magento_Bridge/includes
 */

use Magento_Bridge\Processor\Product_Update;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Magento_Bridge
 * @subpackage Magento_Bridge/includes
 * @author     Your Name <email@example.com>
 */
class Magento_Bridge_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::install_tables();
		self::schedule_cron();
	}

	protected static function schedule_cron() {
		if ( ! wp_next_scheduled( '\Magento_Bridge\Processor\Product_Update\Product_Update::run' ) ) {
			wp_schedule_event( time(), 'hourly', '\Magento_Bridge\Processor\Product_Update\Product_Update::run' );
		}
	}

	protected static function install_tables() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$sql = [];

		$product_table_name = \Magento_Bridge::get_table_name( 'products' );

		$charset_collate = $wpdb->get_charset_collate();

		$sql[] = "CREATE TABLE $product_table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  sku varchar(50) NOT NULL,
  mage_id smallint(4) UNSIGNED NOT NULL,
  name tinytext NOT NULL,
  price FLOAT NOT NULL,
  special_price FLOAT,
  type ENUM ('simple','configurable','grouped'),
  related text,
  main_photo_url text,
  additional_photos text,
  cache_time int(10) UNSIGNED,
  PRIMARY KEY  (id),
  UNIQUE KEY skey (sku),
  UNIQUE KEY mkey (mage_id)
) $charset_collate;";

		$configurable_children_name = \Magento_Bridge::get_table_name( 'configurable_children' );

		$sql[] = "CREATE TABLE $configurable_children_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  parent_id smallint(4) UNSIGNED NOT NULL,
  child_id smallint(4) UNSIGNED NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY parent_child (parent_id,child_id)
) $charset_collate;";

		$configurable_attributes_table_name = \Magento_Bridge::get_table_name( 'configurable_attributes' );

		$sql[] = "CREATE TABLE $configurable_attributes_table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  product_id smallint(4) UNSIGNED NOT NULL,
  attribute_code tinytext,
  attribute_id smallint(4) UNSIGNED NOT NULL,
  attribute_label text,
  PRIMARY KEY  (id),
  UNIQUE KEY product_att_key (product_id,attribute_id)
) $charset_collate;";


		$attribute_label_table = \Magento_Bridge::get_table_name( 'attribute_label' );

		$sql[] = "CREATE TABLE $attribute_label_table (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  attribute_id smallint(4) UNSIGNED NOT NULL,
  value varchar(200),
  attribute_value_label text,
  PRIMARY KEY  (id),
  KEY attribute_id_key (attribute_id),
  KEY attribute_value (value)
) $charset_collate;";

		$attribute_label_table = \Magento_Bridge::get_table_name( 'child_attributes' );

		$sql[] = "CREATE TABLE $attribute_label_table (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  product_id smallint(4) UNSIGNED NOT NULL,
  attribute_code tinytext,
  value tinytext,
  PRIMARY KEY  (id),
  KEY product_id_key (product_id)
) $charset_collate;";

		$related_products_table = \Magento_Bridge::get_table_name( 'related_products' );

		$sql[] = "CREATE TABLE $related_products_table (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  parent_id smallint(4) UNSIGNED NOT NULL,
  related_id smallint(4) UNSIGNED NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY related_relationship (parent_id,related_id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
