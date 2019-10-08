<?php
/**
 * Product Object
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Product;


class Product {

	/** @var string */
	public $sku = '';

	/** @var int */
	public $mage_id = 0;

	/** @var string */
	public $name = '';

	/** @var float */
	public $price = 0.00;

	/** @var null|float */
	public $special_price;

	/** @var string */
	public $type = 'simple';

	/** @var array */
	public $related = [];

	/** @var string */
	public $main_photo_url = '';

	/** @var array */
	public $additional_photos = [];

	/** @var int */
	public $cache_time = 0;
}
