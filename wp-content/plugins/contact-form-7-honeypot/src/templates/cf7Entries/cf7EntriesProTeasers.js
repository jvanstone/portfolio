import { __ } from '@wordpress/i18n';
import CF7AppsProToggleTeaser from '../../components/CF7AppsProToggleTeaser';

/**
 * Free-only Pro teasers on Entries General settings (Figma).
 */
const Cf7EntriesProTeasers = () => (
	<div className="cf7apps-entries-pro-teasers">
		<CF7AppsProToggleTeaser
			label={ __( 'Auto Delete Activation', 'cf7apps' ) }
			help=""
		/>
		<CF7AppsProToggleTeaser
			label={ __( 'Export Entries', 'cf7apps' ) }
			help=""
		/>
		<CF7AppsProToggleTeaser
			label={ __( 'Entries Restriction', 'cf7apps' ) }
			help=""
		/>
	</div>
);

export default Cf7EntriesProTeasers;
