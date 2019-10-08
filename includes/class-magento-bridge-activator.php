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
	}

	protected static function install_tables() {

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = $wpdb->prefix . Magento_Bridge::BRIDGE_TABLE;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
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
  UNIQUE KEY skey (sku)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
