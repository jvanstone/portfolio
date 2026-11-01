import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CF7AppsProBadge from './CF7AppsProBadge';

/**
 * Disabled Pro-gated toggle teaser (Figma free state).
 *
 * @param {Object} props
 * @param {string} props.label
 * @param {string} [props.help]
 * @param {boolean} [props.collapsible] Render as accordion bar instead of toggle.
 */
const CF7AppsProToggleTeaser = ( {
	label = __( 'Conditional Redirection', 'cf7apps' ),
	help = __(
		'Conditional redirection will redirect users based on specific form field values or submission conditions. Upgrade to PRO to unlock this feature.',
		'cf7apps'
	),
	collapsible = false,
} ) => {
	const [ showMore, setShowMore ] = useState( false );
	const previewLength = 52;
	const helpText = help || '';

	if ( collapsible ) {
		return (
			<div className="cf7apps-pro-toggle-teaser cf7apps-pro-collapsible-teaser">
				<div className="cf7apps-settings-collapsible-trigger cf7apps-pro-collapsible-teaser__trigger">
					<label>
						<span>{ label }</span>
						<CF7AppsProBadge />
					</label>
					<span className="cf7apps-collapsible-chevron" aria-hidden="true" />
				</div>
			</div>
		);
	}

	return (
		<div className="cf7apps-pro-toggle-teaser">
			<div className="cf7apps-pro-toggle-teaser__row">
				<div className="cf7apps-pro-toggle-teaser__label-wrap">
					<span className="cf7apps-pro-toggle-teaser__label">
						{ label }
					</span>
					<CF7AppsProBadge />
				</div>
				<button
					type="button"
					className="cf7apps-settings-switch"
					role="switch"
					aria-checked={ false }
					aria-label={ label }
					disabled
				>
					<span className="cf7apps-settings-switch__track" aria-hidden="true" />
					<span className="cf7apps-settings-switch__knob" aria-hidden="true" />
				</button>
			</div>
			{ helpText ? (
				<p className="cf7apps-pro-toggle-teaser__help">
					{ showMore || helpText.length <= previewLength
						? helpText
						: `${ helpText.substring( 0, previewLength ) }... ` }
					{ helpText.length > previewLength && (
						<button
							type="button"
							className="cf7apps-show-more-text"
							onClick={ () => setShowMore( ! showMore ) }
						>
							{ showMore
								? __( 'Show Less', 'cf7apps' )
								: __( 'Show More', 'cf7apps' ) }
						</button>
					) }
				</p>
			) : null }
		</div>
	);
};

export default CF7AppsProToggleTeaser;
