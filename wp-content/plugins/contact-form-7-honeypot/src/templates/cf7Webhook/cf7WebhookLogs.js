import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import ReplayOutlinedIcon from '@mui/icons-material/ReplayOutlined';
import { toast } from 'react-toastify';
import CF7AppsSkeletonLoader from '../../components/CF7AppsSkeletonLoader';
import CF7AppsPopup from '../../components/CF7AppsPopup';
import CF7AppsCalendar from '../../components/CF7AppsCalendar';
import Cf7WebhookLogsDemo from './cf7WebhookLogsDemo';
import {
	deleteWebhookLogs,
	getAllCF7Forms,
	getWebhookLogs,
	retryWebhookLogs,
} from '../../api/api';

const formatDate = ( timestamp ) => {
	const ts = Number( timestamp );
	if ( ! ts ) {
		return '—';
	}
	return new Date( ts * 1000 ).toLocaleString( 'en-US', {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: 'numeric',
		minute: '2-digit',
		hour12: true,
	} ).replace( ',', '' ).replace( /,\s*/, ' ' );
};

const formatResponse = ( row ) => {
	const code = String( row.response_code || '' ).trim();
	const message = String( row.response_message || '' ).trim();
	if ( code && message ) {
		const short = message.length > 40 ? `${ message.slice( 0, 40 ) }…` : message;
		// Prefer "200 OK" style when message is short status text.
		if ( message.length < 48 && ! message.includes( '{' ) ) {
			return `${ code } ${ message }`.trim();
		}
		return code || short;
	}
	return code || message || '—';
};

/**
 * Webhook Logs tab UI (Figma).
 */
const Cf7WebhookLogs = ( { appSettings } ) => {
	const [ loading, setLoading ] = useState( true );
	const [ rows, setRows ] = useState( [] );
	const [ total, setTotal ] = useState( 0 );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ forms, setForms ] = useState( {} );
	const [ bulkAction, setBulkAction ] = useState( 'null' );
	const [ showDetails, setShowDetails ] = useState( false );
	const [ detail, setDetail ] = useState( null );
	const [ selectedDate, setSelectedDate ] = useState( '' );
	const [ searchText, setSearchText ] = useState( '' );
	const [ dateRange, setDateRange ] = useState( {
		startDate: new Date(),
		endDate: new Date(),
		key: 'selection',
	} );
	const [ filters, setFilters ] = useState( {
		form_id: 0,
		url: '',
		start_date: 0,
		end_date: 0,
	} );

	const hasPro = !! appSettings?.has_pro;
	const perPage = 10;

	const formOptions = useMemo( () => {
		const options = { 0: __( 'All Forms', 'cf7apps' ) };
		Object.entries( forms ).forEach( ( [ id, title ] ) => {
			if ( id !== '0' ) {
				options[ id ] = title;
			}
		} );
		return options;
	}, [ forms ] );

	const fetchLogs = async ( page = currentPage, nextFilters = filters ) => {
		const response = await getWebhookLogs( {
			page,
			perPage,
			form_id: nextFilters.form_id || 0,
			url: nextFilters.url || '',
			start_date: nextFilters.start_date || 0,
			end_date: nextFilters.end_date || 0,
		} );

		if ( response ) {
			setRows( response.rows || [] );
			setTotal( response.total || 0 );
			setCurrentPage( page );
		}
	};

	useEffect( () => {
		if ( ! hasPro ) {
			setLoading( false );
			return;
		}

		( async () => {
			setLoading( true );
			const formsList = await getAllCF7Forms();
			if ( formsList ) {
				setForms( formsList );
			}
			await fetchLogs( 1, filters );
			setLoading( false );
		} )();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ hasPro ] );

	if ( ! hasPro ) {
		return <Cf7WebhookLogsDemo />;
	}

	const getSelectedIds = () =>
		Array.from( document.querySelectorAll( '.cf7apps-webhook-log-checkbox:checked' ) ).map(
			( el ) => Number( el.value )
		);

	const handleBulk = async () => {
		const ids = getSelectedIds();
		if ( ! ids.length ) {
			toast.error( __( 'Please select at least one log.', 'cf7apps' ) );
			return;
		}

		if ( bulkAction === 'retry' ) {
			const result = await retryWebhookLogs( ids );
			if ( result ) {
				toast.success( __( 'Selected webhooks retried.', 'cf7apps' ) );
				await fetchLogs( currentPage );
			} else {
				toast.error( __( 'Retry failed.', 'cf7apps' ) );
			}
		} else if ( bulkAction === 'delete' ) {
			if ( ! confirm( __( 'Delete selected webhook logs?', 'cf7apps' ) ) ) {
				return;
			}
			const result = await deleteWebhookLogs( ids );
			if ( result ) {
				toast.success( __( 'Selected logs deleted.', 'cf7apps' ) );
				await fetchLogs( 1 );
			} else {
				toast.error( __( 'Delete failed.', 'cf7apps' ) );
			}
		} else {
			toast.error( __( 'Please select a bulk action.', 'cf7apps' ) );
		}
	};

	const clearFilters = () => {
		const next = {
			form_id: 0,
			url: '',
			start_date: 0,
			end_date: 0,
		};
		setFilters( next );
		setSearchText( '' );
		setSelectedDate( '' );
		setDateRange( {
			startDate: new Date(),
			endDate: new Date(),
			key: 'selection',
		} );
		setBulkAction( 'null' );
		fetchLogs( 1, next );
	};

	const applySearch = () => {
		const next = { ...filters, url: searchText.trim() };
		setFilters( next );
		fetchLogs( 1, next );
	};

	const pages = Math.max( 1, Math.ceil( total / perPage ) );
	const showingFrom = total ? ( currentPage - 1 ) * perPage + 1 : 0;
	const showingTo = Math.min( currentPage * perPage, total );

	return loading ? (
		<CF7AppsSkeletonLoader height={ 280 } />
	) : (
		<div className="cf7apps-webhook-logs cf7apps-template-cf7-entries">
			<div className="cf7apps-entries-toolbar cf7apps-webhook-logs-toolbar cf7apps-table-nav cf7apps-nav-header">
				<div className="cf7apps-entries-toolbar__left cf7apps-bulk-action cf7apps-left">
					<div className="cf7apps-entries-toolbar__field">
						<select
							className="cf7apps-form-input cf7apps-bulk-action-select-field"
							value={ bulkAction }
							onChange={ ( e ) => setBulkAction( e.target.value ) }
							aria-label={ __( 'Bulk Actions', 'cf7apps' ) }
						>
							<option value="null">{ __( 'Bulk Actions', 'cf7apps' ) }</option>
							<option value="retry">{ __( 'Retry Selected', 'cf7apps' ) }</option>
							<option value="delete">{ __( 'Clear Selected', 'cf7apps' ) }</option>
						</select>
					</div>
					<Button
						className="cf7apps-btn tertiary-primary cf7apps-entries-toolbar__apply"
						onClick={ handleBulk }
					>
						{ __( 'Apply', 'cf7apps' ) }
					</Button>
				</div>

				<div className="cf7apps-entries-toolbar__right cf7apps-right cf7apps-filter">
					<div className="cf7apps-entries-toolbar__field">
						<select
							className="cf7apps-form-input cf7apps-filter-select-field"
							value={ String( filters.form_id || 0 ) }
							onChange={ ( e ) => {
								const next = {
									...filters,
									form_id: Number( e.target.value ) || 0,
								};
								setFilters( next );
								fetchLogs( 1, next );
							} }
							aria-label={ __( 'All Forms', 'cf7apps' ) }
						>
							{ Object.entries( formOptions ).map( ( [ id, title ] ) => (
								<option key={ id } value={ id }>
									{ title }
								</option>
							) ) }
						</select>
					</div>

					<div className="cf7apps-entries-toolbar__field">
						<input
							type="text"
							className="cf7apps-form-input cf7apps-filter-text-field cf7apps-app-select-field"
							placeholder={ __( 'Search', 'cf7apps' ) }
							value={ searchText }
							onChange={ ( e ) => setSearchText( e.target.value ) }
							onBlur={ applySearch }
							onKeyDown={ ( e ) => {
								if ( e.key === 'Enter' ) {
									e.preventDefault();
									applySearch();
								}
							} }
						/>
					</div>

					<div className="cf7apps-entries-toolbar__field cf7apps-entries-toolbar__field--calendar">
						<CF7AppsCalendar
							selectedDate={ selectedDate }
							selection={ [ dateRange ] }
							onSelect={ ( ranges ) => {
								const selection = ranges?.selection || dateRange;
								setDateRange( selection );
								setSelectedDate(
									`${ selection.startDate.toLocaleDateString( 'en-US', {
										month: 'short',
										day: 'numeric',
										year: 'numeric',
									} ) } - ${ selection.endDate.toLocaleDateString( 'en-US', {
										month: 'short',
										day: 'numeric',
										year: 'numeric',
									} ) }`
								);
								const start = Math.floor(
									new Date( selection.startDate ).setHours( 0, 0, 0, 0 ) / 1000
								);
								const end = Math.floor(
									new Date( selection.endDate ).setHours( 23, 59, 59, 999 ) / 1000
								);
								const next = {
									...filters,
									start_date: start,
									end_date: end,
								};
								setFilters( next );
								fetchLogs( 1, next );
							} }
							placeHolder={ __( 'Select Date Range', 'cf7apps' ) }
						/>
					</div>

					<Button
						className="cf7apps-btn tertiary-secondary cf7apps-entries-toolbar__clear"
						onClick={ clearFilters }
					>
						{ __( 'Clear Filter', 'cf7apps' ) }
					</Button>
				</div>
			</div>

			<div className="cf7apps-webhook-logs-table-wrap">
				<table className="cf7apps-entries-table cf7apps-webhook-logs-table" width="100%">
					<thead>
						<tr>
							<th>
								<input
									type="checkbox"
									onChange={ ( e ) => {
										document
											.querySelectorAll( '.cf7apps-webhook-log-checkbox' )
											.forEach( ( box ) => {
												box.checked = e.target.checked;
											} );
									} }
								/>
							</th>
							<th>{ __( 'Form Name', 'cf7apps' ) }</th>
							<th>{ __( 'Date & Time', 'cf7apps' ) }</th>
							<th>{ __( 'Status', 'cf7apps' ) }</th>
							<th>{ __( 'Method', 'cf7apps' ) }</th>
							<th>{ __( 'Response', 'cf7apps' ) }</th>
							<th>{ __( 'Action', 'cf7apps' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ rows.length ? (
							rows.map( ( row ) => (
								<tr key={ row.id }>
									<td>
										<input
											type="checkbox"
											className="cf7apps-webhook-log-checkbox"
											value={ row.id }
										/>
									</td>
									<td>{ row.form_name || '—' }</td>
									<td>{ formatDate( row.created_at ) }</td>
									<td>
										<span
											className={
												row.status === 'success'
													? 'cf7apps-webhook-log-status is-success'
													: 'cf7apps-webhook-log-status is-failed'
											}
										>
											{ row.status === 'success'
												? __( 'Success', 'cf7apps' )
												: __( 'Failed', 'cf7apps' ) }
										</span>
									</td>
									<td>
										<span className="cf7apps-webhook-log-method">
											{ row.method || '—' }
										</span>
									</td>
									<td title={ row.response_message || '' }>
										{ formatResponse( row ) }
									</td>
									<td className="cf7apps-webhook-log-actions">
										<div className="cf7apps-entries-row-actions">
											<button
												type="button"
												className="cf7apps-entries-row-action cf7apps-webhook-log-action--view"
												onClick={ () => {
													setDetail( row );
													setShowDetails( true );
												} }
											>
												<VisibilityOutlinedIcon fontSize="inherit" />
												{ __( 'View', 'cf7apps' ) }
											</button>
											<button
												type="button"
												className="cf7apps-entries-row-action cf7apps-webhook-log-action--retry"
												onClick={ async () => {
													const result = await retryWebhookLogs( [ row.id ] );
													if ( result ) {
														toast.success( __( 'Webhook retried.', 'cf7apps' ) );
														fetchLogs( currentPage );
													} else {
														toast.error( __( 'Retry failed.', 'cf7apps' ) );
													}
												} }
											>
												<ReplayOutlinedIcon fontSize="inherit" />
												{ __( 'Retry', 'cf7apps' ) }
											</button>
										</div>
									</td>
								</tr>
							) )
						) : (
							<tr>
								<td colSpan={ 7 }>{ __( 'No webhook logs found.', 'cf7apps' ) }</td>
							</tr>
						) }
					</tbody>
				</table>
			</div>

			<div className="cf7apps-webhook-logs-footer">
				<span className="cf7apps-webhook-logs-count">
					{ total
						? `${ __( 'Showing', 'cf7apps' ) } ${ showingFrom }–${ showingTo } ${ __( 'of', 'cf7apps' ) } ${ total } ${ __( 'entries', 'cf7apps' ) }`
						: __( 'Showing 0 of 0 entries', 'cf7apps' ) }
				</span>
				<div className="cf7apps-entries-pagination">
					<Button
						className="cf7apps-btn tertiary-secondary"
						disabled={ currentPage <= 1 }
						onClick={ () => fetchLogs( currentPage - 1 ) }
					>
						&lt;
					</Button>
					<span>{ currentPage }</span>
					<Button
						className="cf7apps-btn tertiary-secondary"
						disabled={ currentPage >= pages }
						onClick={ () => fetchLogs( currentPage + 1 ) }
					>
						&gt;
					</Button>
				</div>
			</div>

			{ showDetails && detail && (
				<CF7AppsPopup
					title={ __( 'Webhook Log Detail', 'cf7apps' ) }
					className="cf7apps-webhook-log-detail-popup"
					onClose={ () => {
						setShowDetails( false );
						setDetail( null );
					} }
				>
					<div className="cf7apps-webhook-log-detail">
						<div className="cf7apps-webhook-log-detail__meta">
							<div className="cf7apps-webhook-log-detail__row">
								<span className="cf7apps-webhook-log-detail__label">
									{ __( 'Form', 'cf7apps' ) }
								</span>
								<span className="cf7apps-webhook-log-detail__value">
									{ detail.form_name || '—' }
								</span>
							</div>
							<div className="cf7apps-webhook-log-detail__row">
								<span className="cf7apps-webhook-log-detail__label">
									{ __( 'Status', 'cf7apps' ) }
								</span>
								<span className="cf7apps-webhook-log-detail__value">
									<span
										className={
											detail.status === 'success'
												? 'cf7apps-webhook-log-status is-success'
												: 'cf7apps-webhook-log-status is-failed'
										}
									>
										{ detail.status }
									</span>
								</span>
							</div>
							<div className="cf7apps-webhook-log-detail__row">
								<span className="cf7apps-webhook-log-detail__label">
									{ __( 'URL', 'cf7apps' ) }
								</span>
								<span className="cf7apps-webhook-log-detail__value cf7apps-webhook-log-detail__value--mono">
									{ detail.webhook_url || '—' }
								</span>
							</div>
							<div className="cf7apps-webhook-log-detail__row">
								<span className="cf7apps-webhook-log-detail__label">
									{ __( 'Method', 'cf7apps' ) }
								</span>
								<span className="cf7apps-webhook-log-detail__value">
									<span className="cf7apps-webhook-log-method">
										{ detail.method || '—' }
									</span>
								</span>
							</div>
							<div className="cf7apps-webhook-log-detail__row">
								<span className="cf7apps-webhook-log-detail__label">
									{ __( 'Response Code', 'cf7apps' ) }
								</span>
								<span className="cf7apps-webhook-log-detail__value">
									{ detail.response_code || '—' }
								</span>
							</div>
						</div>

						<div className="cf7apps-webhook-log-detail__block">
							<div className="cf7apps-webhook-log-detail__block-title">
								{ __( 'Response', 'cf7apps' ) }
							</div>
							<pre className="cf7apps-webhook-log-detail__code">
								{ detail.response_message || '—' }
							</pre>
						</div>

						<div className="cf7apps-webhook-log-detail__block">
							<div className="cf7apps-webhook-log-detail__block-title">
								{ __( 'Payload', 'cf7apps' ) }
							</div>
							<pre className="cf7apps-webhook-log-detail__code">
								{ ( () => {
									try {
										return JSON.stringify( JSON.parse( detail.payload ), null, 2 );
									} catch ( err ) {
										return detail.payload || '—';
									}
								} )() }
							</pre>
						</div>
					</div>
				</CF7AppsPopup>
			) }
		</div>
	);
};

export default Cf7WebhookLogs;
