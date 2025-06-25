<?php
/**
 * Context handler for Taro Ad Fields
 */

/**
 * Register custom taxonomy "Context" for ad fields.
 */
add_action( 'init', function () {
	register_taxonomy(
		'ad-context',
		'ad-content',
		array(
			'labels'            => array(
				'name'          => __( 'Contexts', 'taf' ),
				'singular_name' => __( 'Context', 'taf' ),
				'search_items'  => __( 'Search Contexts', 'taf' ),
				'popular_items' => __( 'Popular Contexts', 'taf' ),
				'all_items'     => __( 'All Contexts', 'taf' ),
				'parent_item'   => __( 'Parent Context', 'taf' ),
				'edit_item'     => __( 'Edit Context', 'taf' ),
				'update_item'   => __( 'Update Context', 'taf' ),
				'add_new_item'  => __( 'Add New Context', 'taf' ),
				'new_item_name' => __( 'New Context', 'taf' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'meta_box_cb'       => 'taf_context_meta_box_callback',
		)
	);
}, 11 );

/**
 * Render context meta box.
 *
 * @param WP_Post$post
 * @return void
 */
function taf_context_meta_box_callback( $post ) {
	$terms    = get_the_terms( $post, 'ad-context' );
	$selected = array();
	if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$selected[] = $term->term_id;
		}
	}
	$all_parents = get_terms( 'ad-context', array(
		'hide_empty' => false,
		'parent'     => 0, // Only parents.
	) );
	if ( empty( $all_parents ) || is_wp_error( $all_parents ) ) {
		// No parent found.
		printf( '<p class="description">%s</p>', esc_html__( 'No context found.', 'taf' ) );
		return;
	}
	// This description will display with jQuery if no contexts are required by current selection of positions.
	printf( '<p id="no-contexts-available" class="description" style="display: none;">%s</p>', esc_html__( 'No Contexts available for current selection of Positions.', 'taf' ) );
	// Draw context containers for each parent group.
	foreach ( $all_parents as $parent ) {
		$children = get_terms( 'ad-context', array(
			'hide_empty' => false,
			'parent'     => $parent->term_id,
		) );
		if ( empty( $children ) || is_wp_error( $children ) ) {
			// No child found.
			printf( '<p class="description">%s %s</p>', __( 'No item for the context:', 'taf' ), esc_html( $parent->name ) );
			continue;
		}
		echo '<div class="ad-context__container">';
		printf( '<h3 class="ad-context__heading">%s</h3>', esc_html( $parent->name ) );
		if ( trim( $parent->description ) ) {
			// Description exists.
			printf( '<p class="description">%s</p>', nl2br( esc_html( $parent->description ) ) );
		}
		// Allow selecting no context by adding a hidden input field.
		echo '<input type="hidden" name="tax_input[ad-context][]" value="" />';
		foreach ( $children as $child ) {
			printf(
				'<p class="ad-context__item"><label><input type="checkbox" name="tax_input[%s][]" value="%d" %s /> %s</label></p>',
				esc_attr( $child->taxonomy ),
				esc_attr( $child->term_id ),
				checked( in_array( $child->term_id, $selected, true ), true, false ),
				esc_html( $child->name )
			);
		}
		echo '</div> <!-- //ad-context__container -->';
	}
	?>
	<p class="ad-context__info">
		<?php esc_html_e( 'Context is defined by theme or plugin.', 'taf' ); ?>
	</p>
	<?php
}

/**
 * Show notice
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( $screen && 'edit-ad-context' === $screen->id ) {
		// Ensure
		$contexts = taf_register_contexts();
		if ( empty( $contexts ) ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Notice:', 'taf' ); ?></strong>
				<?php esc_html_e( 'Default contexts are registered from theme or plugin. Changing them may cause unexpected result.', 'taf' ); ?>
			</p>
		</div>
		<?php
	}
} );


/**
 * Register contexts and return term.
 *
 * @return WP_Term[]
 */
function taf_register_contexts() {
	$registered = array();
	// Register groups.
	$groups  = taf_default_context_group();
	$parents = array();
	foreach ( $groups as $slug => $group ) {
		$terms = get_terms( array(
			'taxonomy'   => 'ad-context',
			'hide_empty' => false,
			'slug'       => $slug,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$parent       = $terms[0];
			$registered[] = $terms[0];
		} else {
			$term = wp_insert_term(
				$group['name'],
				'ad-context',
				array(
					'slug'        => $slug,
					'description' => $group['description'],
				)
			);
			if ( is_wp_error( $term ) ) {
				continue;
			}
			$parent = get_term( $term['term_id'], 'ad-context' );
		}
		$parents[ $parent->slug ] = $parent->term_id;
		$registered[]             = $parent;
	}
	// Register contexts.
	$contexts = taf_default_contexts();
	foreach ( $contexts as $slug => $context ) {
		// Get parent.
		if ( ! array_key_exists( $context['group'], $parents ) ) {
			// No group found.
			continue;
		}
		// Existing?
		$terms = get_terms( array(
			'taxonomy'   => 'ad-context',
			'hide_empty' => false,
			'slug'       => $slug,
			'parent'     => $parents[ $context['group'] ],
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$term = $terms[0];
		} else {
			$term = wp_insert_term(
				$context['name'],
				'ad-context',
				array(
					'slug'   => $slug,
					'parent' => $parents[ $context['group'] ],
				)
			);
			if ( is_wp_error( $term ) ) {
				continue;
			}
			$term = get_term( $term['term_id'], 'ad-context' );
		}
		$registered[] = $term;
	}
	return $registered;
}
