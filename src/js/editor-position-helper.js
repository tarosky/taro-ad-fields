/*!
 * Editor Position Helper
 *
 * @handle taf-editor-position-helper
 * @deps jquery
 */

const $ = jQuery;

$( document ).ready( function() {
	function refreshContext() {
		// function refreshes the Contexts box.
		let empty = true;

		$( '.ad-context__container' ).each( function() {
			const $container = $( this );
			const $parent = $container
				.find( '.ad-context__heading' )
				.text()
				.trim();
			let found = false;

			// check if Position exists that requires Context and is checked.
			$( '.adPosition__item' ).each( function() {
				if (
					$( this ).find( '.button' ).text().trim() === $parent &&
					$( this ).find( '.adPosition__check' ).is( ':checked' )
				) {
					found = true;
					return false;
				}
			} );

			// Show or hide Context.
			$container.css( 'display', found ? 'block' : 'none' );
			if ( found ) {
				empty = false;
			}
		} );

		// Show message if no available Contexts.
		$( '#no-contexts-available' ).css(
			'display',
			empty ? 'block' : 'none'
		);
	}

	$( '.adPosition__check' ).click( function() {
		const value = [];
		$( '.adPosition__check:checked' ).each( function( index, input ) {
			value.push( $( input ).val() );
		} );
		$( '#ad-position-saver' ).val( value.join( ',' ) );

		refreshContext();
	} );

	refreshContext();
} );
