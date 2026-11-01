import { getCf7AppsRuntime } from '../utils/appCardTheme';

function getRestHeaders() {
	const runtime = getCf7AppsRuntime();
	return {
		'X-WP-Nonce': runtime.nonce,
		'Content-Type': 'application/json',
	};
}

function getProRestBase() {
	const runtime = getCf7AppsRuntime();
	let base = runtime.proRestBase || '';

	if ( ! base ) {
		const freeBase = runtime.restURL || '';
		if ( freeBase.indexOf( 'rest_route=' ) !== -1 ) {
			return freeBase.replace( /cf7apps\/v1\/?$/, 'cf7apps-pro/v1/' );
		}
		return `${ freeBase.replace( /\/?$/, '/' ) }cf7apps-pro/v1/`;
	}

	return base.endsWith( '/' ) ? base : `${ base }/`;
}

function getProRestUrl( endpoint = '' ) {
	return `${ getProRestBase() }${ String( endpoint ).replace( /^\/+/, '' ) }`;
}

function unwrapRestPayload( json ) {
	if ( ! json || typeof json !== 'object' ) {
		return false;
	}
	return json.data !== undefined ? json.data : json;
}

/**
 * Persist Entries Pro settings (auto-delete, export, restriction).
 *
 * @param {object} settings Pro settings payload.
 * @return {Promise<object|boolean>}
 */
export async function saveEntriesProSettings( settings ) {
	if ( ! settings || typeof settings !== 'object' ) {
		return false;
	}

	const runtime = getCf7AppsRuntime();
	if ( ! runtime.hasPro ) {
		return true;
	}

	const response = await fetch( getProRestUrl( 'entries-pro-settings' ), {
		headers: getRestHeaders(),
		method: 'POST',
		body: JSON.stringify( { settings } ),
	} );

	const json = await response.json().catch( () => null );

	if ( ! response.ok ) {
		const message =
			json?.message || json?.data?.message || json?.code || false;
		return message ? { success: false, message } : false;
	}

	const payload = unwrapRestPayload( json );
	return payload || false;
}
