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
	}

}
