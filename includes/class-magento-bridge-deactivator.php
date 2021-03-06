<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Magento_Bridge
 * @subpackage Magento_Bridge/includes
 */

use Magento_Bridge\Processor\Product_Update;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Magento_Bridge
 * @subpackage Magento_Bridge/includes
 * @author     Your Name <email@example.com>
 */
class Magento_Bridge_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::deactivate_cron();
	}

	public static function deactivate_cron() {
		wp_clear_scheduled_hook( 'magento_bridge_product_update' );
	}
}
