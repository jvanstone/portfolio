import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CF7AppsProToggleTeaser from '../../components/CF7AppsProToggleTeaser';
import Cf7WebhookFieldSelector from './cf7WebhookFieldSelector';

/**
 * Selective Webhook Data — Pro accordion or free PRO teaser (Figma).
 */
const Cf7WebhookProSettings = ( { appSettings, formData = {}, setFormData } ) => {
	const hasPro = !! appSettings?.has_pro;
	const [ open, setOpen ] = useState( true );

	if ( ! hasPro ) {
		return (
			<div className="cf7apps-webhook-pro-settings">
				<CF7AppsProToggleTeaser
					label={ __( 'Selective Webhook Data', 'cf7apps' ) }
					collapsible
				/>
			</div>
		);
	}

	const fieldOptions = appSettings.webhook_field_options || {};
	const selectedFields = Array.isArray( formData.selected_fields )
		? formData.selected_fields
		: [];
	const mode = formData.selected_fields_mode || 'all';

	const update = ( patch ) => {
		if ( typeof setFormData !== 'function' ) {
			return;
		}
		setFormData( ( prev ) => ( { ...prev, ...patch } ) );
	};

	return (
		<div className="cf7apps-webhook-pro-settings">
			<div className="cf7apps-webhook-selective">
				<button
					type="button"
					className="cf7apps-settings-collapsible-trigger"
					onClick={ () => setOpen( ! open ) }
					aria-expanded={ open }
				>
					<label>
						<span>{ __( 'Selective Webhook Data', 'cf7apps' ) }</span>
					</label>
					<span
						className={ `cf7apps-collapsible-chevron${ open ? ' is-open' : '' }` }
						aria-hidden="true"
					/>
				</button>
				{ open && (
					<div className="cf7apps-app-collapsible-panel">
						<Cf7WebhookFieldSelector
							fieldOptions={ fieldOptions }
							selectedFields={ selectedFields }
							mode={ mode }
							showHeader={ true }
							onChange={ ( { selectedFields: nextFields, mode: nextMode } ) => {
								update( {
									selected_fields: nextFields,
									selected_fields_mode: nextMode,
								} );
							} }
						/>
						<p className="cf7apps-help-text cf7apps-webhook-selective__note">
							{ __(
								'No fields selected. All form data will be included from the payload.',
								'cf7apps'
							) }
						</p>
					</div>
				) }
			</div>
		</div>
	);
};

export default Cf7WebhookProSettings;
