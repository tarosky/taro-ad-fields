<?php
/**
 * Hooks for output
 *
 * @package taf
 */

/**
 * Render ad field
 */
add_action( 'taro_ad_field', function ( $position, $before = '', $after = '', $number = 1 ) {
	echo taf_render( $position, $before, $after, $number );
}, 10, 4 );
