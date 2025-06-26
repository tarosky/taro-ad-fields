<?php
/**
 * 広告ブロックを登録する
 *
 * @package taf
 */
add_action( 'init', function () {

	/**
	 * taf_post_type_args
	 *
	 * @package taf
	 * @since 1.0.0
	 * @param array  $post_type_args
	 * @param string $post_type
	 * @return array
	 */
	$post_type_args = apply_filters( 'taf_post_type_args', array(
		'labels'          => array(
			'name'          => __( 'Ad Fields', 'taf' ),
			'singular_name' => __( 'Ad Field', 'taf' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'capability_type' => 'page',
		'hierarchical'    => false,
		'menu_position'   => 60,
		'menu_icon'       => 'dashicons-megaphone',
		'taxonomies'      => array( 'ad-position' ),
		'supports'        => array( 'title', 'editor', 'excerpt', 'author' ),
	), 'ad-content' );
	// Register post type
	register_post_type( 'ad-content', $post_type_args );

	// Register custom taxonomy
	register_taxonomy(
		'ad-position',
		'ad-content',
		array(
			'label'             => __( 'Positions', 'taf' ),
			'labels'            => array(
				'name'          => __( 'Positions', 'taf' ),
				'singular_name' => __( 'Position', 'taf' ),
				'search_items'  => __( 'Search Positions', 'taf' ),
				'popular_items' => __( 'Popular Positions', 'taf' ),
				'all_items'     => __( 'All Positions', 'taf' ),
				'parent_item'   => __( 'Parent Position', 'taf' ),
				'edit_item'     => __( 'Edit Position', 'taf' ),
				'update_item'   => __( 'Update Position', 'taf' ),
				'add_new_item'  => __( 'Add New Position', 'taf' ),
				'new_item_name' => __( 'New Position', 'taf' ),
			),
			'show_admin_column' => true,
			'hierarchical'      => false,
			'meta_box_cb'       => function ( $post ) {
				$terms = get_the_terms( $post, 'ad-position' );
				$tags  = array();
				if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$tags[] = $term->name;
					}
				}
				$all_terms = get_terms( 'ad-position', array( 'hide_empty' => false ) );
				?>
				<input type="hidden" name="tax_input[ad-position]" id="ad-position-saver"
						value="<?php echo esc_attr( implode( ',', $tags ) ); ?>"/>
				<script>
					(function(){
					jQuery(document).ready(function($){
						function refresh_context() {
							// function refreshes the Contexts box.
							let empty = true;

							$('.ad-context__container').each(function (){
								const $container = $(this);
								const $parent = $container.find('.ad-context__heading').text().trim();
								let found = false;

								// check if Position exists that requires Context and is checked.
								$('.adPosition__item').each(function () {
									if (
										$(this).find('.button').toArray().some(el => $(el).text().trim() === $parent) &&
										$(this).find('.adPosition__check').is(':checked')
									){
										found = true;
										return false;
									}
								});

								// Show or hide Context.
								$container.css('display', found ? 'block' : 'none');
								if(found){empty = false;}
							});

							// Show message if no available Contexts.
							$('#no-contexts-available').css('display', empty ? 'block' : 'none');
						}

						$('.adPosition__check').click(function(){
							let value = [];
							$('.adPosition__check:checked').each(function(index, input){
								value.push($(input).val());
							});
							$('#ad-position-saver').val(value.join(','));

							refresh_context();
						});

						refresh_context();
					});
					})();
				</script>
				<?php if ( empty( $all_terms ) || is_wp_error( $all_terms ) ) : ?>
					<p style="color: red;">
						<?php esc_html_e( 'No position found.', 'taf' ); ?>
					</p>
				<?php else : ?>
					<div class="adPosition">
						<?php foreach ( $all_terms as $term ) : ?>
							<div class="adPosition__item">
								<label class="adPosition__label">
									<input type="checkbox" class="adPosition__check" value="<?php echo esc_attr( $term->name ); ?>" <?php checked( has_term( $term->term_id, $term->taxonomy, $post ) ); ?>/>
									<?php echo esc_html( $term->name ); ?>
								</label>
								<p class="adPosition__description">
									<?php echo esc_html( $term->description ); ?>
								</p>
								<?php
								$contexts = taf_get_position_contexts( $term );
								if ( ! empty( $contexts ) && ! is_wp_error( $contexts ) ) {
									?>
									<p>
										<?php esc_html_e( 'Available Contexts:', 'taf' ); ?>
										<?php
										foreach ( $contexts as $context ) {
											?>
											<a class="button" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( get_edit_term_link( $context ) ); ?>">
												<?php echo esc_html( $context->name ); ?>
											</a>
											<?php
										}
										?>
									</p>
									<?php
								}
								?>
							</div>
						<?php endforeach; ?>
					</div>
					<hr/>
					<p class="adPosition__info">
						<?php esc_html_e( 'If you select multiple position, same block will be displayed in multiple places.', 'taf' ); ?>
					</p>
					<?php
				endif;
			},
		)
	);
} );

/**
 * Show notice
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( in_array( $screen->id, [ 'edit-ad-position' ], true ) && taf_default_positions() ) {
		taf_register_positions();
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Notice:', 'taf' ); ?></strong>
				<?php esc_html_e( 'Default positions are registered from theme or plugin. Changing their slug may cause unexpected result.', 'taf' ); ?>
			</p>
		</div>
		<?php
	}
} );

/**
 * Add column for taxonomy
 *
 * @param array $columns string
 */
function taf_taxonomy_columns( $columns ) {
	$columns['registered'] = __( 'Registered', 'taf' );
	if ( 'ad-position' === get_current_screen()->taxonomy ) {
		$columns['display_mode'] = __( 'Display', 'taf' );
		$columns['contexts']     = __( 'Contexts', 'taf' );
	}
	return $columns;
}
add_filter( 'manage_edit-ad-position_columns', 'taf_taxonomy_columns' );
add_filter( 'manage_edit-ad-context_columns', 'taf_taxonomy_columns' );

/**
 * Show column content for taxonomy
 *
 * @param string $value Column value.
 * @param string $column Column name.
 * @param int    $term_id Term ID.
 */
function taf_taxonomy_column_render( $value, $column, $term_id ) {
	switch ( $column ) {
		case 'registered':
			if ( taf_is_registered( $term_id ) ) {
				return '<span class="dashicons dashicons-thumbs-up" style="color: #4b9b6d;"></span>';
			} else {
				return '<span class="dashicons dashicons-thumbs-down" style="color: darkgrey;"></span>';
			}
		case 'display_mode':
			return esc_html( get_term_meta( $term_id, 'taf_display_mode', true ) ?: '---' );
		case 'contexts':
			$labels = [];
			foreach ( taf_get_position_contexts( $term_id ) as $context ) {
				$labels[] = sprintf( '<a href="%s">%s</a>', get_edit_term_link( $context ), esc_html( $context->name ) );
			}
			return empty( $labels ) ? '---' : implode( ', ', $labels );
		default:
			return $value;
	}
}
add_filter( 'manage_ad-position_custom_column', 'taf_taxonomy_column_render', 10, 3 );
add_filter( 'manage_ad-context_custom_column', 'taf_taxonomy_column_render', 10, 3 );

/**
 * Get position contexts
 *
 * @param int|string|WP_Term $position Position slug.
 *
 * @return WP_Term[]
 */
function taf_get_position_contexts( $position ) {
	$position = get_term( $position, 'ad-position' );
	if ( ! $position || is_wp_error( $position ) ) {
		return [];
	}
	$terms    = [];
	$contexts = get_term_meta( $position->term_id, 'taf_contexts' );
	if ( ! empty( $contexts ) ) {
		foreach ( $contexts as $context ) {
			$term = get_term_by( 'slug', $context, 'ad-context' );
			if ( $term && ! is_wp_error( $term ) ) {
				$terms[] = $term;
			}
		}
	}
	return $terms;
}

/**
 * Show form notice
 */
add_action( 'ad-position_edit_form_fields', function ( $term ) {
	?>
	<tr>
		<th><?php esc_html_e( 'Registered', 'taf' ); ?></th>
		<td>
			<?php if ( taf_is_registered( $term ) ) : ?>
				<p style="color: #4b9b6d;">
					<span class="dashicons dashicons-thumbs-up"></span>
					<?php esc_html_e( 'This position is registered for themes.', 'taf' ); ?>
				</p>
			<?php else : ?>
				<p style="color: #d93d2e;">
					<span class="dashicons dashicons-thumbs-down"></span>
					<?php esc_html_e( 'This position is not registered for themes.', 'taf' ); ?>
				</p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
} );


// Display help menu
add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'ad-content' !== $post->post_type ) {
		return;
	}
	?>
	<script>
		jQuery(document).ready(function($){
			$('.adContent-toggle').click(function(e){
			e.preventDefault();
			$('.adContent-list').toggleClass('toggle');
			});
		});
	</script>
	<div class="adContent">
		<button class="adContent-toggle button"><?php esc_html_e( 'Open Help of Taro Ad Fields', 'taf' ); ?></button>
		<dl class="adContent-list">
			<dt><?php esc_html_e( 'What will be displayed', 'taf' ); ?></dt>
			<dd><?php esc_html_e( 'The content in editor below.', 'taf' ); ?></dd>
			<dd><?php esc_html_e( 'The content in "Raw Content" meta box. They will never be escaped, so you can use Javascripts for ads.', 'taf' ); ?></dd>
			<dt><?php esc_html_e( 'How To Preview', 'taf' ); ?></dt>
			<dd><?php esc_html_e( 'Set this ad field\'s publish date to future.', 'taf' ); ?></dd>
			<dd><?php esc_html_e( 'Then publish. This ad becomes future post.', 'taf' ); ?></dd>
			<dd>
			<?php
				$url = add_query_arg( array( 'taf_preview' => 'true' ), home_url( '/' ) );
				printf(
					// translators: %s is a link tag.
					esc_html__( 'Access desired page with query parameter "taf_preview=true". If this ad will be displayed on top page, go to %s.', 'taf' ),
					sprintf(
						'<a href="%s" target="_blank">%s</a>',
						$url, esc_html( $url )
					)
				)
			?>
				</dd>
			<dd>
				<?php esc_html_e( 'Don\'t forget to change status of this ad after confirmation.', 'taf' ); ?>
			</dd>
			<dt><?php esc_html_e( 'Field Expiration', 'taf' ); ?></dt>
			<dd>
				<?php
				// translators: %s is a link tag.
				printf( esc_html__( 'If you want ads to be automatically expired, please consider %s!', 'taf' ), '<a href="https://ja.wordpress.org/plugins/taro-clockwork-post/" target="_blank">Taro Clockwork Post</a>' );
				?>
			</dd>
		</dl>
	</div>
	<?php
} );

// Show notices
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	$terms = get_terms( array(
		'taxonomy'   => 'ad-position',
		'hide_empty' => false,
	) );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return;
	}
	// No position exists.
	printf(
		'<div class="error"><p><strong>[Taro Ad Fields]</strong> %1$s &raquo; <a href="%3$s">%2$s</a></p></div>',
		esc_html__( 'No position is registered. No position will be displayed until you register at least 1 position.', 'taf' ),
		esc_html__( 'Register Positions', 'taf' ),
		admin_url( 'edit-tags.php?taxonomy=ad-position&post_type=ad-content' )
	);
} );

/**
 * Add query var for preview.
 *
 * @todo avoid front page query
 */
add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'taf_preview';
	return $vars;
} );

// No cache if this is preview.
add_action( 'template_redirect', function () {
	if ( 'true' === get_query_var( 'taf_preview' ) ) {
		nocache_headers();
	}
} );


// Register widget
add_action( 'widgets_init', function () {
	register_widget( 'TafWidget' );
} );
