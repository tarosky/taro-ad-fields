<?php
/*
Plugin Name: Taro Ad Fields
Plugin URI: https://wordpress.org/plugin/taro-ad-fields
Description: Add ad block for advertisments
Author: Tarosky INC.
Author URI: https://tarosky.co.jp
Text Domain: taf
Domain Path: /languages/
License: GPL v3 or later.
Version: 1.1.0
PHP Version: 5.4.0
*/

add_action( 'plugins_loaded', 'taro_ad_field_init' );

/**
 * Bootstrap
 *
 * @package taf
 * @since 1.0.0
 * @access private
 */
function taro_ad_field_init() {
	load_plugin_textdomain( 'taf', false, basename( dirname( __FILE__ ) ) . '/languages' );
	foreach ( scandir( dirname( __FILE__ ) . '/includes' ) as $file ) {
		if ( preg_match( '#^[^._].*\.php$#u', $file ) ) {
			require dirname( __FILE__ ) . '/includes/' . $file;
		}
	}
}

/**
 * Get version number
 *
 * @package taf
 * @since 1.0.0
 * @return string
 */
function taro_ad_version() {
	static $version = null;
	if ( is_null( $version ) ) {
		$info = get_file_data( __FILE__, array(
			'version' => 'Version',
		) );
		$version = $info['version'];
	}
	return $version;
}
