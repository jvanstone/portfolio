import { __ } from '@wordpress/i18n';

/**
 * Checkbox list for selective webhook fields (Figma: 2-column grid).
 */
const Cf7WebhookFieldSelector = ( {
	fieldOptions = {},
	selectedFields = [],
	mode = 'all',
	onChange,
	disabled = false,
	showHeader = true,
} ) => {
	const names = Object.keys( fieldOptions );

	const emit = ( nextSelected, nextMode ) => {
		if ( typeof onChange === 'function' ) {
			onChange( { selectedFields: nextSelected, mode: nextMode } );
		}
	};

	const isChecked = ( name ) => {
		if ( 'all' === mode ) {
			return true;
		}
		return selectedFields.includes( name );
	};

	const toggleField = ( name ) => {
		if ( disabled ) {
			return;
		}

		let nextSelected;
		if ( 'all' === mode ) {
			nextSelected = names.filter( ( n ) => n !== name );
		} else if ( selectedFields.includes( name ) ) {
			nextSelected = selectedFields.filter( ( n ) => n !== name );
		} else {
			nextSelected = [ ...selectedFields, name ];
		}

		const nextMode =
			nextSelected.length === names.length && names.length > 0
				? 'all'
				: 'selected';

		emit( nextMode === 'all' ? names : nextSelected, nextMode );
	};

	if ( ! names.length ) {
		return (
			<div className="cf7apps-webhook-field-selector">
				<p className="cf7apps-help-text">
					{ __(
						'No form fields found. Create a Contact Form 7 form with fields to select them here.',
						'cf7apps'
					) }
				</p>
			</div>
		);
	}

	return (
		<div className="cf7apps-webhook-field-selector">
			{ showHeader && (
				<div className="cf7apps-webhook-field-selector__heading">
					<label className="cf7apps-webhook-field-selector__title">
						{ __( 'Select Form Fields', 'cf7apps' ) }
					</label>
					<p className="cf7apps-help-text cf7apps-webhook-field-selector__help">
						{ __(
							'Choose which fields to include in the webhook payload.',
							'cf7apps'
						) }
					</p>
				</div>
			) }
			<ul className="cf7apps-webhook-field-selector__list">
				{ names.map( ( name ) => (
					<li key={ name }>
						<label>
							<input
								type="checkbox"
								checked={ isChecked( name ) }
								disabled={ disabled }
								onChange={ () => toggleField( name ) }
							/>
							<span>{ fieldOptions[ name ] || name }</span>
						</label>
					</li>
				) ) }
			</ul>
		</div>
	);
};

export default Cf7WebhookFieldSelector;
