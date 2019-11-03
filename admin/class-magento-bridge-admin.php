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

use Magento_Bridge\Processor\Product_Update;

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

		setcookie( 'magento_bridge_message', '', time() - 1 );
		setcookie( 'magento_bridge_message_status', '', time() - 1 );
	}

	public static function action_clear_fetch_products() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'magento_bridge_clear_cache' ) || ! current_user_can( 'manage_options' ) ) {
			die( 'Nonce Verification failed. Please try again' );
		}

		try {
			Product_Update::run( true );
			setcookie( 'magento_bridge_message', 'Cache Cleared and New Products Fetched' );
			setcookie( 'magento_bridge_message_status', 'success' );
		} catch ( Exception $e ) {
			setcookie( 'magento_bridge_message', $e->getMessage() );
			setcookie( 'magento_bridge_message_status', 'error' );
		}

		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}

	public static function admin_fetch_products_status() {

		if ( isset( $_COOKIE['magento_bridge_message'] ) ) {
			?>
			<div class="notice notice-<?php echo esc_attr( $_COOKIE['magento_bridge_message_status'] ); ?>">
				<p><?php echo esc_html( $_COOKIE['magento_bridge_message'] ); ?></p>
			</div>

			<?php
		}
		?>
		<?php
	}

	public static function display_settings() {
		add_action( 'admin_notices', [ self::class, 'admin_fetch_products_status' ] );
		?>
		<div>
			<?php do_action( 'admin_notices' ); ?>
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
							<p><code>MAGENTO_API_URL</code> is set in your <code>wp-config.php</code> and therefore,
								that value will be used.</p>
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
							<p><code>MAGENTO_API_AUTH</code> is set in your <code>wp-config.php</code> and therefore,
								that value will be used.</p>
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

			<hr/>

			<h2>Clear Product Cache and Re-Fetch</h2>
			<p>By default, the Cron will featch products using the Magento API once per hour. However, if a product
				update is urgent, you may click the button below to immediately re-fetch all product data.</p>
			<form action="<?php echo admin_url( 'admin.php' ); ?>" method="post">
				<input type="hidden" value="1" name="magento_bridge_clear_cache"/>
				<input type="hidden" name="action" value="magento_bridge_clear_fetch"/>
				<?php wp_nonce_field( 'magento_bridge_clear_cache' ); ?>
				<?php submit_button( 'Clear Cache and Re-Fetch Data' ); ?>
			</form>
		</div>
		<?php
	}
}
