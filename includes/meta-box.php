<?php
/**
 * Meta box related functions
 */

// Enqueue Style
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'taf-admin-style', plugin_dir_url( __DIR__ ) . 'assets/css/admin.css', array(), taro_ad_version() );
} );

// Register meta box
add_action( 'edit_form_after_title', function ( WP_Post $post ) {
	if ( 'ad-content' !== $post->post_type ) {
		return;
	}
	// Enqueue code editor and settings for manipulating HTML.
	$settings = wp_enqueue_code_editor( [
		'type' => 'text/html',
	] );

	// Return if the editor was not enqueued.
	if ( false === $settings ) {
		return;
	}
	$js = <<<'JS'
			jQuery( function() { wp.codeEditor.initialize( "taf_content", %s ); } );
JS;
	wp_add_inline_script( 'code-editor', sprintf( $js, wp_json_encode( $settings ) ) );

	wp_nonce_field( 'taf_meta', '_tafnonce', false );
	?>
	<div class="adMeta__container">
		<h2 class="adMeta__title"><?php esc_html_e( 'Raw HTML(JavaScript, CSS, iframe)', 'taf' ) ?></h2>
		<p class="adMeta__desc">
			<?php esc_html_e( 'Enter HTML code below. This value will be output without escape. Most of ad code is acceptable.', 'taf' ); ?>
		</p>
		<textarea class="adMeta__textarea" name="taf_content"
			id="taf_content"><?php echo esc_textarea( get_post_meta( $post->ID, '_taf_content', true ) ); ?></textarea>
	</div>

	<hr class="adMeta__line" />

	<h2 class="adMeta__title"><?php esc_html_e( 'Visual Editor', 'taf' ) ?></h2>
	<p class="adMeta__desc">
		<?php esc_html_e( 'If you need additional HTML with visual editor, enter below field. ', 'taf' ); ?>
	</p>
	<?php
}, 11 );

// Save custom field
add_action( 'save_post', function ( $post_id, $post ) {
	if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
		return;
	}
	if ( ! isset( $_POST['_tafnonce'] ) || ! wp_verify_nonce( $_POST['_tafnonce'], 'taf_meta' ) ) {
		return;
	}
	// Do not sanitize html because it allows javascript to output.
	update_post_meta( $post_id, '_taf_content', $_POST['taf_content'] );
}, 10, 2 );
