import { __ } from '@wordpress/i18n';
import CF7AppsProToggleTeaser from '../../components/CF7AppsProToggleTeaser';

/**
 * Free-only: Multiple Webhooks teaser (shown near top of Webhook settings).
 */
const Cf7WebhookProTeasers = () => (
	<div className="cf7apps-webhook-pro-teasers">
		<CF7AppsProToggleTeaser
			label={ __( 'Enable Multiple Webhooks', 'cf7apps' ) }
			help={ __(
				'Send form data to multiple webhook endpoints from each form’s CF7 Apps Webhook settings. Upgrade to PRO to unlock this feature.',
				'cf7apps'
			) }
		/>
	</div>
);

/**
 * Free-only: Failure Notifications teaser (Figma — after webhook config).
 */
export const Cf7WebhookFailureTeaser = () => (
	<div className="cf7apps-webhook-pro-teasers cf7apps-webhook-pro-teasers--failure">
		<CF7AppsProToggleTeaser
			label={ __( 'Enable Failure Notifications', 'cf7apps' ) }
			help={ __(
				'Receive email notifications when a Webhook fails.',
				'cf7apps'
			) }
		/>
	</div>
);

export default Cf7WebhookProTeasers;
