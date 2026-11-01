import { __ } from '@wordpress/i18n';
import { saveEntriesProSettings } from '../api/entriesProApi';
import { getCf7AppsRuntime } from './appCardTheme';
import {
	publishEntriesProSettingsToWindow,
	readEntriesProSettingsFromWindow,
} from './entriesProWindowBridge';

const BRIDGE_GLOBAL_KEY = '__cf7appsEntriesProBridge';
export const ENTRIES_PRO_SETTINGS_LIVE_KEY = '__cf7appsEntriesProSettingsLive';
export const ENTRIES_PRO_COLLECTOR_KEY = '__cf7appsCollectEntriesProSettingsForSave';
export const ENTRIES_PRO_SETTINGS_REQUEST_EVENT = 'cf7apps-request-entries-pro-settings';
export const ENTRIES_PRO_SETTINGS_RESPONSE_EVENT = 'cf7apps-entries-pro-settings-response';

/**
 * @param {object|null} settings
 */
function rememberEntriesProSettings( settings ) {
	if ( settings && typeof settings === 'object' ) {
		registerEntriesProSettingsSnapshot( settings );
	}
}

/**
 * @return {object|null}
 */
function readLiveCollectorSettings() {
	if ( typeof window === 'undefined' ) {
		return null;
	}

	const collector = window[ ENTRIES_PRO_COLLECTOR_KEY ];
	if ( typeof collector === 'function' ) {
		const live = collector();
		if ( live && typeof live === 'object' ) {
			rememberEntriesProSettings( live );
			return live;
		}
	}

	return null;
}

/**
 * Shared store across free + Pro admin bundles (separate webpack builds).
 *
 * @return {{ saveHandler: Function|null, settingsSnapshot: object|null, lastSaveError: string }}
 */
function getStore() {
	if ( typeof window !== 'undefined' ) {
		if ( ! window[ BRIDGE_GLOBAL_KEY ] ) {
			window[ BRIDGE_GLOBAL_KEY ] = {
				saveHandler: null,
				settingsSnapshot: null,
				lastSaveError: '',
			};
		}
		return window[ BRIDGE_GLOBAL_KEY ];
	}

	return {
		saveHandler: null,
		settingsSnapshot: null,
		lastSaveError: '',
	};
}

/**
 * Publish live Entries Pro settings for the free dashboard save button.
 *
 * @param {object|null} settings Settings object or null to clear.
 */
export function publishEntriesProSettingsLive( settings ) {
	if ( typeof window === 'undefined' ) {
		return;
	}

	if ( settings && typeof settings === 'object' ) {
		window[ ENTRIES_PRO_SETTINGS_LIVE_KEY ] = settings;
	} else {
		delete window[ ENTRIES_PRO_SETTINGS_LIVE_KEY ];
	}
}

/**
 * Register a save callback for Entries Pro settings.
 *
 * @param {Function|null} handler Async save function.
 */
export function registerEntriesProSaveHandler( handler ) {
	getStore().saveHandler = typeof handler === 'function' ? handler : null;
}

/**
 * Keep the latest Entries Pro settings for save (avoids stale closures).
 *
 * @param {object|null} settings Settings object or null to clear.
 */
export function registerEntriesProSettingsSnapshot( settings ) {
	const next =
		settings && typeof settings === 'object' ? settings : null;
	getStore().settingsSnapshot = next;
	publishEntriesProSettingsLive( next );
	publishEntriesProSettingsToWindow( next );
}

/**
 * @return {object|null}
 */
export function getEntriesProSettingsSnapshot() {
	const fromWindow = readEntriesProSettingsFromWindow();
	if ( fromWindow ) {
		return fromWindow;
	}

	const fromCollector = readLiveCollectorSettings();
	if ( fromCollector ) {
		return fromCollector;
	}

	if (
		typeof window !== 'undefined' &&
		window[ ENTRIES_PRO_SETTINGS_LIVE_KEY ] &&
		typeof window[ ENTRIES_PRO_SETTINGS_LIVE_KEY ] === 'object'
	) {
		return window[ ENTRIES_PRO_SETTINGS_LIVE_KEY ];
	}

	return getStore().settingsSnapshot;
}

/**
 * Synchronous read used by the dashboard Save button.
 *
 * @return {object|null}
 */
export function readEntriesProSettingsForSave() {
	return getEntriesProSettingsSnapshot();
}

/**
 * Register a global collector (Pro panel) for the free dashboard save button.
 *
 * @param {Function|null} collector
 */
export function registerEntriesProSettingsCollector( collector ) {
	if ( typeof window === 'undefined' ) {
		return;
	}

	if ( typeof collector === 'function' ) {
		window[ ENTRIES_PRO_COLLECTOR_KEY ] = collector;
		const live = collector();
		if ( live && typeof live === 'object' ) {
			rememberEntriesProSettings( live );
		}
		return;
	}

	delete window[ ENTRIES_PRO_COLLECTOR_KEY ];
}

/**
 * @return {string}
 */
export function getEntriesProLastSaveError() {
	return getStore().lastSaveError || '';
}

/**
 * @param {*} result Save handler / REST result.
 * @return {boolean}
 */
function isSaveSuccess( result ) {
	if ( result === false || result === null ) {
		return false;
	}
	if ( result && typeof result === 'object' && result.success === false ) {
		return false;
	}
	return true;
}

/**
 * Read the latest Pro settings from window / bridge / Pro panel event.
 *
 * @return {Promise<object|null>}
 */
export function collectEntriesProSettingsForSave() {
	const immediate = readEntriesProSettingsForSave();
	if ( immediate ) {
		return Promise.resolve( immediate );
	}

	if ( typeof window === 'undefined' ) {
		return Promise.resolve( null );
	}

	const fromCollector = readLiveCollectorSettings();
	if ( fromCollector ) {
		return Promise.resolve( fromCollector );
	}

	return new Promise( ( resolve ) => {
		let settled = false;
		const finish = ( value ) => {
			if ( settled ) {
				return;
			}
			settled = true;
			window.removeEventListener(
				ENTRIES_PRO_SETTINGS_RESPONSE_EVENT,
				onResponse
			);
			resolve( value || getEntriesProSettingsSnapshot() );
		};

		const onResponse = ( event ) => {
			const detail =
				event && event.detail && typeof event.detail === 'object'
					? event.detail
					: null;
			if ( detail ) {
				registerEntriesProSettingsSnapshot( detail );
			}
			finish( detail );
		};

		window.addEventListener(
			ENTRIES_PRO_SETTINGS_RESPONSE_EVENT,
			onResponse
		);
		window.dispatchEvent(
			new CustomEvent( ENTRIES_PRO_SETTINGS_REQUEST_EVENT )
		);

		window.setTimeout(
			() =>
				finish(
					readLiveCollectorSettings() ||
						getEntriesProSettingsSnapshot()
				),
			300
		);
	} );
}

/**
 * @return {boolean}
 */
export function isEntriesProSettingsPanelMounted() {
	if ( typeof document === 'undefined' ) {
		return false;
	}

	return !! document.querySelector( '.cf7apps-entries-pro-settings' );
}

/**
 * Persist Entries Pro settings if a handler is registered.
 *
 * @param {object|null} snapshot Optional explicit snapshot.
 * @return {Promise<boolean>}
 */
export async function saveRegisteredEntriesProSettings( snapshot = null ) {
	const store = getStore();
	store.lastSaveError = '';

	const resolved =
		snapshot && typeof snapshot === 'object'
			? snapshot
			: getEntriesProSettingsSnapshot();
	const { saveHandler } = store;

	if ( saveHandler ) {
		const handlerResult = await saveHandler( resolved );
		if ( isSaveSuccess( handlerResult ) ) {
			return true;
		}
	}

	if ( resolved && getCf7AppsRuntime().hasPro ) {
		const restResult = await saveEntriesProSettings( resolved );
		if ( isSaveSuccess( restResult ) ) {
			return true;
		}
		store.lastSaveError =
			( restResult && restResult.message ) ||
			__( 'Failed to save Entries Pro settings.', 'cf7apps' );
		return false;
	}

	return true;
}
