/**
 * Window-backed Entries Pro settings bridge (free + Pro bundles must use these exact keys).
 */
export const ENTRIES_PRO_LIVE_WINDOW_KEY = '__cf7appsEntriesProSettingsLive';
export const ENTRIES_PRO_COLLECTOR_WINDOW_KEY =
	'__cf7appsCollectEntriesProSettingsForSave';

/**
 * @param {object|null} settings
 */
export function publishEntriesProSettingsToWindow( settings ) {
	if ( typeof window === 'undefined' ) {
		return;
	}

	if ( settings && typeof settings === 'object' ) {
		window[ ENTRIES_PRO_LIVE_WINDOW_KEY ] = settings;
	} else {
		delete window[ ENTRIES_PRO_LIVE_WINDOW_KEY ];
	}
}

/**
 * @param {Function|null} collector
 */
export function registerEntriesProWindowCollector( collector ) {
	if ( typeof window === 'undefined' ) {
		return;
	}

	if ( typeof collector === 'function' ) {
		window[ ENTRIES_PRO_COLLECTOR_WINDOW_KEY ] = collector;
		const live = collector();
		if ( live && typeof live === 'object' ) {
			publishEntriesProSettingsToWindow( live );
		}
		return;
	}

	delete window[ ENTRIES_PRO_COLLECTOR_WINDOW_KEY ];
}

/**
 * @return {object|null}
 */
export function readEntriesProSettingsFromWindow() {
	if ( typeof window === 'undefined' ) {
		return null;
	}

	const collector = window[ ENTRIES_PRO_COLLECTOR_WINDOW_KEY ];
	if ( typeof collector === 'function' ) {
		const live = collector();
		if ( live && typeof live === 'object' ) {
			return live;
		}
	}

	const cached = window[ ENTRIES_PRO_LIVE_WINDOW_KEY ];
	if ( cached && typeof cached === 'object' ) {
		return cached;
	}

	return null;
}
