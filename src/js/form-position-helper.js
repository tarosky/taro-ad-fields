/*!
 * Form helper in position edit screen.
 *
 * @handle taf-form-position-helper
 * @deps wp-i18n
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

document.addEventListener( 'DOMContentLoaded', function() {
	const container = document.createElement( 'div' );
	container.style.marginTop = '40px';
	if ( data.displayMode ) {
		container.classList.add( 'taf-iframe-container' );

		container.innerHTML = `
			<hr>
			<h2>${ __( 'Iframe URL', 'taf' ) }</h2>
			<p style="margin: 1em 0;" class="description">${ __(
		'This is the iframe URL along with an example of how to use it. You can adjust the attributes (such as size or styling) to fit your specific use case.',
		'taf'
	) }</p>
			<div style="margin: 1em 0;">
				<input type="url" style="width: 100%; box-sizing: border-box" readonly="" value="${
	data.iframeUrl
}" onfocus="this.select()">
			</div>
			<div style="margin: 1em 0;">
				<input type="url" style="width: 100%; box-sizing: border-box" readonly="" value="${
	data.iframeElement
}" onfocus="this.select()">
			</div>
		`;
	} else {
		container.innerHTML = `
			<hr>
			<h2><?php esc_html_e( 'Iframe URL', 'taf' ); ?></h2>
			<p style="margin: 1em 0;" class="description">${ __(
		'The iframe URL will appear here when the Display Mode is set to "iframe". If the Display Mode is set to "Default", this message will be shown instead.',
		'taf'
	) }</p>
		`;
	}

	// Add below the form
	const form = document.querySelector( 'form#edittag' );
	if ( form && form.parentNode ) {
		form.parentNode.appendChild( container );
	}
} );
