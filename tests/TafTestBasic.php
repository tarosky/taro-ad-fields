<?php
/**
 * Function test
 *
 * @package tsoh
 */

/**
 * Sample test case.
 */
class TafTestBasic extends WP_UnitTestCase {

	/**
	 * Test functions
	 */
	function test_functions() {
		$this->assertEquals( 5, count( taf_default_positions() ) );
		$this->assertFalse( taf_is_registered( 'non-existing-term' ) );
		$this->assertEmpty( taf_render( 'non-existing-position' ) );
		$this->assertEmpty( taf_iframe_url( 'no-existing-position' ) );
	}

	function test_position() {
		$result = taf_register_positions();
		$this->assertEquals( 5, $result );
		// Check existence
		$term = get_term_by( 'slug', 'iframe', 'ad-position' );
		$this->assertTrue( $term || ! is_wp_error( $term ) );
		$this->assertEquals( 'iframe', get_term_meta( $term->term_id, 'taf_display_mode', true ) );
		// Check clear method.
		$this->assertTrue( taf_clear_terms() );
	}
}
