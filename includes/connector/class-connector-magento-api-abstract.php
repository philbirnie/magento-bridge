<?php
/**
 * Magento Conector Abstract
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Connector;

abstract class Connector_Magento_API_Abstract implements Connector_Interface {

	const REQUEST_BASE = '/rest/default/V1';

	protected $request_type = 'GET';

	protected $params = [];

	protected $request = '';

	/**
	 * Return URL for Magento Installation.
	 *
	 * @return string
	 * @throws \Exception If url is not set
	 */
	public static function get_url(): string {
		if ( defined( 'MAGENTO_API_URL' ) ) {
			return MAGENTO_API_URL;
		}

		$url = get_option( 'magento_api_url' );

		if ( ! $url ) {
			throw new \Exception( 'Magento URL is missing; please set in Plugin Options or define MAGENTO_API_URL in your config file' );
		}

		return $url;
	}

	protected function get_auth(): string {
		if ( defined( 'MAGENTO_API_AUTH' ) ) {
			return MAGENTO_API_AUTH;
		}

		$url = get_option( 'magento_api_auth' );

		if ( ! $url ) {
			throw new \Exception( 'Magento Auth is missing; please set in Plugin Options or define MAGENTO_API_AUTH in your config file' );
		}

		return $url;
	}

	/**
	 * Send Response
	 *
	 * @return array|\WP_Error
	 * @throws \Exception
	 */
	public function send_request() {

		$request = self::get_url() . self::REQUEST_BASE . $this->get_request();

		/** @var array|\WP_Error $response */
		$response = wp_remote_request(
			$request,
			[
				'method'  => $this->get_request_type(),
				'headers' => [
					sprintf( 'Authorization: Bearer %s', $this->get_auth() )
				],
				'body'    => $this->get_params(),
			]
		);

		if ( ! is_array( $response ) && 'WP_Error' === get_class( $response ) ) {
			throw new \Exception( sprintf( 'Magento API Request Failure. %s', $response->get_error_message() ) );
		}

		if ( 200 != $response['response']['code'] ?? 0 ) {
			throw new \Exception( sprintf( 'Magento API Request Failure. %d, %s', $response['response']['code'] ?? 0, static::class ) );
		}

		return $this->response_post_process( $response['body'] ?? '' );
	}

	/**
	 * Set Request (with optional URL parameters)
	 *
	 * @param       $request
	 * @param mixed ...$args
	 */
	public function set_request( $request, ...$args ) {
		$this->request = call_user_func_array( 'sprintf', array_merge( [ $request ], $args ) );
	}

	/**
	 * Get Request
	 *
	 * @return string
	 */
	public function get_request(): string {
		return $this->request;
	}

	/**
	 * Set Body Request Parameters.
	 *
	 * @param array $params
	 */
	public function set_params( array $params = [] ) {
		$this->params = $params;
	}

	/**
	 * Get Body Parameters.
	 *
	 * @return array
	 */
	public function get_params(): array {
		return $this->params;
	}

	/**
	 * Get Request Type
	 *
	 * @return string
	 */
	public function get_request_type() {
		return $this->request_type;
	}

	/**
	 * Set Request Type
	 *
	 * @param $type
	 *
	 */
	public function set_request_type( $type ) {
		$type = strtoupper( $type );

		switch ( $type ) {
			case 'GET':
			case 'PUT':
			case 'POST':
			case 'DEL':
				$this->request_type = $type;
				break;
			default:
				$this->request_type = 'GET';
		}
	}

	/**
	 * Optional Function to utilize for post-processing if necessary
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	protected function response_post_process( $response ) {
		return $response;
	}
}
