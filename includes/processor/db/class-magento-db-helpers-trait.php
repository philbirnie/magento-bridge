<?php
/**
 * Magento DB Helpers Trait
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */

namespace Magento_Bridge\Processor\Db;

trait Magento_Db_Helpers_Trait {

	/**
	 * Gets Main Image From Attributes
	 *
	 * @param $result
	 *
	 * @return string
	 */
	protected function get_main_image_from_attributes( $result ) {
		$image_attributes = array_filter( $result->custom_attributes ?? [], function ( $attribute ) {
			return isset( $attribute->attribute_code ) && 'image' === $attribute->attribute_code;
		} );

		if ( ! $image_attributes ) {
			return '';
		}

		$image_attributes = array_values( $image_attributes );

		return isset( $image_attributes[0]->value ) ? sprintf( 'media/catalog/product%s', $image_attributes[0]->value ) : '';
	}


	/**
	 * Gets Special Price from Attributes
	 *
	 * @param $result
	 *
	 * @return float
	 */
	public function get_special_price_from_attributes( $result ) {
		$special_price = array_filter( $result->custom_attributes ?? [], function ( $attribute ) {
			return isset( $attribute->attribute_code ) && 'special_price' === $attribute->attribute_code;
		} );

		if ( ! $special_price ) {
			return 0.00;
		}

		$special_price = array_values( $special_price );

		return $special_price[0]->value ?? 0.00;
	}


	/**
	 * Convenience function to get all custom attributes;
	 *
	 * @param $result
	 *
	 * @return array
	 */
	protected function get_custom_attributes( $result ): array {

		$attributes = [];

		foreach ( $result->custom_attributes ?? [] as $custom_attribute ) {
			$attributes[ $custom_attribute->attribute_code ] = $custom_attribute->value;
		}

		return $attributes;
	}

	/**
	 * Gets a JSON of additional photos from attributes
	 *
	 * @param $result
	 *
	 * @return []
	 */
	protected function get_additional_photos_from_attributes( $result ): array {
		return array_reduce( $result->media_gallery_entries ?? [], function ( $carry, $photo ) {
			$carry[] = sprintf( 'media/catalog/product%s', $photo->file );
			return $carry;
		}, [] );
	}

	/**
	 * Gets Description from Attributes
	 *
	 * @param $result
	 *
	 * @return string
	 */
	protected function get_description_from_attributes( $result ): string {
		$description_attribute = array_filter( $result->custom_attributes ?? [], function ( $attribute ) {
			return isset( $attribute->attribute_code ) && 'short_description' === $attribute->attribute_code;
		} );

		$description_attribute = array_values( $description_attribute );

		if ( $description_attribute ) {
			return strip_tags($description_attribute[0]->value);
		}
		return '';
	}

	protected function get_url_from_attributes( $result ): string {

		$store_url_attribute = 'nox_store_url';

		$custom_attributes = $this->get_custom_attributes( $result );

		if ( isset( $custom_attributes[ $store_url_attribute ] ) ) {
			return $custom_attributes[ $store_url_attribute ];
		}
		return $custom_attributes['url_key'] ?? '';
	}


}
