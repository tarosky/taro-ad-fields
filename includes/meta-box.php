<?php
/**
 * Meta box related functions
 */

// Enqueue Style
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'taf-admin-style', plugin_dir_url( __DIR__ ) . 'assets/css/admin.css', array(), taro_ad_version() );
} );

// Register meta box
add_action( 'add_meta_boxes', function ( $post_type ) {
	if ( 'ad-content' !== $post_type ) {
		return;
	}
	add_meta_box( 'ad-content', __( 'Raw Content', 'taf' ), function ( $post ) {
		wp_nonce_field( 'taf_meta', '_tafnonce', false );
		wp_enqueue_script( 'ace-editor', plugin_dir_url( __DIR__ ) . 'assets/lib/ace/ace.js', array( 'jquery' ), '1.2.8', true );
		$js = <<<JS
		(function(){
			var editor = ace.edit("taf-editor");
    		editor.setTheme("ace/theme/xcode");
    		editor.getSession().setMode("ace/mode/html");
    		jQuery(document).ready(function($){
    			editor.getSession().setValue($('#taf_content').val());
				editor.getSession().on('change', function(){
					$('#taf_content').val(editor.getSession().getValue());
				});
    		});
		})();
JS;
		wp_add_inline_script( 'ace-editor', $js );
		?>
		<div id="taf-editor" class="adMeta__editor"></div>
		<textarea class="adMeta__textarea" name="taf_content"
					id="taf_content"><?php echo esc_textarea( get_post_meta( $post->ID, '_taf_content', true ) ); ?></textarea>
		<p class="adMeta__desc">
			<?php esc_html_e( 'If you need Javascript, enter here. Contents will be displayed without escape.', 'taf' ); ?>
		</p>
		<?php
	}, $post_type, 'normal', 'high' );
} );

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
