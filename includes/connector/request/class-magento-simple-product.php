<?php
/**
 * Fetches Simple Product Data from Magento
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Connector\Request;

use Magento_Bridge\Connector\Connector_Magento_API_Abstract;

class Magento_Simple_Product extends Connector_Magento_API_Abstract {

	protected $request = '/products/%s';

	public function __construct( $sku ) {
		$this->set_request( $this->request, $sku );
	}

	final public function set_request_type( $type ) {
		$this->request_type = 'GET';
	}
}
