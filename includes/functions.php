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
 * Clear all terms.
 *
 * @since 1.0.0
 * @return bool
 */
function taf_clear_terms() {
	$error = 0;
	$positions = get_terms( [
		'taxonomy'   => 'ad-position',
		'hide_empty' => false,
	] );
	if ( ! $positions || is_wp_error( $positions ) ) {
		return true;
	}
	foreach ( $positions as $position ) {
		$result = wp_delete_term( $position->term_id, 'ad-position' );
		if ( ! $result || is_wp_error( $result ) ) {
			$error++;
		}
	}
	return 0 === $error;
}

/**
 * Sync terms.
 *
 * @since 1.0.0
 * @return int
 */
function taf_register_positions() {
	$added = 0;
	foreach ( taf_default_positions() as $slug => $term ) {
		$name = isset( $term['name'] ) ? $term['name'] : $slug;
		$desc = isset( $term['description'] ) ? $term['description'] : '';
		$exist = get_term_by( 'slug', $slug, 'ad-position' );
		if ( is_wp_error( $exist ) || ! $exist ) {
			$term_ids = wp_insert_term( $name, 'ad-position', [
				'slug' => $slug,
				'description' => $desc,
			] );
			if ( ! is_wp_error( $term_ids ) ) {
				$added++;
				$exist = get_term_by( 'term_id', $term_ids['term_id'], 'ad-position' );
			}
		} else {
			$added++;
			wp_update_term( $exist->term_id, 'ad-position', [
				'slug' => $slug,
				'description' => $desc,
			] );
		}
		if ( is_wp_error( $exist ) ) {
			continue;
		}
		// Update term meta
		if ( isset( $term['mode'] ) && taf_available_display_mode( $term['mode'] ) ) {
			update_term_meta( $exist->term_id, 'taf_display_mode', $term['mode'] );
		} else {
			delete_term_meta( $exist->term_id, 'taf_display_mode' );
		}
	}
	return $added;
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
 *
 * @return string
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
		$output = '';

		$meta = get_post_meta( $ad->ID, '_taf_content', true );
		if ( $meta ) {
			$output .= $meta;
		}
		if ( trim( $ad->post_content ) ) {
			$output .= apply_filters( 'the_content', $ad->post_content );
		}
		if ( $output ) {
			return $before . $output . $after;
		}
	}
	return '';
}

/**
 * Check if display mode is O.K.
 *
 * @param string $mode
 * @return bool
 */
function taf_available_display_mode( $mode ) {
	return in_array( $mode, [ 'iframe', ] );
}

/**
 *
 *
 * @param string|int $position Term slug, term_id
 * @param array      $args     Query parameters added to URL.
 * @param string     $field    Default slug
 * @return string|WP_Error
 */
function taf_iframe_url( $position, $args = [], $field = 'slug' ) {
	$term = get_term_by( $field, $position, 'ad-position' );
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	} else {
		$url = get_term_link( $term );
		if ( $args ) {
			$url = add_query_arg( $args, $url );
		}
		/**
		 * taf_iframe_url
		 *
		 * Get term url
		 *
		 * @since 1.1.0
		 * @param string  $url
		 * @param WP_Term $term
		 */
		return apply_filters( 'taf_iframe_url', $url, $term );
	}
}
