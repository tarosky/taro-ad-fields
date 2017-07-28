<?php
/**
 * Utility functions for Taro Ad Fields
 *
 * @package taf
 * @since 1.0.0
 */


/**
 * Get Default position to register
 *
 * @return array
 */
function taf_default_positions() {

	/**
	 * taf_default_positions
	 *
	 * @param array $positions
	 * @return array
	 */
	return apply_filters( 'taf_default_positions', [] );
}

/**
 * Detect if term is registered
 *
 * @param int|string|WP_Term $term
 *
 * @return bool
 */
function taf_is_registered( $term ) {
	if ( is_string( $term ) && ! is_numeric( $term ) ) {
		$term = get_term_by( 'slug', $term, 'ad-position' );
	} else {
		$term = get_term( $term, 'ad-position' );
	}
	if ( ! is_a( $term, 'WP_Term' ) ) {
		return false;
	}
	$positions = taf_default_positions();
	return isset( $positions[ $term->slug ] );
}

/**
 * Render ad content
 *
 * @package taf
 * @param string $position
 * @param string $before Default empty string
 * @param string $after  Default empty string
 */
function taf_render( $position, $before = '', $after = '' ) {
	$position = get_term_by( 'slug', $position, 'ad-position' );
	$is_preview = current_user_can( 'edit_posts' ) && ( 'true' === get_query_var( 'taf_preview' ) );
	$args = [
		'post_type'      => 'ad-content',
	    'posts_per_page' => 1,
	    'orderby' => [ 'date' => 'DESC' ],
	    'post_status' => $is_preview ? [ 'publish', 'future' ] : 'publish',
	    'tax_query' => [
	    	[
	    		'taxonomy' => 'ad-position',
		        'terms'    => $position,
		        'field'    => 'slug',
		    ]
	    ],
	];
	foreach( get_posts( $args ) as $ad ) {
		$meta = get_post_meta( $ad->ID, '_taf_content', true );
		echo $before;
		if ( $meta ) {
			echo $meta;
		}
		if ( trim( $ad->post_content ) ) {
			echo apply_filters( 'the_content', $ad->post_content );
		}
		echo $after;
	}
}
add_action( 'taro_ad_field', 'taf_render', 10, 3 );
