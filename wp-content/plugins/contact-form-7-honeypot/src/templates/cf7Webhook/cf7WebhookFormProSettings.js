import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import DeleteOutlineIcon from '@mui/icons-material/DeleteOutline';
import AddIcon from '@mui/icons-material/Add';
import CF7AppsSelectField from '../../components/CF7AppsSelectField';
import CF7AppsTextField from '../../components/CF7AppsTextField';
import { getWebhookDataTypeOptions } from '../../utils/webhookUiUtils';
import Cf7WebhookFieldSelector from './cf7WebhookFieldSelector';

const createWebhook = ( defaults = {} ) => ( {
	id: `wh_${ Date.now() }_${ Math.random().toString( 36 ).slice( 2, 8 ) }`,
	enabled: true,
	webhook_url: '',
	method: 'POST',
	data_type: 'json',
	selected_fields: [],
	selected_fields_mode: 'all',
	parameter_map: {},
	headers: '{"Content-Type":"application/json"}',
	payload: '{"form_id":"{{form_id}}","fields":{{fields}}}',
	failure_notifications: false,
	...defaults,
} );

const CollapsibleSection = ( { title, children, defaultOpen = false } ) => {
	const [ open, setOpen ] = useState( defaultOpen );

	return (
		<div className="cf7apps-webhook-card-section">
			<button
				type="button"
				className="cf7apps-settings-collapsible-trigger cf7apps-webhook-card-collapsible"
				onClick={ () => setOpen( ! open ) }
				aria-expanded={ open }
			>
				<label>
					<span>{ title }</span>
				</label>
				<span
					className={ `cf7apps-collapsible-chevron${ open ? ' is-open' : '' }` }
					aria-hidden="true"
				/>
			</button>
			{ open && (
				<div className="cf7apps-app-collapsible-panel">{ children }</div>
			) }
		</div>
	);
};

/**
 * Form-level Webhook Pro settings — multi-webhook cards (Figma 4584:22594).
 */
const Cf7WebhookFormProSettings = ( { appSettings, formData = {}, setFormData } ) => {
	const hasPro = !! appSettings?.has_pro;
	const fieldOptions = appSettings?.webhook_field_options || {};
	const methodOptions = {
		POST: 'POST',
		GET: 'GET',
		PATCH: 'PATCH',
		PUT: 'PUT',
		DELETE: 'DELETE',
	};
	const disabled = ! formData.is_enabled;
	const webhooks = Array.isArray( formData.webhooks ) ? formData.webhooks : [];

	const update = ( patch ) => {
		if ( typeof setFormData !== 'function' ) {
			return;
		}
		setFormData( ( prev ) => ( { ...prev, ...patch } ) );
	};

	// Seed first card from legacy single-URL settings when empty.
	useEffect( () => {
		if ( ! hasPro || webhooks.length > 0 ) {
			return;
		}
		update( {
			webhooks: [
				createWebhook( {
					webhook_url: formData.webhook_url || '',
					method: formData.method || 'POST',
					data_type: formData.data_type || 'json',
					headers: formData.headers || '{"Content-Type":"application/json"}',
					payload:
						formData.payload ||
						'{"form_id":"{{form_id}}","fields":{{fields}}}',
					selected_fields: Array.isArray( formData.selected_fields )
						? formData.selected_fields
						: [],
					selected_fields_mode: formData.selected_fields_mode || 'all',
					failure_notifications: !! formData.failure_notifications,
				} ),
			],
		} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ hasPro ] );

	if ( ! hasPro ) {
		return null;
	}

	const updateWebhook = ( index, patch ) => {
		const next = webhooks.map( ( item, i ) =>
			i === index ? { ...item, ...patch } : item
		);
		update( { webhooks: next } );
	};

	const removeWebhook = ( index ) => {
		if ( webhooks.length <= 1 ) {
			update( {
				webhooks: [
					createWebhook( {
						failure_notifications: !! formData.failure_notifications,
					} ),
				],
			} );
			return;
		}

		update( { webhooks: webhooks.filter( ( _, i ) => i !== index ) } );
	};

	const addWebhook = () => {
		update( {
			webhooks: [
				...webhooks,
				createWebhook( {
					failure_notifications: !! formData.failure_notifications,
				} ),
			],
		} );
	};

	const examplePayload = `{\n    "your-name": "??????",\n    "your-email": "??????",\n    "your-subject": "??????",\n    "your-message": "??????"\n}`;

	return (
		<div className="cf7apps-webhook-pro-settings cf7apps-webhook-form-pro-settings">
			<div className="cf7apps-webhook-multi">
				{ webhooks.map( ( webhook, index ) => {
					const dataTypeOptions = getWebhookDataTypeOptions(
						webhook.method,
						{ json: __( 'JSON', 'cf7apps' ), form: __( 'Form Data', 'cf7apps' ) }
					);

					return (
						<div
							key={ webhook.id || index }
							className="cf7apps-webhook-multi__card is-open"
						>
							<div className="cf7apps-webhook-multi__card-top">
								<div className="cf7apps-webhook-multi__card-toggle-copy">
									<strong>
										{ __( 'Webhook', 'cf7apps' ) } - { index + 1 }
									</strong>
									<span className="cf7apps-help-text">
										{ __(
											'This determines the basis for setting the condition.',
											'cf7apps'
										) }
									</span>
								</div>
								{ webhooks.length > 1 && (
									<button
										type="button"
										className="cf7apps-webhook-multi__delete"
										disabled={ disabled }
										aria-label={ __( 'Delete webhook', 'cf7apps' ) }
										onClick={ () => removeWebhook( index ) }
									>
										<DeleteOutlineIcon fontSize="small" />
									</button>
								) }
							</div>

							<div className="cf7apps-webhook-multi__card-body">
								<CF7AppsTextField
									label={ __( 'Webhook URL', 'cf7apps' ) }
									name={ `webhook_url_${ index }` }
									placeholder={ __( 'Webhook URL', 'cf7apps' ) }
									value={ webhook.webhook_url || '' }
									disabled={ disabled }
									required={ true }
									onChange={ ( e ) =>
										updateWebhook( index, { webhook_url: e.target.value } )
									}
									variant="app"
									className="lg"
								/>

								<div className="cf7apps-webhook-method-row">
									<CF7AppsSelectField
										label={ __( 'Method', 'cf7apps' ) }
										name={ `method_${ index }` }
										selected={ webhook.method || 'POST' }
										options={ methodOptions }
										disabled={ disabled }
										onChange={ ( e ) => {
											const method = e.target.value;
											const patch = { method };
											if (
												String( method ).toUpperCase() === 'GET' &&
												webhook.data_type === 'form'
											) {
												patch.data_type = 'json';
											}
											updateWebhook( index, patch );
										} }
										variant="app"
										className="inline-select xs"
									/>
									<CF7AppsSelectField
										label={ __( 'Data Type', 'cf7apps' ) }
										name={ `data_type_${ index }` }
										selected={ webhook.data_type || 'json' }
										options={ dataTypeOptions }
										disabled={ disabled }
										onChange={ ( e ) =>
											updateWebhook( index, { data_type: e.target.value } )
										}
										variant="app"
										className="inline-select xs"
									/>
								</div>

								<CollapsibleSection
									title={ __( 'Selective Webhook Data', 'cf7apps' ) }
								>
									<Cf7WebhookFieldSelector
										fieldOptions={ fieldOptions }
										selectedFields={
											Array.isArray( webhook.selected_fields )
												? webhook.selected_fields
												: []
										}
										mode={ webhook.selected_fields_mode || 'all' }
										disabled={ disabled }
										showHeader={ true }
										onChange={ ( {
											selectedFields: nextFields,
											mode: nextMode,
										} ) => {
											updateWebhook( index, {
												selected_fields: nextFields,
												selected_fields_mode: nextMode,
											} );
										} }
									/>
									<p className="cf7apps-help-text">
										{ __(
											'No fields selected. All form data will be included from the payload.',
											'cf7apps'
										) }
									</p>
								</CollapsibleSection>

								<CollapsibleSection title={ __( 'Request Headers', 'cf7apps' ) }>
									<p className="cf7apps-help-text">
										{ __(
											'When you need authentication/authorization. You can add HTTP Headers to your webhook request.',
											'cf7apps'
										) }
									</p>
									<textarea
										className="cf7apps-form-input"
										rows={ 3 }
										disabled={ disabled }
										value={ webhook.headers || '' }
										onChange={ ( e ) =>
											updateWebhook( index, { headers: e.target.value } )
										}
									/>
									<p className="cf7apps-help-text">
										{ __(
											'One header by line, separated by colon. Example: Authorization: Bearer 99999999999999999999',
											'cf7apps'
										) }
									</p>
								</CollapsibleSection>

								<CollapsibleSection title={ __( 'Request Body', 'cf7apps' ) }>
									<p className="cf7apps-help-text">
										{ __( 'When you need customize your request.', 'cf7apps' ) }
									</p>
									<p className="cf7apps-webhook-body-warning">
										<strong>{ __( 'BE CAREFUL!', 'cf7apps' ) }</strong>{ ' ' }
										{ __(
											'This can break your integration (check your quotes).',
											'cf7apps'
										) }
									</p>
									<p className="cf7apps-help-text">
										<strong>{ __( 'REMEMBER:', 'cf7apps' ) }</strong>{ ' ' }
										{ __(
											'You can change field name with webhook config: [email* your_email webhook:email]',
											'cf7apps'
										) }
									</p>
									<textarea
										className="cf7apps-form-input"
										rows={ 4 }
										disabled={ disabled }
										value={ webhook.payload || '' }
										onChange={ ( e ) =>
											updateWebhook( index, { payload: e.target.value } )
										}
									/>
									<pre className="cf7apps-webhook-body-preview">{ examplePayload }</pre>
									<p className="cf7apps-help-text">
										{ __(
											'This is just an example of field names and will not reflect actual data or customizations.',
											'cf7apps'
										) }
									</p>
								</CollapsibleSection>
							</div>
						</div>
					);
				} ) }

				<button
					type="button"
					className="cf7apps-webhook-add-btn"
					disabled={ disabled }
					onClick={ addWebhook }
				>
					<AddIcon fontSize="small" />
					{ __( 'Add Webhook', 'cf7apps' ) }
				</button>
			</div>
		</div>
	);
};

export default Cf7WebhookFormProSettings;
