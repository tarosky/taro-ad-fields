<?php
/*
Plugin Name: Taro Ad Fields
Plugin URI: https://wordpress.org/plugin/taro-ad-fields
Description: Add ad blocks for advertisement.
Author: Tarosky INC.
Author URI: https://tarosky.co.jp
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Text Domain: taf
Domain Path: /languages/
License: GPL v3 or later.
Version: nightly
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
	// Load translation.
	load_plugin_textdomain( 'taf', false, basename( __DIR__ ) . '/languages' );
	// Load includes.
	foreach ( scandir( __DIR__ . '/includes' ) as $file ) {
		if ( preg_match( '#^[^._].*\.php$#u', $file ) ) {
			require __DIR__ . '/includes/' . $file;
		}
	}
	// Composer if exists.
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
	}
	// If this is test environment, load test bootstrap.
	if ( class_exists( 'Tarosky\TaroAdFieldsTest\Bootstrap' ) ) {
		new Tarosky\TaroAdFieldsTest\Bootstrap();
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
		$info    = get_file_data( __FILE__, array(
			'version' => 'Version',
		) );
		$version = $info['version'];
	}
	return $version;
}
