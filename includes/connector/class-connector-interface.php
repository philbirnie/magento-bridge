<?php
/**
 * Connector Interface
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Connector;

interface Connector_Interface {

	/**
	 * Sends Request to service
	 *
	 * @return mixed
	 */
	public function send_request();
}
