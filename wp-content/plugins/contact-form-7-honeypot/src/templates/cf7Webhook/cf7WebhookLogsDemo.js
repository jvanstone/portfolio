import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import ReplayOutlinedIcon from '@mui/icons-material/ReplayOutlined';

const DEMO_ROWS = [
	{
		id: 1,
		form_name: 'Contact Form',
		created_at_label: 'Aug 20, 2026, 10:24 AM',
		status: 'success',
		method: 'POST',
		response: '200 OK',
	},
	{
		id: 2,
		form_name: 'Newsletter Signup',
		created_at_label: 'Aug 19, 2026, 4:12 PM',
		status: 'failed',
		method: 'POST',
		response: '500 Internal Server Error',
	},
	{
		id: 3,
		form_name: 'Support Request',
		created_at_label: 'Aug 18, 2026, 9:05 AM',
		status: 'success',
		method: 'PUT',
		response: '200 OK',
	},
	{
		id: 4,
		form_name: 'Contact Form',
		created_at_label: 'Aug 17, 2026, 2:41 PM',
		status: 'failed',
		method: 'POST',
		response: '404 Not Found',
	},
	{
		id: 5,
		form_name: 'Quote Request',
		created_at_label: 'Aug 16, 2026, 11:18 AM',
		status: 'success',
		method: 'POST',
		response: '201 Created',
	},
];

/**
 * Free-only Logs demo table (non-interactive preview).
 */
const Cf7WebhookLogsDemo = () => (
	<div className="cf7apps-webhook-logs cf7apps-webhook-logs--demo cf7apps-template-cf7-entries">
		<div className="cf7apps-webhook-logs-demo-stage" aria-hidden="true">
			<div className="cf7apps-entries-toolbar cf7apps-webhook-logs-toolbar cf7apps-table-nav cf7apps-nav-header">
				<div className="cf7apps-entries-toolbar__left cf7apps-bulk-action cf7apps-left">
					<div className="cf7apps-entries-toolbar__field">
						<select
							className="cf7apps-form-input cf7apps-bulk-action-select-field"
							disabled
							defaultValue="null"
							aria-label={ __( 'Bulk Actions', 'cf7apps' ) }
						>
							<option value="null">{ __( 'Bulk Actions', 'cf7apps' ) }</option>
						</select>
					</div>
					<Button className="cf7apps-btn tertiary-primary cf7apps-entries-toolbar__apply" disabled>
						{ __( 'Apply', 'cf7apps' ) }
					</Button>
				</div>

				<div className="cf7apps-entries-toolbar__right cf7apps-right cf7apps-filter">
					<div className="cf7apps-entries-toolbar__field">
						<select
							className="cf7apps-form-input cf7apps-filter-select-field"
							disabled
							defaultValue="0"
							aria-label={ __( 'All Forms', 'cf7apps' ) }
						>
							<option value="0">{ __( 'All Forms', 'cf7apps' ) }</option>
						</select>
					</div>
					<div className="cf7apps-entries-toolbar__field">
						<input
							type="text"
							className="cf7apps-form-input cf7apps-filter-text-field cf7apps-app-select-field"
							placeholder={ __( 'Search', 'cf7apps' ) }
							disabled
						/>
					</div>
					<div className="cf7apps-entries-toolbar__field">
						<input
							type="text"
							className="cf7apps-form-input cf7apps-app-select-field"
							placeholder={ __( 'Select Date Range', 'cf7apps' ) }
							disabled
						/>
					</div>
					<Button className="cf7apps-btn tertiary-secondary cf7apps-entries-toolbar__clear" disabled>
						{ __( 'Clear Filter', 'cf7apps' ) }
					</Button>
				</div>
			</div>

			<div className="cf7apps-webhook-logs-table-wrap">
				<table className="cf7apps-entries-table cf7apps-webhook-logs-table" width="100%">
					<thead>
						<tr>
							<th>
								<input type="checkbox" disabled />
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
						{ DEMO_ROWS.map( ( row ) => (
							<tr key={ row.id }>
								<td>
									<input type="checkbox" disabled />
								</td>
								<td>{ row.form_name }</td>
								<td>{ row.created_at_label }</td>
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
									<span className="cf7apps-webhook-log-method">{ row.method }</span>
								</td>
								<td>{ row.response }</td>
								<td className="cf7apps-webhook-log-actions">
									<div className="cf7apps-entries-row-actions">
										<button
											type="button"
											className="cf7apps-entries-row-action cf7apps-webhook-log-action--view"
											disabled
											tabIndex={ -1 }
										>
											<VisibilityOutlinedIcon fontSize="inherit" />
											{ __( 'View', 'cf7apps' ) }
										</button>
										<button
											type="button"
											className="cf7apps-entries-row-action cf7apps-webhook-log-action--retry"
											disabled
											tabIndex={ -1 }
										>
											<ReplayOutlinedIcon fontSize="inherit" />
											{ __( 'Retry', 'cf7apps' ) }
										</button>
									</div>
								</td>
							</tr>
						) ) }
					</tbody>
				</table>
			</div>

			<div className="cf7apps-webhook-logs-footer">
				<span className="cf7apps-webhook-logs-count">
					{ __( 'Showing 1–5 of 24 entries', 'cf7apps' ) }
				</span>
				<div className="cf7apps-entries-pagination">
					<Button className="cf7apps-btn tertiary-secondary" disabled>
						&lt;
					</Button>
					<span>1</span>
					<Button className="cf7apps-btn tertiary-secondary" disabled>
						&gt;
					</Button>
				</div>
			</div>
		</div>
	</div>
);

export default Cf7WebhookLogsDemo;
