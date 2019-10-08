<?php
/**
 * Mage Bridge Autoloader
 *
 * @package    Noxgear
 * @subpackage Noxgear
 * @since      2019 Oct
 */


/**
 * Autoloader for Surety Plan Setup classes
 *
 * @package    Surety
 * @subpackage Surety
 * @since      2019 Mar
 */

spl_autoload_register(
	function ( $class ) {
		$class_path = str_replace( '\\', '/', $class );

		$final_slash_pos = strrpos( $class_path, '/' );

		if ( false === $final_slash_pos ) {
			$class_name = $class;
		} else {
			$class_name = substr( $class_path, $final_slash_pos + 1 );
		}

		$class_path = preg_replace( "/$class_name$/", '', $class_path, 1 );

		$class_path = str_replace( 'Magento_Bridge/', '', $class_path );

		$class_path = strtolower($class_path);

		$class_name = str_replace( '_', '-', $class_name );

		$full_path = __DIR__ . '/' . $class_path . sprintf( 'class-%s.php', strtolower( $class_name ) );

		if ( file_exists( $full_path ) ) {
			require_once $full_path;
		}
	}
);


