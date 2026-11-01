<?php
/**
 * Webhook failure notification email template.
 *
 * @package CF7Apps
 *
 * @var string $site_name
 * @var string $form_name
 * @var int    $form_id
 * @var string $webhook_url
 * @var string $method
 * @var string $response_code
 * @var string $response_message
 * @var string $payload_preview
 * @var string $occurred_at
 * @var string $logs_url
 * @var string $logo_url
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="color-scheme" content="light only">
	<title><?php esc_html_e( 'Webhook Request Failed', 'cf7apps' ); ?></title>
	<style type="text/css">
		body, table, td, p, h1, h2, span, a, strong, pre {
			font-family: Arial, Helvetica, sans-serif !important;
		}
		body {
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}
		table {
			border-collapse: collapse;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
		}
		pre {
			white-space: pre-wrap;
			word-break: break-word;
		}
	</style>
</head>
<body style="margin:0;padding:0;background-color:#f6f7f7;color:#21222f;">
	<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#f6f7f7" style="background-color:#f6f7f7;">
		<tr>
			<td align="center" style="padding:32px 16px;">
				<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ffffff" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:10px;overflow:hidden;">
					<tr>
						<td style="padding:32px 32px 16px;" align="center">
							<?php if ( ! empty( $logo_url ) ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'CF7 Apps', 'cf7apps' ); ?>" width="180" style="display:block;width:180px;max-width:100%;height:auto;border:0;">
							<?php else : ?>
								<p style="margin:0;font-size:22px;font-weight:700;color:#0d2149;">CF7 Apps</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="padding:8px 32px 0;" align="center">
							<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="background-color:#fdecea;border:1px solid #ea4335;border-radius:20px;">
								<tr>
									<td style="padding:6px 14px;font-size:12px;font-weight:700;color:#611a15;text-transform:uppercase;letter-spacing:0.04em;">
										<?php esc_html_e( 'Webhook Failed', 'cf7apps' ); ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:20px 32px 8px;" align="center">
							<h1 style="margin:0;font-size:22px;line-height:1.3;font-weight:700;color:#0d2149;">
								<?php esc_html_e( 'A webhook request failed', 'cf7apps' ); ?>
							</h1>
							<p style="margin:10px 0 0;font-size:14px;line-height:1.5;color:#4a5568;">
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: site name, 2: datetime */
										__( 'CF7 Apps could not deliver a webhook from %1$s on %2$s.', 'cf7apps' ),
										$site_name,
										$occurred_at
									)
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<td style="padding:16px 32px 8px;">
							<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #d4d4e4;border-radius:8px;overflow:hidden;">
								<tr>
									<td colspan="2" style="padding:12px 16px;background-color:#f9f9f9;border-bottom:1px solid #d4d4e4;font-size:13px;font-weight:700;color:#0d2149;">
										<?php esc_html_e( 'Request Details', 'cf7apps' ); ?>
									</td>
								</tr>
								<tr>
									<td style="padding:10px 16px;width:140px;font-size:13px;font-weight:600;color:#717171;border-bottom:1px solid #eef0f4;vertical-align:top;">
										<?php esc_html_e( 'Form', 'cf7apps' ); ?>
									</td>
									<td style="padding:10px 16px;font-size:13px;color:#2c3338;border-bottom:1px solid #eef0f4;">
										<?php echo esc_html( $form_name ); ?>
										<?php if ( $form_id ) : ?>
											<span style="color:#717171;">(#<?php echo absint( $form_id ); ?>)</span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td style="padding:10px 16px;font-size:13px;font-weight:600;color:#717171;border-bottom:1px solid #eef0f4;vertical-align:top;">
										<?php esc_html_e( 'Webhook URL', 'cf7apps' ); ?>
									</td>
									<td style="padding:10px 16px;font-size:13px;color:#2c3338;border-bottom:1px solid #eef0f4;word-break:break-all;">
										<?php echo esc_html( $webhook_url ); ?>
									</td>
								</tr>
								<tr>
									<td style="padding:10px 16px;font-size:13px;font-weight:600;color:#717171;border-bottom:1px solid #eef0f4;vertical-align:top;">
										<?php esc_html_e( 'Method', 'cf7apps' ); ?>
									</td>
									<td style="padding:10px 16px;font-size:13px;color:#2c3338;border-bottom:1px solid #eef0f4;">
										<span style="display:inline-block;padding:2px 8px;border-radius:5px;background:#ededed;font-weight:700;">
											<?php echo esc_html( $method ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td style="padding:10px 16px;font-size:13px;font-weight:600;color:#717171;border-bottom:1px solid #eef0f4;vertical-align:top;">
										<?php esc_html_e( 'Response Code', 'cf7apps' ); ?>
									</td>
									<td style="padding:10px 16px;font-size:13px;color:#2c3338;border-bottom:1px solid #eef0f4;">
										<?php echo esc_html( $response_code ? $response_code : '—' ); ?>
									</td>
								</tr>
								<tr>
									<td style="padding:10px 16px;font-size:13px;font-weight:600;color:#717171;vertical-align:top;">
										<?php esc_html_e( 'Response', 'cf7apps' ); ?>
									</td>
									<td style="padding:10px 16px;font-size:13px;color:#2c3338;">
										<pre style="margin:0;padding:10px;background:#f9f9f9;border-radius:6px;font-size:12px;line-height:1.45;color:#2c3338;"><?php echo esc_html( $response_message ? $response_message : '—' ); ?></pre>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:8px 32px 8px;">
							<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #d4d4e4;border-radius:8px;overflow:hidden;">
								<tr>
									<td style="padding:12px 16px;background-color:#f9f9f9;border-bottom:1px solid #d4d4e4;font-size:13px;font-weight:700;color:#0d2149;">
										<?php esc_html_e( 'Payload', 'cf7apps' ); ?>
									</td>
								</tr>
								<tr>
									<td style="padding:12px 16px;">
										<pre style="margin:0;padding:12px;background:#f9f9f9;border-radius:6px;font-size:12px;line-height:1.45;color:#2c3338;"><?php echo esc_html( $payload_preview ? $payload_preview : '—' ); ?></pre>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<?php if ( ! empty( $logs_url ) ) : ?>
					<tr>
						<td style="padding:16px 32px 28px;" align="center">
							<a href="<?php echo esc_url( $logs_url ); ?>" style="display:inline-block;padding:10px 18px;background-color:#208aae;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
								<?php esc_html_e( 'View Webhook Logs', 'cf7apps' ); ?>
							</a>
						</td>
					</tr>
					<?php else : ?>
					<tr>
						<td style="padding:0 32px 28px;"></td>
					</tr>
					<?php endif; ?>
				</table>
				<p style="margin:16px 0 0;font-size:12px;line-height:1.5;color:#8a8a8a;">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: site name */
							__( 'This notification was sent by CF7 Apps on %s because Failure Notifications are enabled.', 'cf7apps' ),
							$site_name
						)
					);
					?>
				</p>
			</td>
		</tr>
	</table>
</body>
</html>
