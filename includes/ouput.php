<?php
/**
 * Hooks for output
 *
 * @package taf
 */

/**
 * Render ad field
 */
add_action( 'taro_ad_field', function ( $position, $before = '', $after = '', $number = 1, $contexts = array() ) {
	echo taf_render( $position, $before, $after, $number, $contexts );
}, 10, 5 );
