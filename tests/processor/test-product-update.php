<?php

use Magento_Bridge\Adapters\Product_Adapter_Wordpress;
use Magento_Bridge\Connector\Request\Magento_Configurable_Children;
use Magento_Bridge\Connector\Request\Magento_Product_Attribute;
use Magento_Bridge\Connector\Request\Magento_Simple_Product;
use Magento_Bridge\Processor\Product_Update;
use Magento_Bridge\Product\Product;

/**
 * Product Update Test
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */
class ProductUpdateTest extends WP_UnitTestCase {

	protected $configurable_connector;

	protected $product_attribute_connector;

	protected $simple_product_connector;

	protected $configurable_with_related_connector;

	public function setUp() {
		parent::setUp(); // TODO: Change the autogenerated stub
		$custom_product = new WP_UnitTest_Factory_For_Post();
		$custom_product->create(
			[
				'post_type'  => 'product',
				'meta_input' => [
					'product_sku' => 'tracer360'
				]
			]
		);
		$custom_product->create(
			[
				'post_type'  => 'product',
				'meta_input' => [
					'product_sku' => '39g'
				]
			]
		);
		$custom_product->create(
			[
				'post_type'  => 'post',
				'meta_input' => [
					'product_sku' => '39goat'
				]
			]
		);

		$custom_product->create(
			[
				'post_type'  => 'product',
				'meta_input' => [
					'product_sku' => 'new-product'
				]
			]
		);

		$custom_product->create(
			[
				'post_type'  => 'product',
				'meta_input' => [
					'product_sku' => 'some_dumb_product'
				]
			]
		);

		$this->configurable_connector = $this->createMock( Magento_Configurable_Children::class );
		$this->configurable_connector->method( 'send_request' )
			->willReturn('[{"id":50,"sku":"tracer360-S","name":"Tracer 360-S","attribute_set_id":9,"price":69.95,"status":1,"visibility":1,"type_id":"simple","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-02 13:51:37","weight":0.9,"product_links":[],"tier_prices":[],"custom_attributes":[{"attribute_code":"required_options","value":"0"},{"attribute_code":"nox_sales_price","value":"49.9500"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["2","3"]},{"attribute_code":"nox_tracer_size","value":"4"},{"attribute_code":"short_description","value":"<p>The Tracer360 visibility vest keeps you safe and out of harm\'s way while you\'re active by making you highly visible to cars and traffic under any conditions.<\/p>"},{"attribute_code":"image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"small_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"thumbnail","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"swatch_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"url_key","value":"tracer-360-s"},{"attribute_code":"msrp_display_actual_price_type","value":"0"}]},{"id":51,"sku":"tracer360-M\/L","name":"Tracer 360-M\/L","attribute_set_id":9,"price":69.95,"status":1,"visibility":1,"type_id":"simple","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-02 13:51:37","weight":0.9063,"product_links":[],"tier_prices":[],"custom_attributes":[{"attribute_code":"required_options","value":"0"},{"attribute_code":"special_price","value":"49.9500"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["2","3"]},{"attribute_code":"nox_tracer_size","value":"5"},{"attribute_code":"short_description","value":"<p>The Tracer360 is a high visibility vest that takes you where a reflective vest can\'t. With 360 degrees of full color spectrum illumination paired with 3M reflectivity, the Tracer360 perfect for runners and cyclists.<\/p>"},{"attribute_code":"image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"small_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"thumbnail","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"swatch_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"url_key","value":"tracer-360-m-l"},{"attribute_code":"msrp_display_actual_price_type","value":"0"}]},{"id":52,"sku":"tracer360-XL","name":"Tracer 360-XL","attribute_set_id":9,"price":69.95,"status":1,"visibility":1,"type_id":"simple","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-02 13:51:37","weight":0.9313,"product_links":[],"tier_prices":[],"custom_attributes":[{"attribute_code":"required_options","value":"0"},{"attribute_code":"special_price","value":"49.9500"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["2","3"]},{"attribute_code":"nox_tracer_size","value":"6"},{"attribute_code":"short_description","value":"<p>The Tracer360 is a high visibility vest.<\/p>"},{"attribute_code":"image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"small_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"thumbnail","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"swatch_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"url_key","value":"tracer-360-xl"},{"attribute_code":"msrp_display_actual_price_type","value":"0"}]}]' );

		$this->product_attribute_connector = $this->createMock( Magento_Product_Attribute::class );
		$this->product_attribute_connector->method( 'send_request' )
			->willReturn( '{"is_wysiwyg_enabled":false,"is_html_allowed_on_front":true,"used_for_sort_by":false,"is_filterable":false,"is_filterable_in_search":false,"is_used_in_grid":true,"is_visible_in_grid":true,"is_filterable_in_grid":false,"position":0,"apply_to":[],"is_searchable":"0","is_visible_in_advanced_search":"0","is_comparable":"0","is_used_for_promo_rules":"0","is_visible_on_front":"0","used_in_product_listing":"0","is_visible":true,"scope":"global","attribute_id":138,"attribute_code":"nox_tracer_size","frontend_input":"select","entity_type_id":"4","is_required":true,"options":[{"label":" ","value":""},{"label":"S","value":"4"},{"label":"M\/L","value":"5"},{"label":"XL","value":"6"}],"is_user_defined":true,"default_frontend_label":"Size","frontend_labels":[{"store_id":1,"label":"Size"}],"backend_type":"int","default_value":"","is_unique":"0","validation_rules":[]}' );

		$this->simple_product_connector = $this->createMock( Magento_Simple_Product::class );
		$this->simple_product_connector->method( 'send_request' )
			->willReturn( '{"id":53,"sku":"tracer360","name":"Tracer 360","attribute_set_id":9,"price":0,"status":1,"visibility":4,"type_id":"configurable","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-18 14:52:29","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"2"},{"position":5,"category_id":"5"}],"configurable_product_options":[{"id":14,"attribute_id":"138","label":"Size","position":0,"values":[{"value_index":4},{"value_index":5},{"value_index":6}],"product_id":53}],"configurable_product_links":[50,51,52],"configurable_base":69.95},"product_links":[],"options":[],"media_gallery_entries":[{"id":239,"media_type":"image","label":null,"position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/s\/i\/size-chart-01-2x.jpg"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"description","value":"<p>The Tracer360 is a high visibility vest that takes you where a reflective vest can\'t. With 360 degrees of full color spectrum illumination paired with 3M reflectivity, the Tracer360 perfect for runners and cyclists.<\/p>"},{"attribute_code":"image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"nox_sales_price","value":"49.950000"},{"attribute_code":"url_key","value":"tracer360-visibility-safety-vest"},{"attribute_code":"special_price","value":"49.950000"},{"attribute_code":"gift_message_available","value":"0"},{"attribute_code":"short_description","value":"<p>Essential performance safety visibility vest<\/p>"},{"attribute_code":"small_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"nox_store_url","value":"tracer360-visibility-safety-vest"},{"attribute_code":"special_from_date","value":"2019-10-18 00:00:00"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"thumbnail","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"swatch_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code": "nox_amazon_url","value": "https://amzn.to/2ryaRp0"},{"attribute_code": "nox_amazon_price","value": "59.950000"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"required_options","value":"1"},{"attribute_code":"has_options","value":"1"},{"attribute_code":"category_ids","value":["2","5"]}]}' );

		$this->configurable_with_related_connector = $this->createMock( Magento_Simple_Product::class );
		$this->configurable_with_related_connector->method( 'send_request' )
			->will( $this->onConsecutiveCalls(
				'{"id":53,"sku":"tracer360","name":"Tracer 360","attribute_set_id":9,"price":0,"status":1,"visibility":4,"type_id":"configurable","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-02 13:51:37","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"2"},{"position":0,"category_id":"3"}],"configurable_product_options":[{"id":14,"attribute_id":"138","label":"Size","position":0,"values":[{"value_index":4},{"value_index":5},{"value_index":6}],"product_id":53}],"configurable_product_links":[50,51,52]},"product_links":[{"sku":"tracer360","link_type":"related","linked_product_sku":"shirt1","linked_product_type":"configurable","position":0},{"sku":"tracer360","link_type":"related","linked_product_sku":"lighthound","linked_product_type":"configurable","position":0},{"sku":"tracer360","link_type":"related","linked_product_sku":"reflect","linked_product_type":"simple","position":0}],"options":[],"media_gallery_entries":[{"id":178,"media_type":"image","label":"","position":1,"disabled":false,"types":[],"file":"\/s\/i\/size-chart-01_2x_1.jpg"},{"id":239,"media_type":"image","label":"","position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/s\/i\/size-chart-01-2x.jpg"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"small_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"thumbnail","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code": "nox_amazon_url","value": "https://amzn.to/2ryaRp0"},{"attribute_code": "nox_amazon_price","value": "59.950000"},{"attribute_code":"swatch_image","value":"\/s\/i\/size-chart-01-2x.jpg"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"url_key","value":"tracer360-visibility-safety-vest"},{"attribute_code":"required_options","value":"0"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["2","3"]},{"attribute_code":"description","value":"<p>The Tracer360 is a high visibility vest that takes you where a reflective vest can\'t. With 360 degrees of full color spectrum illumination paired with 3M reflectivity, the Tracer360 perfect for runners and cyclists.<\/p>"},{"attribute_code":"short_description","value":"<p>The Tracer360 is a high visibility vest that takes you where a reflective vest can\'t. With 360 degrees of full color spectrum illumination paired with 3M reflectivity, the Tracer360 perfect for runners and cyclists.<\/p>"}]}',
				'{"id":67,"sku":"shirt1","name":"4:46 A.M. Runner","attribute_set_id":11,"price":0,"status":1,"visibility":4,"type_id":"configurable","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-18 17:20:06","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"2"},{"position":0,"category_id":"4"},{"position":20,"category_id":"5"}],"configurable_product_options":[{"id":23,"attribute_id":"140","label":"Shirt Size","position":0,"values":[{"value_index":11},{"value_index":12},{"value_index":13},{"value_index":14},{"value_index":15},{"value_index":16}],"product_id":67}],"configurable_product_links":[61,62,63,64,65,66],"configurable_base":35},"product_links":[{"sku":"shirt1","link_type":"related","linked_product_sku":"shirt4","linked_product_type":"configurable","position":0},{"sku":"shirt1","link_type":"related","linked_product_sku":"lighthound","linked_product_type":"configurable","position":0},{"sku":"shirt1","link_type":"related","linked_product_sku":"tracer360","linked_product_type":"configurable","position":1}],"options":[],"media_gallery_entries":[{"id":192,"media_type":"image","label":null,"position":1,"disabled":false,"types":[],"file":"\/4\/4\/446am-runner_6.jpg"},{"id":228,"media_type":"image","label":null,"position":1,"disabled":false,"types":[],"file":"\/4\/4\/446am-runner.jpg"},{"id":253,"media_type":"image","label":null,"position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/4\/4\/446am-runner_1.jpg"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"description","value":"<p><span style=\"font-weight: 400;\">Let your pride for running at the darkest time of the day shine throughout. Rock this comfy t-shirt and you\u2019ll be beaming before the sun is up.<\/span><\/p>"},{"attribute_code":"image","value":"\/4\/4\/446am-runner_1.jpg"},{"attribute_code":"url_key","value":"shirt-446am-runner"},{"attribute_code":"gift_message_available","value":"0"},{"attribute_code":"short_description","value":"<p>Shine your light at the darkest time of\u00a0day<\/p>"},{"attribute_code":"small_image","value":"\/4\/4\/446am-runner_1.jpg"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"nox_product_features","value":"<ul>\r\n<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">100% cotton<\/span><\/li>\r\n<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Standard Fit<\/span><\/li>\r\n<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Unisex Style<\/span><\/li>\r\n<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Soft Screenprint<\/span><\/li>\r\n<li style=\"font-weight: 400;\"><span style=\"font-weight: 400;\">Crewneck t-shirt<\/span><\/li>\r\n<\/ul>"},{"attribute_code":"thumbnail","value":"\/4\/4\/446am-runner_1.jpg"},{"attribute_code":"swatch_image","value":"\/4\/4\/446am-runner_1.jpg"},{"attribute_code":"meta_description","value":"Shirt: 446 A.M. Runner  - Noxgear advanced visibility and safety gear"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"required_options","value":"1"},{"attribute_code":"has_options","value":"1"},{"attribute_code":"category_ids","value":["2","4","5"]}]}',
				'{"id":58,"sku":"lighthound","name":"LightHound","attribute_set_id":10,"price":0,"status":1,"visibility":4,"type_id":"configurable","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-15 12:58:57","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"2"},{"position":10,"category_id":"5"}],"configurable_product_options":[{"id":20,"attribute_id":"139","label":"Vest Size","position":0,"values":[{"value_index":7},{"value_index":8},{"value_index":9},{"value_index":10}],"product_id":58}],"configurable_product_links":[54,55,56,57],"configurable_base":69.95},"product_links":[{"sku":"lighthound","link_type":"related","linked_product_sku":"shirt3","linked_product_type":"configurable","position":0},{"sku":"lighthound","link_type":"related","linked_product_sku":"tracer360","linked_product_type":"configurable","position":1}],"options":[],"media_gallery_entries":[{"id":183,"media_type":"image","label":null,"position":1,"disabled":false,"types":[],"file":"\/l\/i\/lighthound_7.jpg"},{"id":219,"media_type":"image","label":null,"position":1,"disabled":false,"types":[],"file":"\/l\/i\/lighthound.jpg"},{"id":244,"media_type":"image","label":null,"position":1,"disabled":false,"types":["image","small_image","thumbnail"],"file":"\/l\/i\/lighthound_1.jpg"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"description","value":"<p>The LightHound LED dog harness allows you and your pup to experience more together with the peace-of-mind knowing that your best friend is safe and highly visible at night and in low visibility conditions.<\/p>"},{"attribute_code":"image","value":"\/l\/i\/lighthound_1.jpg"},{"attribute_code":"url_key","value":"lighthound-illuminated-led-dog-harness"},{"attribute_code":"gift_message_available","value":"0"},{"attribute_code":"short_description","value":"<p>Peace-of-mind for pups &amp; their owners<\/p>"},{"attribute_code":"small_image","value":"\/l\/i\/lighthound_1.jpg"},{"attribute_code":"nox_store_url","value":"lighthound-illuminated-led-dog-harness"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"thumbnail","value":"\/l\/i\/lighthound_1.jpg"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"required_options","value":"1"},{"attribute_code":"has_options","value":"1"},{"attribute_code":"category_ids","value":["2","5"]}]}',
				'{"id":89,"sku":"reflect","name":"Shoulder Reflectors","attribute_set_id":4,"price":19.95,"status":1,"visibility":4,"type_id":"simple","created_at":"2019-09-27 14:57:57","updated_at":"2019-10-02 13:51:37","weight":0.125,"extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"2"},{"position":0,"category_id":"4"},{"position":0,"category_id":"3"}]},"product_links":[{"sku":"reflect","link_type":"related","linked_product_sku":"tracer360","linked_product_type":"configurable","position":0}],"options":[],"media_gallery_entries":[{"id":214,"media_type":"image","label":"","position":1,"disabled":false,"types":[],"file":"\/s\/h\/shoulder-reflectors_3.jpg"},{"id":275,"media_type":"image","label":"","position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/s\/h\/shoulder-reflectors.jpg"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"image","value":"\/s\/h\/shoulder-reflectors.jpg"},{"attribute_code":"small_image","value":"\/s\/h\/shoulder-reflectors.jpg"},{"attribute_code":"thumbnail","value":"\/s\/h\/shoulder-reflectors.jpg"},{"attribute_code":"swatch_image","value":"\/s\/h\/shoulder-reflectors.jpg"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"url_key","value":"tracer-360-shoulder-reflectors"},{"attribute_code":"required_options","value":"0"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["2","4","3"]},{"attribute_code":"description","value":"<p>This pair of <strong>Shoulder Reflectors<\/strong> was specially designed for the Tracer360 to meet the reflective requirements of <em><strong>Ragnar<\/strong><\/em> and other <em><strong>Overnight Relay Races<\/strong><\/em>.<\/p>\n<p>The <strong>Shoulder Reflectors<\/strong> fuse 3M Scotchlite Reflectivity with High Visibility florescent sports fabric and provide reflectivity over the shoulders without blocking the illumination from our Fiber Optic! Attach them easily to your Tracer360<\/a> with three (3) pieces of Velcro. <\/p>"},{"attribute_code":"short_description","value":"<p>This pair of <strong>Shoulder Reflectors<\/strong> was specially designed for the Tracer360 to meet the reflective requirements of <em><strong>Ragnar<\/strong><\/em> and other <em><strong>Overnight Relay Races<\/strong><\/em>.<\/p>\n<p>The <strong>Shoulder Reflectors<\/strong> fuse 3M Scotchlite Reflectivity with High Visibility florescent sports fabric and provide reflectivity over the shoulders without blocking the illumination from our Fiber Optic! Attach them easily to your Tracer360<\/a> with three (3) pieces of Velcro. <\/p>"}]}'
			)
			);

		Product_Update::set_connector( 'configurable', $this->configurable_connector );
		Product_Update::set_connector( 'simple', $this->simple_product_connector );
		Product_Update::set_connector( 'product_attribute', $this->product_attribute_connector );


		//Insert some products
		$this->insert_products();
	}

	public function testShouldReturnOnlyProductPosts() {
		$product_skus = Product_Update::get_all_product_skus();

		$this->assertContains( '39g', $product_skus );
		$this->assertContains( 'tracer360', $product_skus );
		$this->assertNotContains( '39goat', $product_skus );
	}

	public function testShouldOnlyReturnNewProducts() {
		$product_skus = Product_Update::get_new_product_skus();

		$this->assertContains( 'new-product', $product_skus );
		$this->assertNotContains( 'tracer360', $product_skus );
	}

	public function testShouldReturnOnlyExpiredProducts() {
		$product_skus = Product_Update::get_expired_products();

		$this->assertEquals( [ '39g' ], $product_skus );
	}

	public function testShouldClearCache(  ) {
		global $wpdb;

		$product_skus = Product_Update::get_expired_products();
		$this->assertCount(1, $product_skus);

		//Run Cache Flush
		Product_Update::clear_cache();
		$expired_cache = Product_Update::get_expired_products();

		$this->assertCount(3, $expired_cache, 'Number of Expired products is equal to number of products');

	}

	public function testUpdateProductChangesAttributesOfProduct() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$table = \Magento_Bridge::get_table_name( 'products' );

		/** Set up Product and children */
		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'tracer360'" );

		$this->assertEquals( 53, $result->mage_id );
		$this->assertEquals( 'media/catalog/product/s/i/size-chart-01-2x.jpg', $result->main_photo_url );

	}


	public function testInsertAddsNewProduct() {
		global $wpdb;

		$connector = $this->createMock( Magento_Simple_Product::class );
		$connector->method( 'send_request' )
			->willReturn( '{"id":1,"sku":"phil-test-product","name":"Phil\'s Test Product","attribute_set_id":4,"price":15.23,"status":1,"visibility":4,"type_id":"simple","created_at":"2019-09-16 14:47:51","updated_at":"2019-09-25 18:16:35","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"3"}]},"product_links":[],"options":[],"media_gallery_entries":[{"id":1,"media_type":"image","label":null,"position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/s\/h\/shoe.png"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"image","value":"\/s\/h\/shoe.png"},{"attribute_code":"small_image","value":"\/s\/h\/shoe.png"},{"attribute_code":"nox_sales_price","value":"14.1000"},{"attribute_code":"thumbnail","value":"\/s\/h\/shoe.png"},{"attribute_code":"swatch_image","value":"\/s\/h\/shoe.png"},{"attribute_code":"special_from_date","value":"2019-09-12 00:00:00"},{"attribute_code":"special_to_date","value":"2019-09-21 00:00:00"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"url_key","value":"phil-s-test-product"},{"attribute_code":"gift_message_available","value":"2"},{"attribute_code":"required_options","value":"0"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"meta_title","value":"Phil\'s Test Product"},{"attribute_code":"meta_keyword","value":"Phil\'s Test Product"},{"attribute_code":"meta_description","value":"Phil\'s Test Product"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["3"]}]}' );

		Product_Update::set_connector( 'simple', $connector );
		Product_Update::update_product( 'phil-test-product' );

		$table = \Magento_Bridge::get_table_name( 'products' );


		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'phil-test-product'" );

		$this->assertEquals( 1, $result->mage_id );
		$this->assertEquals( 15.23, $result->price );
		$this->assertEquals( 14.1000, $result->special_price );
		$this->assertEquals( 'media/catalog/product/s/h/shoe.png', $result->main_photo_url );
	}

	public function test_inserts_url() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$table = \Magento_Bridge::get_table_name( 'products' );

		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'tracer360'" );

		$this->assertEquals( 'tracer360-visibility-safety-vest', $result->url );

		$connector = $this->createMock( Magento_Simple_Product::class );
		$connector->method( 'send_request' )
			->willReturn( '{"id":1,"sku":"phil-test-product","name":"Phil\'s Test Product","attribute_set_id":4,"price":15.23,"status":1,"visibility":4,"type_id":"simple","created_at":"2019-09-16 14:47:51","updated_at":"2019-09-25 18:16:35","extension_attributes":{"website_ids":[1],"category_links":[{"position":0,"category_id":"3"}]},"product_links":[],"options":[],"media_gallery_entries":[{"id":1,"media_type":"image","label":null,"position":1,"disabled":false,"types":["image","small_image","thumbnail","swatch_image"],"file":"\/s\/h\/shoe.png"}],"tier_prices":[],"custom_attributes":[{"attribute_code":"image","value":"\/s\/h\/shoe.png"},{"attribute_code":"small_image","value":"\/s\/h\/shoe.png"},{"attribute_code":"nox_sales_price","value":"14.1000"},{"attribute_code":"thumbnail","value":"\/s\/h\/shoe.png"},{"attribute_code":"swatch_image","value":"\/s\/h\/shoe.png"},{"attribute_code":"special_from_date","value":"2019-09-12 00:00:00"},{"attribute_code":"special_to_date","value":"2019-09-21 00:00:00"},{"attribute_code":"options_container","value":"container2"},{"attribute_code":"msrp_display_actual_price_type","value":"0"},{"attribute_code":"url_key","value":"phil-s-test-product"},{"attribute_code":"nox_store_url","value":"http:\/\/whatever.com\/phil-test-product"},{"attribute_code":"gift_message_available","value":"2"},{"attribute_code":"required_options","value":"0"},{"attribute_code":"has_options","value":"0"},{"attribute_code":"meta_title","value":"Phil\'s Test Product"},{"attribute_code":"meta_keyword","value":"Phil\'s Test Product"},{"attribute_code":"meta_description","value":"Phil\'s Test Product"},{"attribute_code":"tax_class_id","value":"2"},{"attribute_code":"category_ids","value":["3"]}]}' );

		Product_Update::set_connector( 'simple', $connector );
		Product_Update::update_product( 'phil-test-product' );
		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'phil-test-product'" );

		$this->assertEquals( 'http://whatever.com/phil-test-product', $result->url );
	}

	public function testAddsAndProcessesShortDescription() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$table = \Magento_Bridge::get_table_name( 'products' );

		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'tracer360'" );

		$this->assertContains( 'Essential performance safety visibility vest', $result->description );
	}

	public function testAddsAmazonURLAndPrice(  ) {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$table = \Magento_Bridge::get_table_name( 'products' );

		$result = $wpdb->get_row( "SELECT * from ${table} WHERE sku = 'tracer360'" );

		$this->assertEquals('https://amzn.to/2ryaRp0', $result->amazon_url );
		$this->assertEquals( 59.95, $result->amazon_price );
	}

	public function testAddsConfigurableAttributes() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$configurable_attributes_table = Magento_Bridge::get_table_name( 'configurable_attributes' );

		$result = $wpdb->get_col( "SELECT COUNT(*) from {$configurable_attributes_table} WHERE attribute_id = 138" );

		$this->assertEquals( 1, $result[0] );

		$result = $wpdb->get_row( "SELECT * from {$configurable_attributes_table} WHERE attribute_id = 138" );

		$this->assertEquals( 53, $result->product_id );
		$this->assertEquals( 'Size', $result->attribute_label );
	}

	public function testUpdatesAndInsertsProductAttributes() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$configurable_attributes_table = Magento_Bridge::get_table_name( 'configurable_attributes' );

		$result = $wpdb->get_row( "SELECT * from {$configurable_attributes_table} WHERE attribute_id = 138" );

		$this->assertEquals( 53, $result->product_id );
		$this->assertEquals( 'nox_tracer_size', $result->attribute_code );
		$this->assertEquals( 'Size', $result->attribute_label );

		$attributes_label_table = Magento_Bridge::get_table_name( 'attribute_label' );

		$result = $wpdb->get_col( "SELECT COUNT(*) from {$attributes_label_table} WHERE attribute_id = 138" );

		$this->assertEquals( 3, $result[0] );

		$result = $wpdb->get_row( "SELECT * from ${attributes_label_table} WHERE attribute_id = 138 AND value = 4" );
		$this->assertEquals( 'S', $result->attribute_value_label );

		$result = $wpdb->get_row( "SELECT * from ${attributes_label_table} WHERE attribute_id = 138 AND value = 5" );
		$this->assertEquals( 'M/L', $result->attribute_value_label );

		$result = $wpdb->get_row( "SELECT * from ${attributes_label_table} WHERE attribute_id = 138 AND value = 6" );
		$this->assertEquals( 'XL', $result->attribute_value_label );
	}

	public function test_inserts_related_products() {
		global $wpdb;

		Product_Update::$connectors['simple'] = $this->configurable_with_related_connector;
		Product_Update::update_product( 'tracer360' );

		$products = Magento_Bridge::get_table_name( 'products' );

		$lighthound = $wpdb->get_row( "SELECT * From ${products} WHERE sku = 'lighthound'" );

		$this->assertNotNull( $lighthound );
		$this->assertEquals( 58, $lighthound->mage_id );
		$this->assertEquals( 'media/catalog/product/l/i/lighthound_1.jpg', $lighthound->main_photo_url );

		$related_products_table = Magento_Bridge::get_table_name( 'related_products' );

		$related_products = $wpdb->get_results( "SELECT * FROM ${related_products_table} WHERE parent_id = 53" );

		$this->assertCount( 3, $related_products );
	}

	public function testRelatedProductsShouldNotReturnZero() {
		global $wpdb;

		Product_Update::$connectors['simple'] = $this->configurable_with_related_connector;
		Product_Update::update_product( 'tracer360' );

		$product_table = Magento_Bridge::get_table_name( 'products' );

		$related_product = $wpdb->get_row( "SELECT * FROM ${product_table} WHERE sku = 'reflect'" );

		$this->assertEquals( 19.95, $related_product->price );

		$related_product = $wpdb->get_row( "SELECT * FROM ${product_table} WHERE sku = 'shirt1'" );

		$this->assertEquals( 35, $related_product->price );
	}

	public function test_cached_products_do_not_update_for_related() {
		global $wpdb;

		$products = Magento_Bridge::get_table_name( 'products' );

		$cache_time = time() - 10;

		$wpdb->insert(
			$products,
			[
				'sku'        => 'lighthound',
				'mage_id'    => 58,
				'price'      => 5,
				'type'       => 'configurable',
				'cache_time' => $cache_time
			]
		);

		Product_Update::$connectors['simple'] = $this->configurable_with_related_connector;
		Product_Update::update_product( 'tracer360' );

		$lighthound = $wpdb->get_row( "SELECT * From ${products} WHERE sku = 'lighthound'" );

		$this->assertNotNull( $lighthound );
		$this->assertEquals( 58, $lighthound->mage_id );
		$this->assertEquals( $cache_time, $lighthound->cache_time );

		$related_products_table = Magento_Bridge::get_table_name( 'related_products' );

		$related_products = $wpdb->get_results( "SELECT * FROM ${related_products_table} WHERE parent_id = 53 AND related_id = 58" );

		$this->assertCount( 1, $related_products );
	}

	public function test_configurable_product_price_is_first_child_price() {
		global $wpdb;

		Product_Update::update_product( 'tracer360' );

		$products = Magento_Bridge::get_table_name( 'products' );

		$tracer = $wpdb->get_row( "SELECT * From ${products} WHERE sku = 'tracer360'" );

		$this->assertEquals( '69.95', $tracer->price );
		$this->assertEquals( '49.95', $tracer->special_price );
	}

	protected function insert_products() {

		global $wpdb;

		/** Set up Product and children */
		$wpdb->insert(
			\Magento_Bridge::get_table_name( 'products' ),
			[
				'sku'            => 'tracer360',
				'mage_id'        => 53,
				'name'           => 'Tracer 360',
				'price'          => 0,
				'main_photo_url' => '',
				'special_price'  => 0,
				'type'           => 'configurable',
				'cache_time'     => time(),
			]
		);

		$wpdb->insert(
			\Magento_Bridge::get_table_name( 'products' ),
			[
				'sku'           => '39g',
				'mage_id'       => 7,
				'name'          => '39 g',
				'price'         => 68,
				'special_price' => 0,
				'type'          => 'simple',
				'cache_time'    => time() - Product_Adapter_Wordpress::CACHE_AGE - 1,
			]
		);

		$wpdb->insert(
			\Magento_Bridge::get_table_name( 'products' ),
			[
				'sku'           => 'some_dumb_product',
				'mage_id'       => 999,
				'name'          => 'Some Dumb Product',
				'price'         => 52.56,
				'special_price' => 0,
				'type'          => 'simple',
				'cache_time'    => time(),
			]
		);


		/** Set up Configurable Label */
		$wpdb->insert(
			\Magento_Bridge::get_table_name( 'configurable_attributes' ),
			[
				'product_id'      => 53,
				'attribute_code'  => 'tracer_size',
				'attribute_id'    => 136,
				'attribute_label' => 'Size',
			]
		);

		/** Set up Product Attributes */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_attribute_label',
			[
				'attribute_id'          => 136,
				'value'                 => 100,
				'attribute_value_label' => 'S',
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_attribute_label',
			[
				'attribute_id'          => 136,
				'value'                 => 101,
				'attribute_value_label' => 'M',
			]
		);

		/** Set up Child Attributes */
		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_child_attributes',
			[
				'product_id'     => 5,
				'attribute_code' => 'tracer_size',
				'value'          => 100,
			]
		);

		$wpdb->insert(
			$wpdb->prefix . Magento_Bridge::BRIDGE_TABLE . '_child_attributes',
			[
				'product_id'     => 6,
				'attribute_code' => 'tracer_size',
				'value'          => 101,
			]
		);

	}

}
