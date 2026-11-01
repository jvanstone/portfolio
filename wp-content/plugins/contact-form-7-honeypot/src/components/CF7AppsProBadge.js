import { __ } from '@wordpress/i18n';
import { getCF7AppsAssetsURL } from '../utils/appCardTheme';

/**
 * Figma PRO Tag (node 4574:22672) — teal gradient pill, white crown + PRO.
 */
const CF7AppsProBadge = ( { className = '' } ) => {
	const assetsBase = getCF7AppsAssetsURL();
	const crownIcon = assetsBase
		? `${ assetsBase.replace( /\/$/, '' ) }/images/dashboard/pro-crown-white.svg`
		: '';

	return (
		<span className={ `cf7apps-pro-badge${ className ? ` ${ className }` : '' }` }>
			{ crownIcon && (
				<img
					className="cf7apps-pro-badge__icon"
					src={ crownIcon }
					alt=""
					width={ 9.8 }
					height={ 9.76 }
					aria-hidden="true"
				/>
			) }
			<span className="cf7apps-pro-badge__label">{ __( 'PRO', 'cf7apps' ) }</span>
		</span>
	);
};

export default CF7AppsProBadge;
