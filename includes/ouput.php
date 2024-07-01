<?php
/**
 * Hooks for output
 *
 * @package taf
 */

/**
 * Render ad field
 */
add_action( 'taro_ad_field', function ( $position, $before = '', $after = '' ) {
	echo taf_render( $position, $before, $after );
}, 10, 3 );
