/**
 * Mutable template registry for CF7 Apps admin UI.
 *
 * Free registers free-only templates. CF7 Apps Pro registers Pro templates
 * at runtime via window.CF7AppsRegisterTemplate (or registerTemplate export).
 */

const CF7AppsTemplates =
	( typeof window !== 'undefined' && window.CF7AppsTemplateRegistry ) || {};

if ( typeof window !== 'undefined' ) {
	window.CF7AppsTemplateRegistry = CF7AppsTemplates;
}

export function registerTemplate( key, component ) {
	if ( ! key || ! component ) {
		return;
	}
	CF7AppsTemplates[ key ] = component;
	if ( typeof window !== 'undefined' ) {
		window.CF7AppsTemplateRegistry = CF7AppsTemplates;
		window.dispatchEvent(
			new CustomEvent( 'cf7apps-template-registered', {
				detail: { key },
			} )
		);
	}
}

export function getTemplate( key ) {
	if ( ! key ) {
		return undefined;
	}
	const registry =
		( typeof window !== 'undefined' && window.CF7AppsTemplateRegistry ) ||
		CF7AppsTemplates;
	return registry[ key ];
}

export function hasTemplate( key ) {
	return typeof getTemplate( key ) !== 'undefined';
}

if ( typeof window !== 'undefined' ) {
	window.CF7AppsRegisterTemplate = registerTemplate;
}

// Free-only templates.
registerTemplate( 'HoneypotForms', require( './honeypot/HoneypotForms.js' ).default );
registerTemplate( 'cf7Entries', require( './cf7Entries/cf7Entries.js' ).default );
registerTemplate( 'cf7EntriesSettings', require( './cf7Entries/cf7EntriesSettings.js' ).default );
registerTemplate( 'cf7EntriesTable', require( './cf7Entries/cf7EntriesTable.js' ).default );
registerTemplate( 'weeklySummaryAdmin', require( './weeklySummary/WeeklySummaryAdmin.js' ).default );

export default CF7AppsTemplates;
