<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://7thstreetweb.com
 * @since             0.0.1
 * @package           Magento_Bridge
 *
 * @wordpress-plugin
 * Plugin Name:       Magento Bridge
 * Plugin URI:        http://7thstreetweb.com/
 * Description:       Bridge to bring over product data from Magento
 * Version:           0.0.1
 * Author:            7th Street Web
 * Author URI:        http://7thstreetweb.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       magento-bridge
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '0.0.1' );

/** Table Constants */


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_magento_bridge() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-magento-bridge-activator.php';
	Magento_Bridge_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_magento_bridge() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-magento-bridge-deactivator.php';
	Magento_Bridge_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_magento_bridge' );
register_deactivation_hook( __FILE__, 'deactivate_magento_bridge' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-magento-bridge.php';
require plugin_dir_path( __FILE__ ) . 'includes/autoload.php';

add_filter( 'http_request_host_is_external', '__return_true' );

/** If WP_DEBUG, we're working locally and there is not a need to validate the SSL. */
if ( WP_DEBUG ) {
	add_filter( 'https_local_ssl_verify', '__return_false' );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Magento_Bridge();
	$plugin->run();

}
run_plugin_name();
