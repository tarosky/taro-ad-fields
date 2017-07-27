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
	$post_type_args = apply_filters( 'taf_post_type_args', [
		'labels'          => [
				'name' => __( 'Ad Fields', 'taf' ),
				'singular_name' => __( 'Ad Field', 'taf' ),
		],
		'public'          => false,
		'show_ui'         => true,
		'capability_type' => 'page',
		'hierarchical'    => false,
		'menu_position'   => 60,
		'menu_icon'       => 'dashicons-megaphone',
		'taxonomies'      => array( 'ad-position' ),
		'supports'        => [ 'title', 'editor', 'excerpt', 'author' ],
	], 'ad-content' );
	// Register post type
	register_post_type( 'ad-content', $post_type_args );

	// Register custom taxonomy
	register_taxonomy(
		'ad-position',
		'ad-content',
		[
			'label'             => __( 'Positions', 'taf' ),
			'labels'            => [
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
			],
			'show_admin_column' => true,
			'hierarchical'      => false,
			'meta_box_cb'       => function( $post ) {
				$terms = get_the_terms( $post, 'ad-position' );
				$tags  = [];
				if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$tags[] = $term->name;
					}
				}
				$all_terms = get_terms( 'ad-position', [ 'hide_empty' => false ] );
				?>
				<input type="hidden" name="tax_input[ad-position]" id="ad-position-saver"
					   value="<?= esc_attr( implode( ',', $tags ) ) ?>"/>
				<script>
                  (function(){
                    jQuery(document).ready(function($){
                      $('.adPosition__check').click(function(){
                        var value = [];
                        $('.adPosition__check:checked').each(function(index, input){
                          value.push($(input).val());
                        });
                        $('#ad-position-saver').val(value.join(','));
                      });
                    });
                  })();
				</script>
				<?php if ( empty( $all_terms ) || is_wp_error($all_terms  ) ) : ?>
					<p style="color: red;">
						<?php esc_html_e( 'No position found.', 'taf' ) ?>
					</p>
				<?php else : ?>
					<div class="adPosition">
						<?php foreach ( $all_terms as $term ) : ?>
							<div class="adPosition__item">
								<label class="adPosition__label">
									<input type="checkbox" class="adPosition__check" value="<?= esc_attr( $term->name ) ?>" <?php checked( has_term( $term->term_id, $term->taxonomy, $post ) ) ?>/>
									<?= esc_html( $term->name ) ?>
								</label>
								<p class="adPosition__description">
									<?= esc_html( $term->description ) ?>
								</p>
							</div>
						<?php endforeach; ?>
						<div style="clear: left;"></div>
						<hr/>
						<p class="adPosition__info">
							<?php esc_html_e( 'If you select multiple position, same block will be displayed in multiple places.', 'taf' ) ?>
						</p>
					</div>
				<?php endif;

			},
		]
	);
} );

/**
 * Show notice
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();

	if ( false !== array_search( $screen->id, [ 'edit-ad-position' ] ) && taf_default_positions() ) {
		foreach ( taf_default_positions() as $slug => $term ) {
			$name = isset( $term['name'] ) ? $term['name'] : $slug;
			$desc = isset( $term['description'] ) ? $term['description'] : '';
			$exist = get_term_by( 'slug', $slug, 'ad-position' );
			if ( is_wp_error( $exist ) || ! $exist ) {
				wp_insert_term( $name, 'ad-position', [
					'slug' => $slug,
					'description' => $desc,
				] );
			}
		}
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Notice:', 'taf' ) ?></strong>
				<?php esc_html_e( 'Default positions are registered from theme or plugin. Changing them may cause unexpected result.', 'taf' ) ?>
			</p>
		</div>
		<?php
	}
} );

/**
 * Add column for taxonomy
 */
add_filter( 'manage_edit-ad-position_columns', function ( $columns ) {
	$columns['registered'] = __( 'Registered', 'taf' );

	return $columns;
} );

/**
 * Show column content for taxonomy
 */
add_filter( 'manage_ad-position_custom_column', function ( $value, $column, $term_id ) {
	if ( taf_is_registered( $term_id ) ) {
		return '<span class="dashicons dashicons-thumbs-up" style="color: #4b9b6d;"></span>';
	} else {
		return '<span class="dashicons dashicons-thumbs-down" style="color: darkgrey;"></span>';
	}
}, 10, 3 );

/**
 * Show form notice
 */
add_action( 'ad-position_edit_form_fields', function ( $term ) {
	?>
	<tr>
		<th><?php esc_html_e( 'Registered', 'taf' ) ?></th>
		<td>
			<?php if ( taf_is_registered( $term ) ) : ?>
				<p style="color: #4b9b6d;">
					<span class="dashicons dashicons-thumbs-up"></span>
					<?php esc_html_e( 'This position is registered for themes.', 'taf' ) ?>
				</p>
			<?php else : ?>
				<p style="color: #d93d2e;">
					<span class="dashicons dashicons-thumbs-down"></span>
					<?php esc_html_e( 'This position is not registered for themes.', 'taf' ) ?>
				</p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
} );


// Display help menu
add_action( 'edit_form_after_title', function( $post ) {
	if ( 'ad-content' != $post->post_type ) {
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
		<button class="adContent-toggle button"><?php esc_html_e( 'Open Help of Taro Ad Fields', 'taf' ) ?></button>
		<dl class="adContent-list">
			<dt><?php esc_html_e( 'What will be displayed', 'taf' ) ?></dt>
			<dd><?php esc_html_e( 'The content in editor below.', 'taf' ) ?></dd>
			<dd><?php esc_html_e( 'The content in "Raw Content" meta box. They will never be escaped, so you can use Javascripts for ads.', 'taf' ) ?></dd>
			<dt><?php esc_html_e( 'How To Preview', 'taf' ) ?></dt>
			<dd><?php esc_html_e( 'Set this ad field\'s publish date to future.', 'taf' ) ?></dd>
			<dd><?php esc_html_e( 'Then publish. This ad becomes future post.', 'taf' ) ?></dd>
			<dd><?php
				$url = add_query_arg( [ 'taf_preview' => 'true' ], home_url('/') );
				printf(
					esc_html__( 'Access desired page with query paramete "taf_preview=true". If this ad will be displayed on top page, go to %s.', 'taf' ),
					sprintf(
						'<a href="%s" target="_blank">%s</a>',
						$url, esc_html( $url )
					)
				)
				?></dd>
			<dd>
				<?php esc_html_e( 'Don\'t forget to change status of this ad after confirmation.', 'taf' ) ?>
			</dd>
			<dt><?php esc_html_e( 'Field Expiration', 'taf' ) ?></dt>
			<dd>
				<?php printf( esc_html__( 'If you want ads to be automatically expired, please consider %s!', 'taf' ), '<a href="https://ja.wordpress.org/plugins/taro-clockwork-post/" target="_blank">Taro Clockwork Post</a>' ) ?>
			</dd>
		</dl>
	</div>
	<?php
} );

// Show notices
add_action( 'admin_notices', function() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	$terms = get_terms( [
		'taxonomy' => 'ad-position',
		'hide_empty' => false,
	] );
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
 * Add query var for preview
 */
add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'taf_preview';
	return $vars;
} );

// No cache if this is preview
add_action( 'template_redirect', function() {
	if ( 'true' === get_query_var( 'taf_preview' ) ) {
		nocache_headers();
	}
} );


// Register widget
add_action( 'widgets_init', function() {
	register_widget( 'TafWidget' );
} );