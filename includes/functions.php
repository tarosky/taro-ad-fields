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
	 * @return array<string, array{name:string, description:string, mode:string, contexts:string[]}>
	 */
	return apply_filters( 'taf_default_positions', array() );
}

/**
 * Get Default contexts to register.
 *
 * Contexts are useful for grouping ads.
 * The contexts registered by the filter are recognized as those set for the theme.
 * The theme can display ads according to specific contexts, such as browsing environment or group.
 *
 * @return array{string, name:string, group: string}
 */
function taf_default_contexts() {
	return apply_filters( 'taf_default_contexts', array(
		'mobile'     => array(
			'name'  => __( 'Mobile Browser', 'taf' ),
			'group' => 'device',
		),
		'desktop'    => array(
			'name'  => __( 'Desktop Browser', 'taf' ),
			'group' => 'device',
		),
		'all-device' => array(
			'name'  => __( 'All Device', 'taf' ),
			'group' => 'device',
		),
	) );
}

/**
 * Get Default group to register in context.
 *
 * Context group is actually a parent term.
 *
 * @return array<string, array{name:string, slug:string, description:string}>
 */
function taf_default_context_group() {
	/**
	 * taf_default_context_groups
	 *
	 * @param array<string, array{name: string, description: string}> $groups
	 */
	return apply_filters( 'taf_default_context_groups', array(
		'device' => array(
			'name'        => __( 'Device', 'taf' ),
			'description' => __( 'Depends on user\'s device.', 'taf' ),
		),
	) );
}

/**
 * Clear all terms.
 *
 * @since 1.0.0
 * @return bool
 */
function taf_clear_terms() {
	$error     = 0;
	$positions = get_terms( array(
		'taxonomy'   => 'ad-position',
		'hide_empty' => false,
	) );
	if ( ! $positions || is_wp_error( $positions ) ) {
		return true;
	}
	foreach ( $positions as $position ) {
		$result = wp_delete_term( $position->term_id, 'ad-position' );
		if ( ! $result || is_wp_error( $result ) ) {
			++$error;
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
		$name  = $term['name'] ?? $slug;
		$desc  = $term['description'] ?? '';
		$exist = get_term_by( 'slug', $slug, 'ad-position' );
		if ( is_wp_error( $exist ) || ! $exist ) {
			$term_ids = wp_insert_term( $name, 'ad-position', array(
				'slug'        => $slug,
				'description' => $desc,
			) );
			if ( ! is_wp_error( $term_ids ) ) {
				++$added;
				$exist = get_term_by( 'term_id', $term_ids['term_id'], 'ad-position' );
			}
		} else {
			++$added;
			wp_update_term( $exist->term_id, 'ad-position', array(
				'slug'        => $slug,
				'description' => $desc,
			) );
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
		delete_term_meta( $exist->term_id, 'taf_contexts' );
		if ( ! empty( $term['contexts'] ) ) {
			foreach ( $term['contexts'] as $group ) {
				// Get term by slug.
				$context = get_term_by( 'slug', $group, 'ad-context' );
				if ( $context && ! is_wp_error( $context ) ) {
					add_term_meta( $exist->term_id, 'taf_contexts', $context->slug );
				}
			}
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
function taf_is_registered( $term, $taxonomy = '' ) {
	if ( is_string( $term ) && ! is_numeric( $term ) ) {
		$term = get_term_by( 'slug', $term, $taxonomy );
	} elseif ( ! is_a( $term, 'WP_Term' ) ) {
		$term = get_term( $term, $taxonomy );
	}
	if ( ! is_a( $term, 'WP_Term' ) ) {
		return false;
	}
	switch ( $term->taxonomy ) {
		case 'ad-position':
			$positions = taf_default_positions();
			return isset( $positions[ $term->slug ] );
		case 'ad-context':
			$groups   = taf_default_context_group();
			$contexts = taf_default_contexts();
			return array_key_exists( $term->slug, $groups ) || array_key_exists( $term->slug, $contexts );
		default:
			return false;
	}
}

/**
 * Render ad content
 *
 * @param string   $position Slug of position.
 * @param string   $before   Default empty string
 * @param string   $after    Default empty string
 * @param int      $number   Number to display. Default 1.
 * @param string[] $contexts Contexts to display.
 *
 * @return string
 */
function taf_render( $position, $before = '', $after = '', $number = 1, array $contexts = array() ) {
	// Check if position is registered.
	$position = get_term_by( 'slug', $position, 'ad-position' );
	if ( ! $position || is_wp_error( $position ) ) {
		return '';
	}
	$args = [
		'post_type'      => 'ad-content',
		'posts_per_page' => $number,
		'orderby'        => [ 'date' => 'DESC' ],
		'no_found_rows'  => true,
		'post_status'    => 'publish',
		'tax_query'      => [
			[
				'taxonomy' => 'ad-position',
				'terms'    => [ $position->slug ],
				'field'    => 'slug',
			],
		],
	];
	// Is this preview?
	if ( current_user_can( 'edit_posts' ) && ( 'true' === get_query_var( 'taf_preview' ) ) ) {
		$args['post_status'] = [ 'publish', 'future' ];
	}
	// Is contexts set?
	if ( ! empty( $contexts ) ) {
		// Convert contexts to term objects.
		$contexts = array_filter( array_map( function ( $slug ) {
			$term = get_term_by( 'slug', $slug, 'ad-context' );
			if ( ! $term || is_wp_error( $term ) ) {
				return '';
			}
			return $term;
		}, $contexts ) );
		// Divide by group.
		$context_queries = array();
		foreach ( $contexts as $context ) {
			if ( ! $context->parent ) {
				continue;
			}
			if ( ! isset( $context_queries[ $context->parent ] ) ) {
				$context_queries[ $context->parent ] = array();
			}
			$context_queries[ $context->parent ][] = $context->term_id;
		}
		foreach ( $context_queries as $parent => $term_ids ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'ad-context',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			);

		}
	}
	$args = apply_filters( 'taf_render_query', $args, $position, $before, $after, $number, $contexts );
	// Ship Query
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}
	$output = '';
	foreach ( $query->posts as $ad ) {
		$meta       = get_post_meta( $ad->ID, '_taf_content', true );
		$ad_content = '';
		if ( $meta ) {
			// Meta fields exist.
			$meta        = apply_filters( 'taf_content', $meta, $ad );
			$ad_content .= $meta;
		}
		if ( trim( $ad->post_content ) ) {
			// Post body exists.
			$ad_content .= apply_filters( 'the_content', $ad->post_content );
		}
		if ( ! empty( $ad_content ) ) {
			$output .= $before . $ad_content . $after;
		}
	}
	return $output;
}

/**
 * Check if display mode is O.K.
 *
 * @param string $mode
 * @return bool
 */
function taf_available_display_mode( $mode ) {
	return in_array( (string) $mode, [ 'iframe' ], true );
}

/**
 * iframeとして表示する
 *
 * @param string|int $position Term slug, term_id
 * @param array      $args     Query parameters added to URL.
 * @param string     $field    Default slug
 * @return string|WP_Error
 */
function taf_iframe_url( $position, $args = array(), $field = 'slug' ) {
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

/**
 * Validates that all context requirements for each ad position are satisfied.
 *
 * For each position's required contexts (stored as term meta),
 * the function checks whether at least one of the submitted context ids
 * in ad-content has a parent context that matches.
 *
 * @param array $tax_input Array of taxonomy inputs
 * @return bool True if a match was found for all the required contexts.
 */
function taf_validate_tax_input( $tax_input ) {

	// Get ad context (array of term ids)
	$ad_context = array_filter( $tax_input['ad-context'] ?? [], 'is_numeric' );

	// Get ad position (array of term names)
	$ad_position = array_filter(
		array_map( 'trim', explode( ',', $tax_input['ad-position'] ?? '' ) ),
		fn( $val ) => '' !== $val
	);

	// Go through every position's required contexts and look for a match.
	foreach ( $ad_position as $pos ) {
		$pos_term = get_term_by( 'name', $pos, 'ad-position' );

		if ( $pos_term && ! is_wp_error( $pos_term ) ) {
			$req_context_slugs = get_term_meta( $pos_term->term_id, 'taf_contexts', false );

			// Skip if position has no required contexts.
			if ( empty( $req_context_slugs ) ) {
				continue;
			}

			// Make sure each required context matches with the parent of at least one of the submitted contexts in ad-context.
			foreach ( $req_context_slugs as $slug ) {
				$matched = false;

				foreach ( $ad_context as $context_id ) {
					$context_term = get_term( $context_id, 'ad-context' );

					if ( $context_term && ! is_wp_error( $context_term ) && $context_term->parent > 0 ) {
						$parent_term = get_term( $context_term->parent, 'ad-context' );

						// If match found, skip to next required context.
						if ( $parent_term && ! is_wp_error( $parent_term ) ) {
							if ( $parent_term->slug === $slug ) {
								$matched = true;
								break;
							}
						}
					}
				}

				// Return false if no matching context was found.
				if ( ! $matched ) {
					return false;
				}
			}
		}
	}

	return true;
}
