import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CF7AppsHelpText from './CF7AppsHelpText';

/**
 * Multi-select field with optional exclusive "All" option (Entries form selection).
 *
 * @since 3.9.0
 */
const CF7AppsMultiSelectField = ( {
	label,
	options = {},
	selectedValues = [],
	onChange,
	name,
	description,
	disabled = false,
	variant = 'app',
	allValue = 'all',
	placeholder = __( 'Select forms', 'cf7apps' ),
} ) => {
	const isAppShell = variant === 'app';
	const [ open, setOpen ] = useState( false );
	const [ query, setQuery ] = useState( '' );
	const rootRef = useRef( null );
	const searchRef = useRef( null );

	const optionEntries = useMemo(
		() => Object.entries( options || {} ),
		[ options ]
	);

	const formOptionEntries = useMemo(
		() => optionEntries.filter( ( [ value ] ) => String( value ) !== String( allValue ) ),
		[ optionEntries, allValue ]
	);

	const normalizedSelected = useMemo( () => {
		const values = ( Array.isArray( selectedValues ) ? selectedValues : [] )
			.map( ( value ) => String( value ) )
			.filter( Boolean );

		if ( ! values.length || values.includes( String( allValue ) ) ) {
			return [ String( allValue ) ];
		}

		return [ ...new Set( values ) ];
	}, [ selectedValues, allValue ] );

	const isAllSelected = normalizedSelected.includes( String( allValue ) );

	const selectedFormIds = useMemo(
		() => ( isAllSelected ? [] : normalizedSelected ),
		[ isAllSelected, normalizedSelected ]
	);

	const filteredFormOptions = useMemo( () => {
		const needle = query.trim().toLowerCase();
		if ( ! needle ) {
			return formOptionEntries;
		}

		return formOptionEntries.filter( ( [ , optionLabel ] ) =>
			String( optionLabel ).toLowerCase().includes( needle )
		);
	}, [ formOptionEntries, query ] );

	useEffect( () => {
		if ( ! open ) {
			setQuery( '' );
			return undefined;
		}

		const focusTimer = window.setTimeout( () => {
			if ( searchRef.current ) {
				searchRef.current.focus();
			}
		}, 0 );

		const onDocumentClick = ( event ) => {
			if ( rootRef.current && ! rootRef.current.contains( event.target ) ) {
				setOpen( false );
			}
		};

		const onKeyDown = ( event ) => {
			if ( 'Escape' === event.key ) {
				setOpen( false );
			}
		};

		document.addEventListener( 'mousedown', onDocumentClick );
		document.addEventListener( 'keydown', onKeyDown );

		return () => {
			window.clearTimeout( focusTimer );
			document.removeEventListener( 'mousedown', onDocumentClick );
			document.removeEventListener( 'keydown', onKeyDown );
		};
	}, [ open ] );

	const emitChange = ( nextValues ) => {
		if ( typeof onChange === 'function' ) {
			onChange( nextValues );
		}
	};

	const toggleValue = ( value ) => {
		if ( disabled ) {
			return;
		}

		const key = String( value );

		if ( key === String( allValue ) ) {
			emitChange( [ String( allValue ) ] );
			return;
		}

		const withoutAll = normalizedSelected.filter(
			( item ) => item !== String( allValue )
		);
		const exists = withoutAll.includes( key );
		const next = exists
			? withoutAll.filter( ( item ) => item !== key )
			: [ ...withoutAll, key ];

		if ( ! next.length ) {
			emitChange( [ String( allValue ) ] );
			return;
		}

		emitChange( next );
	};

	const removeChip = ( event, value ) => {
		event.preventDefault();
		event.stopPropagation();
		toggleValue( value );
	};

	const selectedCountLabel = isAllSelected
		? __( 'All forms selected', 'cf7apps' )
		: sprintfFormsSelected( selectedFormIds.length );

	return (
		<div
			ref={ rootRef }
			className={ `cf7apps-form-group cf7apps-settings cf7apps-multi-select${
				isAppShell ? ' cf7apps-app-field-group cf7apps-app-select-group' : ''
			}${ open ? ' is-open' : '' }${ disabled ? ' is-disabled' : '' }` }
		>
			{ label &&
				( isAppShell ? (
					<label className="cf7apps-app-field-label" htmlFor={ name }>
						{ label }
					</label>
				) : (
					<div>
						<label htmlFor={ name }>
							<b>{ label }</b>
						</label>
					</div>
				) ) }

			<div className="cf7apps-multi-select__control">
				<button
					type="button"
					id={ name }
					className={ `cf7apps-multi-select__trigger${
						! isAllSelected && selectedFormIds.length > 1 ? ' has-multiple-chips' : ''
					}` }
					aria-haspopup="listbox"
					aria-expanded={ open }
					disabled={ disabled }
					onClick={ () => setOpen( ( prev ) => ! prev ) }
				>
					<div className="cf7apps-multi-select__chips">
						{ isAllSelected ? (
							<span className="cf7apps-multi-select__summary-text">
								{ options[ allValue ] || __( 'All Forms', 'cf7apps' ) }
							</span>
						) : selectedFormIds.length ? (
							selectedFormIds.map( ( value ) => (
								<span key={ value } className="cf7apps-multi-select__chip">
									<span className="cf7apps-multi-select__chip-label">
										{ options[ value ] || value }
									</span>
									<span
										role="button"
										tabIndex={ -1 }
										className="cf7apps-multi-select__chip-remove"
										aria-label={ __( 'Remove', 'cf7apps' ) }
										onClick={ ( event ) => removeChip( event, value ) }
										onKeyDown={ () => {} }
									>
										×
									</span>
								</span>
							) )
						) : (
							<span className="cf7apps-multi-select__placeholder">{ placeholder }</span>
						) }
					</div>
					<span className="cf7apps-multi-select__meta">
						{ ! isAllSelected && selectedFormIds.length > 0 && (
							<span className="cf7apps-multi-select__count">
								{ selectedFormIds.length }
							</span>
						) }
						<span className="cf7apps-multi-select__chevron" aria-hidden="true" />
					</span>
				</button>

				{ open && (
					<div className="cf7apps-multi-select__panel">
						<div className="cf7apps-multi-select__panel-head">
							<span className="cf7apps-multi-select__panel-title">
								{ selectedCountLabel }
							</span>
							{ formOptionEntries.length > 4 && (
								<input
									ref={ searchRef }
									type="search"
									className="cf7apps-multi-select__search"
									value={ query }
									placeholder={ __( 'Search forms…', 'cf7apps' ) }
									onChange={ ( event ) => setQuery( event.target.value ) }
									onClick={ ( event ) => event.stopPropagation() }
								/>
							) }
						</div>

						<ul
							className="cf7apps-multi-select__menu"
							role="listbox"
							aria-multiselectable="true"
						>
							<li
								role="option"
								aria-selected={ isAllSelected }
								className={ `cf7apps-multi-select__item${
									isAllSelected ? ' is-selected' : ''
								}` }
							>
								<button
									type="button"
									className="cf7apps-multi-select__option"
									onClick={ () => toggleValue( allValue ) }
									disabled={ disabled }
								>
									<span
										className={ `cf7apps-multi-select__check${
											isAllSelected ? ' is-checked' : ''
										}` }
										aria-hidden="true"
									/>
									<span className="cf7apps-multi-select__option-label">
										{ options[ allValue ] || __( 'All Forms', 'cf7apps' ) }
									</span>
								</button>
							</li>

							<li className="cf7apps-multi-select__divider" aria-hidden="true" />

							{ filteredFormOptions.length ? (
								filteredFormOptions.map( ( [ value, optionLabel ] ) => {
									const key = String( value );
									const checked =
										! isAllSelected && normalizedSelected.includes( key );

									return (
										<li
											key={ key }
											role="option"
											aria-selected={ checked }
											className={ `cf7apps-multi-select__item${
												checked ? ' is-selected' : ''
											}` }
										>
											<button
												type="button"
												className="cf7apps-multi-select__option"
												onClick={ () => toggleValue( key ) }
												disabled={ disabled }
											>
												<span
													className={ `cf7apps-multi-select__check${
														checked ? ' is-checked' : ''
													}` }
													aria-hidden="true"
												/>
												<span className="cf7apps-multi-select__option-label">
													{ optionLabel }
												</span>
											</button>
										</li>
									);
								} )
							) : (
								<li className="cf7apps-multi-select__empty">
									{ __( 'No forms found.', 'cf7apps' ) }
								</li>
							) }
						</ul>
					</div>
				) }
			</div>

			<CF7AppsHelpText description={ description } />
		</div>
	);
};

function sprintfFormsSelected( count ) {
	if ( count === 1 ) {
		return __( '1 form selected', 'cf7apps' );
	}

	return `${ count } ${ __( 'forms selected', 'cf7apps' ) }`;
}

export default CF7AppsMultiSelectField;
