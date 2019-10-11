<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Magento_Bridge
 * @subpackage Magento_Bridge/admin
 * @author     Phil Birnie <phil@7thstreetweb.com>
 */
class Magento_Bridge_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public static function register_settings() {
		add_option( 'magento_api_url', '' );
		add_option( 'magento_api_auth', '' );

		register_setting( 'magento_bridge_options_group', 'magento_api_url', [] );
		register_setting( 'magento_bridge_options_group', 'magento_api_auth', [] );
	}

	public static function register_option_page() {
		add_options_page(
			__( 'Magento Connector', 'magento-bridge' ),
			__( 'Magento Connector', 'magento-bridge' ),
			'manage_options',
			'magento_bridge_options',
			[ self::class, 'display_settings' ]
		);
	}

	public static function display_settings() {
		?>
		<div>
			<?php screen_icon(); ?>
			<h2>Magento Connection Settings</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'magento_bridge_options_group' ); ?>
				<table>
					<tr valign="top">
						<th scope="row"><label
									for="magento_api_url"><?php esc_html_e( 'Magento URL', 'magento-bridge' ); ?></label>
						</th>
						<?php
						if ( defined( 'MAGENTO_API_URL' ) ) {
							?>
							<p><code>MAGENTO_API_URL</code> is set in your <code>wp-config.php</code> and therefore, that value will be used.</p>
							<?php
						} else {
							?>
							<td><input required="required" type="text" id="magento_api_url" name="magento_api_url"
										value="<?php echo get_option( 'magento_api_url' ); ?>"/></td>

							<?php
						}
						?>
					</tr>
					<tr valign="top">
						<th scope="row"><label
									for="magento_api_auth"><?php esc_html_e( 'Magento Auth', 'magento-bridge' ); ?></label>
						</th>
						<?php
						if ( defined( 'MAGENTO_API_AUTH' ) ) {
							?>
							<p><code>MAGENTO_API_AUTH</code> is set in your <code>wp-config.php</code> and therefore, that value will be used.</p>
							<?php
						} else {
							?>
							<td><input required="required" type="text" id="magento_api_auth" name="magento_api_auth"
										value="<?php echo get_option( 'magento_api_auth' ); ?>"/></td>
							<?php
						}
						?>
					</tr>

				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
