<?php

namespace Tarosky\TaroAdFieldsTest;


/**
 * Test bootstrap.
 */
class Bootstrap {

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		add_filter( 'taf_default_positions', [ $this, 'default_positions' ] );
		add_action( 'wp_after_load_template', [ $this, 'render_after_content' ] );
		add_action( 'wp_body_open', [ $this, 'body_open' ], 10000 );
		add_action( 'wp_head', [ $this, 'wp_head' ] );
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );
	}

	/**
	 * Resister default positions
	 *
	 * @return array
	 */
	public function default_positions() {
		return [
			'head' => [
				'name'        => 'Inside of head tag',
				'description' => 'Displayed inside head tag. Maximum 2 ads.',
				'mode'        => '',
				'contexts'   => [ 'device' ],
			],
			'after_content' => [
				'name'        => 'After Content',
				'description' => 'Displayed after content in singular page. Maximum 2 ads. (Only works if theme contains a template named content.)',
				'mode'        => '',
			],
			'body_open' => [
				'name'        => 'Body Open',
				'description' => 'Just after body open tag. Maximum 3 ads.',
				'mode'        => '',
				'contexts'   => [ 'device' ],
			],
			'in_footer' => [
				'name'        => 'Footer',
				'description' => 'In Footer. Maximum 1 ad',
				'mode'        => '',
				'contexts'   => [ 'device' ],
			],
			'iframe' => [
				'name'        => 'iframe Ad',
				'description' => 'Displayed as iframe.',
				'mode'        => 'iframe',
			],
		];
	}

	/**
	 * Render ad field just after main content.
	 *
	 * @param string $template_file Template file path.
	 */
	public function render_after_content( $template_file ) {
		$parts = pathinfo( $template_file );
		if ( $parts[ 'filename' ] === 'content' && ( $parts[ 'extension' ] === 'php' || $parts[ 'extension' ] === 'html' ) ) {
			echo taf_render( 'after_content', '<aside class="hentry taf-after-content" style="padding: 20px; box-sizing: border-box;">', '</aside>', 2 );
		}
	}

	/**
	 * Render ad fields in head tag.
	 *
	 * @return void
	 */
	public function wp_head() {
		$contexts = [ 'all-device' ];
		$contexts[] = wp_is_mobile() ? 'mobile' : 'desktop';
		echo taf_render( 'head', '', '', 2, $contexts );
	}

	/**
	 * Render ad fields in footer.
	 *
	 * @return void
	 */
	public function wp_footer() {
		$contexts = [ 'all-device' ];
		$contexts[] = wp_is_mobile() ? 'mobile' : 'desktop';
		echo taf_render( 'in_footer', '', '', 1, $contexts );
	}

	/**
	 * Render body open.
	 *
	 * @return void
	 */
	public function body_open() {
		$contexts = [ 'all-device' ];
		$contexts[] = wp_is_mobile() ? 'mobile' : 'desktop';
		echo taf_render( 'body_open', '', '', 3, $contexts );
	}
}
