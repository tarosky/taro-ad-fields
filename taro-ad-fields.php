<?php
/*
Plugin Name: Taro Ad Fields
Plugin URI: https://wordpress.org/plugin/taro-ad-fields
Description: Add ad blocks for advertisement.
Author: Tarosky INC.
Author URI: https://tarosky.co.jp
Requires at least: 5.9
Requires PHP: 7.4
Text Domain: taf
Domain Path: /languages/
License: GPL v3 or later.
Version: nightly
*/

// Do not load directly.
defined( 'ABSPATH' ) || exit;

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
	// Register assets.
	add_action( 'init', 'taf_register_assets' );
	// Composer if exists.
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
	}
	// If this is a test environment, load test bootstrap.
	if ( class_exists( 'Tarosky\TaroAdFieldsTest\Bootstrap' ) ) {
		new Tarosky\TaroAdFieldsTest\Bootstrap();
	}
}

/**
 * Register all assets for Taro Ad Fields.
 *
 * @return void
 */
function taf_register_assets() {
	$json = __DIR__ . '/wp-dependencies.json';
	if ( ! file_exists( $json ) ) {
		return;
	}
	$dependencies = json_decode( file_get_contents( $json ), true );
	if ( empty( $dependencies ) || ! is_array( $dependencies ) ) {
		return;
	}
	// Register styles.
	foreach ( $dependencies as $dependency ) {
		if ( empty( $dependency['path'] ) ) {
			continue;
		}
		$url = plugins_url( $dependency['path'], __FILE__ );
		switch ( $dependency['ext'] ) {
			case 'js':
				$script_info = [
					'in_footer' => $dependency['footer'] ?? true,
				];
				if ( in_array( $dependency['strategy'], [ 'defer', 'async' ], true ) ) {
					$script_info['strategy'] = $dependency['strategy'];
				}
				wp_register_script(
					$dependency['handle'],
					$url,
					$dependency['deps'],
					$dependency['hash'],
					$script_info
				);
				break;
			case 'css':
				wp_register_style( $dependency['handle'], $url, $dependency['deps'], $dependency['hash'], $dependency['media'] );
				break;
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
		$info    = get_file_data( __FILE__, array(
			'version' => 'Version',
		) );
		$version = $info['version'];
	}
	return $version;
}
