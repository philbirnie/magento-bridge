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

	/** @var string */
	public $amazon_url = '';

	/** @var float */
	public $amazon_price = 0.00;

	/** @var array */
	public $additional_photos = [];

	/** @var array of Products */
	public $children = [];

	/** @var array */
	public $configurable_attributes = [];

	/** @var array */
	public $attributes = [];

	/** @var int */
	public $cache_time = 0;

	/** @var string url */
	public $url = '';

	/** @var description */
	public $description;
}
