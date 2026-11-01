import { __ } from '@wordpress/i18n';

const isWebhookEnabled = ( item ) =>
	item && item.enabled !== false;

/**
 * Validate enabled webhook endpoints (multi-card or legacy single URL).
 *
 * @param {object} formData Webhook settings payload.
 * @return {{ valid: boolean, message?: string }}
 */
export const validateWebhookUrls = ( formData ) => {
	const webhooks = Array.isArray( formData?.webhooks ) ? formData.webhooks : [];
	const activeCards = webhooks.filter( ( item ) => isWebhookEnabled( item ) );

	if ( activeCards.length ) {
		const missingUrl = activeCards.find(
			( item ) => ! String( item.webhook_url || '' ).trim()
		);
		if ( missingUrl ) {
			return {
				valid: false,
				message: __( 'Webhook URL cannot be empty.', 'cf7apps' ),
			};
		}

		const invalid = activeCards.find( ( item ) => {
			const url = String( item.webhook_url || '' ).trim();
			return (
				! url.startsWith( 'http://' ) && ! url.startsWith( 'https://' )
			);
		} );
		if ( invalid ) {
			return {
				valid: false,
				message: __(
					'Webhook URL must start with http:// or https://',
					'cf7apps'
				),
			};
		}

		const seen = new Set();
		const hasDuplicate = activeCards.some( ( item ) => {
			const url = String( item.webhook_url || '' )
				.trim()
				.replace( /\/+$/, '' )
				.toLowerCase();
			const method = String( item.method || 'POST' ).toUpperCase();
			const key = `${ method }|${ url }`;
			if ( seen.has( key ) ) {
				return true;
			}
			seen.add( key );
			return false;
		} );
		if ( hasDuplicate ) {
			return {
				valid: false,
				message: __(
					'Duplicate webhooks found. Each webhook URL and method combination must be unique.',
					'cf7apps'
				),
			};
		}

		return { valid: true };
	}

	const webhookUrl = formData?.webhook_url
		? String( formData.webhook_url ).trim()
		: '';

	if ( webhookUrl === '' ) {
		return {
			valid: false,
			message: __( 'Webhook URL cannot be empty.', 'cf7apps' ),
		};
	}

	if (
		! webhookUrl.startsWith( 'http://' ) &&
		! webhookUrl.startsWith( 'https://' )
	) {
		return {
			valid: false,
			message: __(
				'Webhook URL must start with http:// or https://',
				'cf7apps'
			),
		};
	}

	return { valid: true };
};
