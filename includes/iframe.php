<?php
/**
 * iframe features.
 *
 * @package taf
 * @since 1.1.0
 */

/**
 * Add term meta which indicates this position is for iframe.
 *
 */
add_action( 'ad-position_edit_form_fields', function ( WP_Term $tag ) {
	?>
	<tr>
		<th>
			<?php wp_nonce_field( 'taf_term_meta', '_taftermmeta', false ); ?>
			<label for="taf-term-display">
				<?php esc_html_e( 'Display Mode', 'taf' ); ?>
			</label>
		</th>
		<td>
			<?php $current_display = get_term_meta( $tag->term_id, 'taf_display_mode', true ); ?>
			<select name="taf-term-display" id="taf-term-display">
				<option value="" <?php selected( $current_display, '' ); ?>>
					<?php esc_html_e( 'Default', 'taf' ); ?>
				</option>
				<option value="iframe" <?php selected( $current_display, 'iframe' ); ?>>
					iframe
				</option>
			</select>
			<p class="description">
				<?php esc_html_e( 'If set to iframe, this fields has URL and simple html pages.', 'taf' ); ?>
			</p>
		</td>
	</tr>
	<?php
}, 11 );

/**
 * Display iframe URL on edit page.
 */
add_action('ad-position_edit_form_fields', function ( $term ) {
	$display_mode = get_term_meta( $term->term_id, 'taf_display_mode', true );

	$iframe_url     = get_term_link( $term );
	$iframe_element = '<iframe src="' . $iframe_url . '" style="width:100%; height:100%; border:0;" frameborder="0" scrolling="auto" allowfullscreen></iframe>';
	wp_enqueue_script( 'taf-form-position-helper' );
	wp_set_script_translations( 'taf-form-position-helper', 'taf' );
	wp_localize_script( 'taf-form-position-helper', 'TafIframeData', [
		'iframeUrl'     => $iframe_url,
		'iframeElement' => $iframe_element,
		'displayMode'   => (bool) $display_mode,
	] );
});

/**
 * Show notice for iframe URL
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-ad-position' !== $screen->id || ! isset( $_GET['tag_ID'] ) ) {
		return;
	}

	$term         = get_term( intval( $_GET['tag_ID'] ), 'ad-position' );
	$display_mode = get_term_meta( $term->term_id, 'taf_display_mode', true );

	if ( 'iframe' === $display_mode ) {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Notice:', 'taf' ); ?></strong>
				<?php esc_html_e( 'The iframe URL can be found at the bottom of this page.', 'taf' ); ?>
			</p>
		</div>
		<?php
	}
} );

/**
 * Save term meta
 */
add_action( 'edit_term', function ( $term_id, $term_taxonomy_id, $taxonomy ) {
	if ( wp_verify_nonce( filter_input( INPUT_POST, '_taftermmeta' ), 'taf_term_meta' ) ) {
		update_term_meta( $term_id, 'taf_display_mode', filter_input( INPUT_POST, 'taf-term-display' ) );
	}
}, 10, 3 );

/**
 * Render HTML
 */
add_action( 'pre_get_posts', function ( WP_Query &$wp_query ) {
	if ( ! $wp_query->is_main_query() || is_admin() ) {
		return;
	}
	$position = $wp_query->get( 'ad-position' );
	if ( $position ) {
		$term = get_term_by( 'slug', $position, 'ad-position' );
		if ( ! $term || is_wp_error( $term ) || 'iframe' !== get_term_meta( $term->term_id, 'taf_display_mode', true ) ) {
			$wp_query->set_404();
			return;
		}
		do_action( 'taf_before_render' );
		echo '<!doctype html>';
		?>
		<html lang="<?php language_attributes(); ?>">
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport"
					content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="ie=edge">
			<title><?php echo wp_get_document_title(); ?></title>
			<style>
				body, html{
					margin: 0;
					padding: 0;
				}
			</style>
			<?php
			/**
			 * taf_head
			 *
			 * Executed at
			 */
			do_action( 'taf_head', $term );
			$styles = array();
			foreach ( array( 'width', 'height' ) as $prop ) {
				if ( isset( $_GET[ $prop ] ) && is_numeric( $_GET[ $prop ] ) && 0 < $_GET[ $prop ] ) {
					$styles[ $prop ] = sprintf( '%dpx', $_GET[ $prop ] );
				}
			}
			$style = '';
			if ( $styles ) {
				$style = implode( ';', array_map( function ( $key, $prop ) {
					return "{$key}: {$prop}";
				}, array_keys( $styles ), array_values( $styles ) ) );
			}
			?>
		</head>
		<body style="<?php echo esc_attr( $style ); ?>">
		<?php do_action( 'taro_ad_field', $position, '', '' ); ?>
		</body>
		</html>
		<?php
		exit;
	}
} );

