/*!
 * Form helper in position edit screen.
 *
 * @handle taf-form-position-helper
 * @deps wp-i18n, wp-element
 */

/* global TafIframeData:false */

/**
 * @typedef {Object} TafIframeData
 * @property {string}  iframeUrl     URL of the iframe.
 * @property {string}  iframeElement HTML element string for the iframe.
 * @property {boolean} displayMode   Display mode of the form, either 'iframe' or 'default'.
 */
/** @type {TafIframeData} */
const data = TafIframeData;

const { __ } = wp.i18n;
const { createRoot, render } = wp.element;

/**
 * Creates a container for the iframe URL and example usage.
 *
 * @param displayMode
 * @returns {JSX.Element}
 */
const TafIframeContainer = ( { displayMode } ) => {
	return (
		<div className={ displayMode ? 'taf-iframe-container' : '' } style={ { marginTop: '40px' } }>
			<hr />
			<h2>{ __( 'Iframe URL', 'taf' ) }</h2>
			<p className="description" style={ { marginTop: '1em' } }>
				{ displayMode ? __(
					'This is the iframe URL along with an example of how to use it. You can adjust the attributes (such as size or styling) to fit your specific use case.',
					'taf'
				) : __(
					'The iframe URL will appear here when the Display Mode is set to "iframe". If the Display Mode is set to "Default", this message will be shown instead.',
					'taf'
				) }
			</p>
			{ displayMode && (
				<>
					<div style={ { margin: '1em 0' } }>
						<input type="url" style={ { width: '100%', boxSizing: 'border-box' } } readOnly={ true } value={ data.iframeUrl } onFocus={ (event) => event.target.select() } />
					</div>
					<div style={ { margin: '1em 0' } }>
						<input type="url"  style={ { width: '100%', boxSizing: 'border-box' } } readOnly={ true } value={ data.iframeElement } onFocus={ (event) => event.target.select() } />
					</div>
				</>
			) }
		</div>
	);
};

// Render the iframe container when the DOM is ready.
document.addEventListener( 'DOMContentLoaded', function () {
	const container = document.createElement( 'div' );
	// Add below the form
	const form = document.querySelector( 'form#edittag' );
	if ( form && form.parentNode ) {
		form.parentNode.appendChild( container );
		const root = createRoot( container );
		root.render( <TafIframeContainer displayMode={ data.displayMode }  /> );
	}
} );
