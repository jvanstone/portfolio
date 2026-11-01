import * as ReactToastify from 'react-toastify';

/**
 * Pro admin bundle must use the same react-toastify instance as the free shell
 * (ToastContainer lives in the free/internal-settings entry). Webpack externals
 * read window.CF7AppsReactToastify for Pro imports of 'react-toastify'.
 */
export function registerCf7appsReactToastifyGlobal() {
	if ( typeof window !== 'undefined' ) {
		window.CF7AppsReactToastify = ReactToastify;
	}
}

export const toast = ReactToastify.toast;
export { ToastContainer } from 'react-toastify';
