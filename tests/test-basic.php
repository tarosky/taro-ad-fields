<?php
/**
 * Function test
 *
 * @package tsoh
 */

/**
 * Sample test case.
 */
class Taf_Basic_Test extends WP_UnitTestCase {

	/**
	 * Test functions
	 */
	function test_functions() {
		$this->assertEquals( [], taf_default_positions() );
		$this->assertFalse( taf_is_registered( 'non-existing-term' ) );
		$this->assertEmpty( taf_render( 'non-existing-position' ) );
		$this->assertEmpty( taf_iframe_url( 'no-exsisting-position' ) );
	}

	function test_position() {
		// Create Terms
		add_filter( 'taf_default_positions', function() {
			return [
				'after_title' => [
					'name'        => 'After Title',
					'description' => 'This is a position.',
					'mode'        => 'iframe',
				],
			];
		} );
		$result = taf_register_positions();
		$this->assertEquals( 1, $result );
		// Check existence
		$term = get_term_by( 'slug', 'after_title', 'ad-position' );
		$this->assertTrue( $term || ! is_wp_error( $term ) );
		$this->assertEquals( 'iframe', get_term_meta( $term->term_id, 'taf_display_mode', true ) );

		// Check clear method.
		$this->assertTrue( taf_clear_terms() );


	}



}
