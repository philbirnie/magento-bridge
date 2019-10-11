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

class Magento_Product_Attribute extends Connector_Magento_API_Abstract {

	protected $request = '/products/attributes/%d';

	public function __construct( $attribute_id ) {
		$this->set_request( $this->request, $attribute_id );
	}

	final public function set_request_type( $type ) {
		$this->request_type = 'GET';
	}
}
