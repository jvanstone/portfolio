<?php
/**
 * CF7 Webhook Integration
 *
 * @since 3.2.0
 * @package CF7Apps
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CF7Apps_Webhook' ) && class_exists( 'CF7Apps_App' ) ) :
	/**
	 * Class CF7Apps_Webhook
	 */
	class CF7Apps_Webhook extends CF7Apps_App {
		/**
		 * App version
		 *
		 * @var string
		 */
		private $app_version = '1.0.0';

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id                    = 'webhook';
			$this->priority              = 2;
			$this->title                 = __( 'Webhook', 'cf7apps' );
			$this->description           = __( 'Send Contact Form 7 data to third-party apps instantly using webhook endpoints.', 'cf7apps' );
			$this->icon                  = plugin_dir_url( __FILE__ ) . 'assets/images/logo.png';
			$this->has_admin_settings    = true;
			$this->is_pro                = false;
			$this->by_default_enabled    = false;
			$this->has_internal_settings = true;
			$this->documentation_url     = 'https://cf7apps.com/docs/integration/webhook';
			$this->parent_menu           = 'integration';
			$this->setting_tabs          = array(
				'general' => __( 'Settings', 'cf7apps' ),
				'logs'    => __( 'Logs', 'cf7apps' ),
			);

			$this->run();
		}

		/**
		 * HTTP methods available for the current license.
		 *
		 * @return array
		 */
		public static function get_method_options() {
			$options = array(
				'POST' => 'POST',
				'GET'  => 'GET',
			);

			if ( function_exists( 'cf7apps_has_pro' ) && cf7apps_has_pro() ) {
				$options['PATCH']  = 'PATCH';
				$options['PUT']    = 'PUT';
				$options['DELETE'] = 'DELETE';
			}

			return $options;
		}

		/**
		 * Default settings
		 *
		 * @return array
		 */
		private function get_default_settings() {
			return array(
				'is_enabled'             => false,
				'global_settings'        => false,
				'webhook_url'            => '',
				'method'                 => 'POST',
				'data_type'              => 'json',
				'headers'                => '{"Content-Type":"application/json"}',
				'payload'                => '{"form_id":"{{form_id}}","fields":{{fields}}}',
				'selected_fields'        => array(),
				'selected_fields_mode'   => 'all',
				'failure_notifications'  => false,
				'webhooks'               => array(),
			);
		}

		/**
		 * Collect CF7 field names.
		 *
		 * @param int $form_id Optional form ID. 0 = all forms.
		 * @return array
		 */
		public static function get_form_field_options( $form_id = 0 ) {
			$options = array();
			$args    = array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);

			if ( absint( $form_id ) > 0 ) {
				$args['p'] = absint( $form_id );
			}

			$forms = get_posts( $args );
			foreach ( $forms as $form_post ) {
				$contact_form = wpcf7_contact_form( $form_post->ID );
				if ( ! $contact_form ) {
					continue;
				}
				$tags = $contact_form->scan_form_tags();
				foreach ( $tags as $tag ) {
					if ( empty( $tag->name ) || in_array( $tag->basetype, array( 'submit', 'acceptance', 'quiz', 'captcha', 'honeypot' ), true ) ) {
						continue;
					}
					$options[ $tag->name ] = $tag->name;
				}
			}

			// Preserve CF7 form field order (do not alphabetize).
			return $options;
		}

		/**
		 * Admin (global) settings
		 *
		 * @return array
		 */
		public function admin_settings() {
			$general_fields = array(
				'title'       => __( 'Webhook Settings', 'cf7apps' ),
				'description' => '',
				'notice'      => array(
					'type'  => 'notice',
					'class' => 'info',
					'text'  => sprintf( __( 'Stuck? Check our Documentation on %s', 'cf7apps' ), '<a href="https://cf7apps.com/docs/integration/webhook" target="_blank"><u>' . __( 'Webhook', 'cf7apps' ) . '</u></a>' ),
				),
				'is_enabled'  => array(
					'title'   => __( 'Enable Webhook App', 'cf7apps' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'global_settings' => array(
					'title'   => __( 'Global Settings', 'cf7apps' ),
					'type'    => 'checkbox',
					'default' => false,
					'help'    => __( 'Enable global settings, so they apply across all forms. These global settings will automatically sync with each individual form, where they can still be modified if needed.', 'cf7apps' ),
				),
			);

			$general_fields['webhook_url'] = array(
				'title'       => __( 'Webhook URL', 'cf7apps' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'Webhook URL',
				'class'       => 'lg',
				'required'    => true,
			);

			$general_fields['method'] = array(
				'title'   => __( 'Method', 'cf7apps' ),
				'type'    => 'select',
				'options' => self::get_method_options(),
				'default' => 'POST',
				'class'   => 'inline-select xs',
			);

			$general_fields['data_type'] = array(
				'title'   => __( 'Data Type', 'cf7apps' ),
				'type'    => 'select',
				'options' => array(
					'json' => __( 'JSON', 'cf7apps' ),
					'form' => __( 'Form Data', 'cf7apps' ),
				),
				'default' => 'json',
				'class'   => 'inline-select xs',
			);

			$general_fields['headers'] = array(
				'title'       => __( 'Request Headers', 'cf7apps' ),
				'type'        => 'textarea',
				'default'     => '{"Content-Type":"application/json"}',
				'placeholder' => '{"Authorization":"Bearer TOKEN"}',
				'help'        => __( 'Optional JSON object of headers to send with the webhook request.', 'cf7apps' ),
				'collapsible' => true,
				'pre_text'    => sprintf( __( 'When you need authentication/authorization.<br>You can add %s to your webhook request.', 'cf7apps' ), '<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers" target="_blank" style="color: #208aae; text-decoration: underline;"><u>HTTP Headers</u></a>' ),
				'post_text'   => __( 'One header by line, separated by colon. Example: Authorization: Bearer 99999999999999999999', 'cf7apps' ),
			);

			$general_fields['payload'] = array(
				'title'       => __( 'Request Body', 'cf7apps' ),
				'type'        => 'textarea',
				'default'     => '{"form_id":"{{form_id}}","fields":{{fields}}}',
				'help'        => __( 'Use {{form_id}} and {{fields}} placeholders.', 'cf7apps' ),
				'collapsible' => true,
				'pre_text'    => __( 'When you need customize your request.<br><br><strong style="color: #0d2149;">BE CAREFUL!</strong> This can break your integration (check your quotes).<br><strong style="color: #0d2149;">REMEMBER:</strong> You can change field name with webhook config: [email* your_email webhook:email]', 'cf7apps' ),
				'post_text'   => sprintf( __( 'To create your own body, you can replace values like in mail body (case sensitive).<br>Example: {"message": "Sir [your-name] from [your-city]." }<br>We will send your form data as JSON:<br><textarea style="width: 500px; min-height: 150px; font-family: monospace; background: #fff; border: 0.6px solid #8a8a8a; border-radius: 6px; padding: 10px; pointer-events: none;" readonly>%s</textarea><br>This is just an example of field names and will not reflect actual data or customizations.', 'cf7apps' ), "{\n    \"your-name\": \"??????\",\n    \"your-email\": \"??????\",\n    \"your-subject\": \"??????\",\n    \"your-message\": \"??????\"\n}" ),
			);

			$general_fields['save_settings'] = array(
				'type'  => 'save_button',
				'text'  => __( 'Save Settings', 'cf7apps' ),
				'class' => 'button-primary',
			);

			$settings = array(
				'setting_tabs' => $this->setting_tabs,
				'general'      => array(
					'fields' => array(
						'general' => $general_fields,
						'logs'    => array(),
					),
				),
			);

			return apply_filters( 'cf7apps_webhook_admin_settings', $settings );
		}

		/**
		 * Internal settings per form
		 *
		 * @return array
		 */
		public function internal_settings() {
			$enabled = $this->get_option( 'is_enabled' );

			$settings = array(
				'general' => array(
					'fields' => array(),
				),
			);

			$settings['general']['fields']['notice'] = array(
				'type'  => 'notice',
				'class' => 'info',
				'text'  => sprintf( __( 'Stuck? Check our Documentation on %s', 'cf7apps' ), '<a href="https://cf7apps.com/docs/integration/webhook" target="_blank"><u>' . __( 'Webhook', 'cf7apps' ) . '</u></a>' ),
			);

			$settings['general']['fields']['is_enabled'] = array(
				'title'    => __( 'Enable Webhook', 'cf7apps' ),
				'type'     => 'checkbox',
				'default'  => true,
				'disabled' => ! $enabled,
			);

			$settings['general']['fields']['webhook_url'] = array(
					'title'       => __( 'Webhook URL', 'cf7apps' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => 'Webhook URL',
					'disabled'    => ! $enabled,
					'class'       => 'lg',
					'required'    => true,
				);

				$settings['general']['fields']['method'] = array(
					'title'    => __( 'Method', 'cf7apps' ),
					'type'     => 'select',
					'options'  => self::get_method_options(),
					'default'  => 'POST',
					'disabled' => ! $enabled,
					'class'    => 'inline-select xs',
				);

				$settings['general']['fields']['data_type'] = array(
					'title'    => __( 'Data Type', 'cf7apps' ),
					'type'     => 'select',
					'options'  => array(
						'json' => __( 'JSON', 'cf7apps' ),
						'form' => __( 'Form Data', 'cf7apps' ),
					),
					'default'  => 'json',
					'disabled' => ! $enabled,
					'class'    => 'inline-select xs',
				);

				$settings['general']['fields']['headers'] = array(
					'title'       => __( 'Request Headers', 'cf7apps' ),
					'type'        => 'textarea',
					'default'     => '{"Content-Type":"application/json"}',
					'placeholder' => '{"Authorization":"Bearer TOKEN"}',
					'disabled'    => ! $enabled,
					'help'        => __( 'Optional JSON object of headers to send with the webhook request.', 'cf7apps' ),
					'collapsible' => true,
					'pre_text'    => sprintf( __( 'When you need authentication/authorization.<br>You can add %s to your webhook request.', 'cf7apps' ), '<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers" target="_blank" style="color: #208aae; text-decoration: underline;"><u>HTTP Headers</u></a>' ),
					'post_text'   => __( 'One header by line, separated by colon. Example: Authorization: Bearer 99999999999999999999', 'cf7apps' ),
				);

				$settings['general']['fields']['payload'] = array(
					'title'       => __( 'Request Body', 'cf7apps' ),
					'type'        => 'textarea',
					'default'     => '{"form_id":"{{form_id}}","fields":{{fields}}}',
					'disabled'    => ! $enabled,
					'help'        => __( 'Use {{form_id}} and {{fields}} placeholders.', 'cf7apps' ),
					'collapsible' => true,
					'pre_text'    => __( 'When you need customize your request.<br><br><strong style="color: #0d2149;">BE CAREFUL!</strong> This can break your integration (check your quotes).<br><strong style="color: #0d2149;">REMEMBER:</strong> You can change field name with webhook config: [email* your_email webhook:email]', 'cf7apps' ),
					'post_text'   => sprintf( __( 'To create your own body, you can replace values like in mail body (case sensitive).<br>Example: {"message": "Sir [your-name] from [your-city]." }<br><br>We will send your form data as JSON:<br><br><textarea style="width: 500px; min-height: 150px; font-family: monospace; background: #fff; border: 0.6px solid #8a8a8a; border-radius: 6px; padding: 10px; pointer-events: none;" readonly>%s</textarea><br><br>This is just an example of field names and will not reflect actual data or customizations.', 'cf7apps' ), "{\n    \"your-name\": \"??????\",\n    \"your-email\": \"??????\",\n    \"your-subject\": \"??????\",\n    \"your-message\": \"??????\"\n}" ),
				);

			$settings['general']['fields']['save_settings'] = array(
				'type'     => 'save_button',
				'text'     => __( 'Save Settings', 'cf7apps' ),
				'class'    => 'button-primary',
				'disabled' => ! $enabled,
			);

			return apply_filters( 'cf7apps_webhook_internal_settings', $settings );
		}

		/**
		 * Enrich settings payload for React.
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings                          = parent::get_settings();
			$settings['has_pro']               = function_exists( 'cf7apps_has_pro' ) && cf7apps_has_pro();
			$settings['webhook_field_options'] = self::get_form_field_options();
			$saved                             = $this->get_option( null );
			$settings['webhook_pro_settings']  = array(
				'selected_fields'       => isset( $saved['selected_fields'] ) ? (array) $saved['selected_fields'] : array(),
				'selected_fields_mode'  => isset( $saved['selected_fields_mode'] ) ? $saved['selected_fields_mode'] : 'all',
				'failure_notifications' => ! empty( $saved['failure_notifications'] ),
				'webhooks'              => isset( $saved['webhooks'] ) ? (array) $saved['webhooks'] : array(),
			);

			if ( empty( $settings['has_pro'] ) && ! empty( $settings['setting_tabs']['logs'] ) ) {
				unset( $settings['setting_tabs']['logs'] );
			}

			return apply_filters( 'cf7apps_webhook_get_settings', $settings, null );
		}

		/**
		 * Get Internal Settings with global fallback + Pro field options.
		 *
		 * HFCF7-650: When Global Settings is enabled and the form has no real
		 * custom webhook config, hydrate form UI (including Pro cards) from global.
		 *
		 * @param int $form_id Form ID.
		 * @return array
		 */
		public function get_internal_settings( $form_id ) {
			$settings = parent::get_internal_settings( $form_id );
			$settings['has_pro'] = function_exists( 'cf7apps_has_pro' ) && cf7apps_has_pro();
			$settings['webhook_field_options'] = self::get_form_field_options( $form_id );
			// Form editor uses a single settings panel (no Logs tab).
			$settings['setting_tabs'] = array();

			$form_settings = get_post_meta( $form_id, 'cf7apps_settings', true );
			if ( empty( $form_settings ) || ! is_array( $form_settings ) ) {
				$form_settings = array();
			}
			$form_webhook = isset( $form_settings[ $this->id ] ) && is_array( $form_settings[ $this->id ] )
				? $form_settings[ $this->id ]
				: array();

			$global_settings_enabled = $this->get_option( 'global_settings' );
			$use_global              = $global_settings_enabled && ! $this->has_custom_settings( $form_webhook );
			$global_settings         = $use_global ? $this->get_option( null ) : array();
			if ( ! is_array( $global_settings ) ) {
				$global_settings = array();
			}

			$source = $use_global && ! empty( $global_settings ) ? $global_settings : $form_webhook;
			$settings['webhook_pro_settings'] = $this->build_pro_settings_payload( $source );

			if ( ! $global_settings_enabled ) {
				return $settings;
			}

			if ( isset( $settings['admin_settings']['general']['fields']['is_enabled'] ) ) {
				if ( ! isset( $form_webhook['is_enabled'] ) ) {
					$settings['admin_settings']['general']['fields']['is_enabled']['checked'] = true;
				}
			}

			if ( ! $use_global || empty( $global_settings ) || ! isset( $settings['admin_settings']['general']['fields'] ) ) {
				return $settings;
			}

			$setting_fields = $settings['admin_settings']['general']['fields'];
			foreach ( $setting_fields as $field_key => $field ) {
				if ( in_array( $field_key, array( 'notice', 'save_settings', 'is_enabled', 'template' ), true ) ) {
					continue;
				}
				if ( ! is_array( $field ) || ! isset( $field['type'] ) ) {
					continue;
				}
				if ( ! isset( $global_settings[ $field_key ] ) ) {
					continue;
				}
				if ( 'checkbox' === $field['type'] ) {
					$settings['admin_settings']['general']['fields'][ $field_key ]['checked'] = (
						true === $global_settings[ $field_key ]
						|| 1 === $global_settings[ $field_key ]
						|| '1' === $global_settings[ $field_key ]
					);
				} elseif ( 'select' === $field['type'] ) {
					$settings['admin_settings']['general']['fields'][ $field_key ]['selected'] = $global_settings[ $field_key ];
				} else {
					$settings['admin_settings']['general']['fields'][ $field_key ]['value'] = $global_settings[ $field_key ];
				}
			}

			return $settings;
		}

		/**
		 * Build Pro webhook UI payload from saved/global settings.
		 *
		 * @param array $source Settings source.
		 * @return array
		 */
		private function build_pro_settings_payload( $source ) {
			$source   = is_array( $source ) ? $source : array();
			$defaults = $this->get_default_settings();
			$webhooks = array();

			if ( ! empty( $source['webhooks'] ) && is_array( $source['webhooks'] ) ) {
				foreach ( $source['webhooks'] as $webhook ) {
					if ( is_array( $webhook ) ) {
						$webhooks[] = $webhook;
					}
				}
			}

			$has_webhook_url = false;
			foreach ( $webhooks as $webhook ) {
				if ( ! empty( $webhook['webhook_url'] ) ) {
					$has_webhook_url = true;
					break;
				}
			}

			// Global app settings store a single endpoint; map into a Pro card.
			if ( ! $has_webhook_url && ! empty( $source['webhook_url'] ) ) {
				$webhooks = array(
					array(
						'id'                    => 'wh_global',
						'enabled'               => true,
						'webhook_url'           => (string) $source['webhook_url'],
						'method'                => ! empty( $source['method'] ) ? (string) $source['method'] : $defaults['method'],
						'data_type'             => ! empty( $source['data_type'] ) ? (string) $source['data_type'] : $defaults['data_type'],
						'selected_fields'       => isset( $source['selected_fields'] ) ? (array) $source['selected_fields'] : array(),
						'selected_fields_mode'  => isset( $source['selected_fields_mode'] ) ? (string) $source['selected_fields_mode'] : 'all',
						'parameter_map'         => isset( $source['parameter_map'] ) ? (array) $source['parameter_map'] : array(),
						'headers'               => isset( $source['headers'] ) ? (string) $source['headers'] : $defaults['headers'],
						'payload'               => isset( $source['payload'] ) ? (string) $source['payload'] : $defaults['payload'],
						'failure_notifications' => ! empty( $source['failure_notifications'] ),
					),
				);
			}

			return array(
				'selected_fields'       => isset( $source['selected_fields'] ) ? (array) $source['selected_fields'] : array(),
				'selected_fields_mode'  => isset( $source['selected_fields_mode'] ) ? (string) $source['selected_fields_mode'] : 'all',
				'failure_notifications' => ! empty( $source['failure_notifications'] ),
				'webhooks'              => $webhooks,
			);
		}

		/**
		 * Register this app
		 *
		 * @param array $apps Apps.
		 * @return array
		 */
		public static function initialize_module( $apps ) {
			$apps[] = __CLASS__;
			return $apps;
		}

		private static $hooks_registered = false;

		/**
		 * Boot hooks.
		 *
		 * @return void
		 */
		private function run() {
			if ( self::$hooks_registered ) {
				return;
			}
			self::$hooks_registered = true;
			add_action( 'wpcf7_mail_sent', array( $this, 'handle_webhook_submission' ), 10, 2 );
		}

		/**
		 * Whether settings look custom for a form.
		 *
		 * Empty / default-only values do not count as custom so Global Settings
		 * can still hydrate the form UI (HFCF7-650).
		 *
		 * @param array $individual_settings Settings.
		 * @return bool
		 */
		private function has_custom_settings( $individual_settings ) {
			if ( empty( $individual_settings ) || ! is_array( $individual_settings ) ) {
				return false;
			}

			if ( ! empty( $individual_settings['webhook_url'] ) ) {
				return true;
			}

			if ( ! empty( $individual_settings['webhooks'] ) && is_array( $individual_settings['webhooks'] ) ) {
				foreach ( $individual_settings['webhooks'] as $webhook ) {
					if ( is_array( $webhook ) && ! empty( $webhook['webhook_url'] ) ) {
						return true;
					}
				}
			}

			$defaults = $this->get_default_settings();
			foreach ( array( 'method', 'data_type', 'headers', 'payload' ) as $key ) {
				if ( ! isset( $individual_settings[ $key ] ) ) {
					continue;
				}
				if ( (string) $individual_settings[ $key ] !== (string) $defaults[ $key ] ) {
					return true;
				}
			}

			if (
				isset( $individual_settings['selected_fields'] )
				&& is_array( $individual_settings['selected_fields'] )
				&& ! empty( $individual_settings['selected_fields'] )
			) {
				return true;
			}

			if (
				isset( $individual_settings['selected_fields_mode'] )
				&& 'all' !== (string) $individual_settings['selected_fields_mode']
			) {
				return true;
			}

			return false;
		}

		/**
		 * Build endpoint configs for a form submission.
		 *
		 * HFCF7-654: Deduplicate identical webhook URLs so one submission
		 * does not fire the same endpoint multiple times.
		 *
		 * @param array $webhook_settings Resolved settings.
		 * @return array
		 */
		private function get_endpoint_configs( $webhook_settings ) {
			$has_pro = function_exists( 'cf7apps_has_pro' ) && cf7apps_has_pro();

			if ( $has_pro && ! empty( $webhook_settings['webhooks'] ) && is_array( $webhook_settings['webhooks'] ) ) {
				$endpoints = array();
				$seen      = array();

				foreach ( $webhook_settings['webhooks'] as $endpoint ) {
					if ( ! is_array( $endpoint ) ) {
						continue;
					}
					if ( isset( $endpoint['enabled'] ) && ! $endpoint['enabled'] ) {
						continue;
					}
					if ( empty( $endpoint['webhook_url'] ) ) {
						continue;
					}

					$url    = untrailingslashit( strtolower( trim( (string) $endpoint['webhook_url'] ) ) );
					$method = strtoupper( (string) ( $endpoint['method'] ?? 'POST' ) );
					$key    = $method . '|' . $url;

					if ( isset( $seen[ $key ] ) ) {
						continue;
					}
					$seen[ $key ] = true;

					$endpoints[] = wp_parse_args( $endpoint, $this->get_default_settings() );
				}
				if ( ! empty( $endpoints ) ) {
					return $endpoints;
				}
			}

			return array( wp_parse_args( $webhook_settings, $this->get_default_settings() ) );
		}

		/**
		 * Handle webhook submission.
		 *
		 * @param WPCF7_ContactForm $contact_form Form.
		 * @param array|null        $result       Result.
		 * @return void
		 */
		public function handle_webhook_submission( $contact_form, $result = null ) {
			if ( $result && isset( $result['status'] ) && 'mail_sent' !== $result['status'] ) {
				return;
			}

			// HFCF7-640: Preview Form must not trigger webhooks.
			if ( function_exists( 'cf7apps_is_form_preview_request' ) && cf7apps_is_form_preview_request() ) {
				return;
			}

			$form_id = $contact_form->id();
			if ( ! $form_id ) {
				return;
			}

			if ( ! $this->get_option( 'is_enabled' ) ) {
				return;
			}

			$individual_settings     = $this->get_individual_option( $form_id );
			$global_settings_enabled = $this->get_option( 'global_settings' );
			$has_custom_settings     = $this->has_custom_settings( $individual_settings );

			if ( $has_custom_settings ) {
				$webhook_settings = $individual_settings;
				if ( empty( $individual_settings['is_enabled'] ) ) {
					return;
				}
			} else {
				if ( ! $global_settings_enabled ) {
					return;
				}
				$global_settings = $this->get_option( null );
				if ( empty( $global_settings ) ) {
					return;
				}
				$webhook_settings = $global_settings;
				if ( isset( $individual_settings['is_enabled'] ) && ! $individual_settings['is_enabled'] ) {
					return;
				}
				$webhook_settings['is_enabled'] = true;
			}

			if ( empty( $webhook_settings['is_enabled'] ) ) {
				return;
			}

			$submission = WPCF7_Submission::get_instance();
			if ( ! $submission ) {
				return;
			}

			$posted_data = $submission->get_posted_data();
			if ( ! $posted_data ) {
				return;
			}

			// CF7 stores sha256 hashes in posted_data for file
			// fields; replace with base64 content while temp uploads still exist.
			$posted_data = $this->enrich_posted_data_with_file_contents( $posted_data, $submission );

			$form_name = $contact_form->title();
			$endpoints = $this->get_endpoint_configs( $webhook_settings );

			foreach ( $endpoints as $endpoint ) {
				// Form-level failure toggle applies to all endpoints when set.
				if ( ! empty( $webhook_settings['failure_notifications'] ) ) {
					$endpoint['failure_notifications'] = true;
				}
				$webhook_data = $this->prepare_webhook_data( $form_id, $posted_data, $endpoint );
				$this->send_webhook_request( $endpoint, $webhook_data, $form_id, $form_name );
			}
		}

		/**
		 * Replace CF7 file-field hashes with base64-encoded file contents.
		 *
		 * Contact Form 7 puts sha256 hashes of uploads into posted_data and keeps
		 * the real temp paths in uploaded_files() until the submission destructor.
		 * We only swap keys that have readable uploads; all other fields stay as-is.
		 *
		 * @since 3.8.1
		 * @see HPCF7IS-115
		 *
		 * @param array            $posted_data Posted data.
		 * @param WPCF7_Submission $submission  Submission instance.
		 * @return array
		 */
		private function enrich_posted_data_with_file_contents( $posted_data, $submission ) {
			if ( ! is_array( $posted_data ) || ! is_object( $submission ) || ! method_exists( $submission, 'uploaded_files' ) ) {
				return $posted_data;
			}

			$uploaded_files = $submission->uploaded_files();
			if ( empty( $uploaded_files ) || ! is_array( $uploaded_files ) ) {
				return $posted_data;
			}

			foreach ( $uploaded_files as $field_name => $paths ) {
				$field_name = (string) $field_name;
				if ( '' === $field_name ) {
					continue;
				}

				$encoded = array();
				foreach ( (array) $paths as $path ) {
					if ( ! is_string( $path ) || '' === $path || ! is_file( $path ) || ! is_readable( $path ) ) {
						continue;
					}

					$bytes = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( false === $bytes ) {
						continue;
					}

					$encoded[] = base64_encode( $bytes );
				}

				if ( empty( $encoded ) ) {
					continue;
				}

				// Match CF7 posted_data shape: one file → string; multi-file → array.
				$posted_data[ $field_name ] = ( 1 === count( $encoded ) ) ? $encoded[0] : $encoded;
			}

			return $posted_data;
		}

		/**
		 * Apply selected field filter + parameter map.
		 *
		 * @param array $mapped_data Mapped fields.
		 * @param array $settings    Endpoint settings.
		 * @return array
		 */
		private function filter_selected_fields( $mapped_data, $settings ) {
			$has_pro = function_exists( 'cf7apps_has_pro' ) && cf7apps_has_pro();
			if ( ! $has_pro ) {
				return $mapped_data;
			}

			$mode = isset( $settings['selected_fields_mode'] ) ? $settings['selected_fields_mode'] : 'all';
			if ( 'selected' === $mode ) {
				$selected = isset( $settings['selected_fields'] ) ? (array) $settings['selected_fields'] : array();
				if ( empty( $selected ) ) {
					$mapped_data = array();
				} else {
					$filtered = array();
					foreach ( $selected as $field_name ) {
						if ( array_key_exists( $field_name, $mapped_data ) ) {
							$filtered[ $field_name ] = $mapped_data[ $field_name ];
						}
					}
					$mapped_data = $filtered;
				}
			}

			$parameter_map = isset( $settings['parameter_map'] ) && is_array( $settings['parameter_map'] ) ? $settings['parameter_map'] : array();
			if ( ! empty( $parameter_map ) ) {
				$remapped = $mapped_data;
				foreach ( $parameter_map as $from => $to ) {
					$from = (string) $from;
					$to   = (string) $to;
					if ( '' === $from || '' === $to || ! array_key_exists( $from, $remapped ) ) {
						continue;
					}
					$remapped[ $to ] = $remapped[ $from ];
					if ( $from !== $to ) {
						unset( $remapped[ $from ] );
					}
				}
				$mapped_data = $remapped;
			}

			return $mapped_data;
		}

		/**
		 * Prepare webhook data based on settings.
		 *
		 * @param int   $form_id     Form ID.
		 * @param array $posted_data Posted data.
		 * @param array $settings    Settings.
		 * @return array|string
		 */
		/**
		 * Escape a value for substitution inside a JSON string placeholder ([field-name]).
		 *
		 * @param mixed $value Field value.
		 * @return string
		 */
		private function escape_json_placeholder_value( $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ', ', array_map( 'strval', $value ) );
			} elseif ( ! is_scalar( $value ) && null !== $value ) {
				$encoded = wp_json_encode( $value );
				return false === $encoded ? '' : $encoded;
			} elseif ( null === $value ) {
				$value = '';
			} else {
				$value = (string) $value;
			}

			$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( false === $encoded || strlen( $encoded ) < 2 || '"' !== $encoded[0] ) {
				return $value;
			}

			return substr( $encoded, 1, -1 );
		}

		private function prepare_webhook_data( $form_id, $posted_data, $settings ) {
			$mapped_data = $this->apply_field_mapping( $posted_data, $form_id );
			$mapped_data = $this->filter_selected_fields( $mapped_data, $settings );

			if ( ! empty( $settings['payload'] ) ) {
				$payload_template = $settings['payload'];
				$payload_template = str_replace( '{{form_id}}', $form_id, $payload_template );
				$payload_template = str_replace( '{{fields}}', wp_json_encode( $mapped_data ), $payload_template );

				foreach ( $mapped_data as $field_name => $field_value ) {
					$placeholder = '[' . $field_name . ']';
					$replace       = $this->escape_json_placeholder_value( $field_value );
					$payload_template = str_replace( $placeholder, $replace, $payload_template );
				}

				$decoded_payload = json_decode( $payload_template, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					return $decoded_payload;
				}
				return $payload_template;
			}

			return array(
				'form_id'   => $form_id,
				'fields'    => $mapped_data,
				'timestamp' => current_time( 'mysql' ),
				'site_url'  => home_url(),
			);
		}

		/**
		 * Apply field name mapping based on form configuration.
		 *
		 * @param array $posted_data Posted data.
		 * @param int   $form_id     Form ID.
		 * @return array
		 */
		private function apply_field_mapping( $posted_data, $form_id ) {
			$mapped_data = $posted_data;
			$form        = wpcf7_contact_form( $form_id );
			if ( ! $form ) {
				return $mapped_data;
			}

			$form_content = $form->prop( 'form' );
			preg_match_all( '/\[([^\]]+)\s+([^\s]+)\s+webhook:([^\]]+)\]/', $form_content, $matches, PREG_SET_ORDER );

			if ( ! empty( $matches ) ) {
				foreach ( $matches as $match ) {
					$original_field = $match[2];
					$mapped_field   = $match[3];
					if ( isset( $mapped_data[ $original_field ] ) ) {
						$mapped_data[ $mapped_field ] = $mapped_data[ $original_field ];
						unset( $mapped_data[ $original_field ] );
					}
				}
			}

			return $mapped_data;
		}

		/**
		 * Send webhook request and optionally log / notify.
		 *
		 * @param array  $settings  Endpoint settings.
		 * @param mixed  $data      Payload.
		 * @param int    $form_id   Form ID.
		 * @param string $form_name Form title.
		 * @return array
		 */
		public function send_webhook_request( $settings, $data, $form_id = 0, $form_name = '' ) {
			$webhook_url = $settings['webhook_url'] ?? '';
			$result      = array(
				'status'           => 'failed',
				'response_code'    => '',
				'response_message' => '',
			);

			if ( empty( $webhook_url ) ) {
				return $result;
			}

			$allowed_methods = array_keys( self::get_method_options() );
			$method          = strtoupper( $settings['method'] ?? 'POST' );
			if ( ! in_array( $method, $allowed_methods, true ) ) {
				$method = 'POST';
			}
			$data_type = isset( $settings['data_type'] ) ? $settings['data_type'] : 'json';
			$headers   = $this->prepare_headers( $settings );

			$user_provided_content_type = false;
			if ( ! empty( $settings['headers'] ) ) {
				$custom = is_string( $settings['headers'] ) ? json_decode( $settings['headers'], true ) : $settings['headers'];
				if ( is_array( $custom ) ) {
					foreach ( $custom as $hk => $hv ) {
						if ( strtolower( $hk ) === 'content-type' ) {
							$user_provided_content_type = true;
							break;
						}
					}
				}
			}

			if ( ! $user_provided_content_type ) {
				if ( 'form' === $data_type && 'GET' !== $method ) {
					$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
				} else {
					$headers['Content-Type'] = 'application/json';
				}
			}

			$args = array(
				'method'  => $method,
				'headers' => $headers,
				'timeout' => 30,
			);

			if ( ! empty( $settings['username'] ) && ! empty( $settings['password'] ) ) {
				$args['headers']['Authorization'] = 'Basic ' . base64_encode( $settings['username'] . ':' . $settings['password'] );
			}

			if ( 'GET' === $method ) {
				$query    = is_array( $data ) ? http_build_query( $data ) : 'payload=' . rawurlencode( $data );
				$url      = $webhook_url . ( false === strpos( $webhook_url, '?' ) ? '?' : '&' ) . $query;
				$response = wp_remote_request( $url, $args );
			} else {
				if ( 'json' === $data_type ) {
					$args['body'] = is_string( $data ) ? $data : wp_json_encode( $data );
				} elseif ( is_array( $data ) ) {
					$args['body'] = $data;
				} else {
					$decoded = json_decode( $data, true );
					$args['body'] = ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) ? $decoded : $data;
				}
				$response = wp_remote_request( $webhook_url, $args );
			}

			if ( is_wp_error( $response ) ) {
				$result['response_message'] = $response->get_error_message();
				error_log( 'Webhook request failed: ' . $result['response_message'] );
			} else {
				$code = (int) wp_remote_retrieve_response_code( $response );
				$body = wp_remote_retrieve_body( $response );
				$result['response_code']    = (string) $code;
				$result['response_message'] = $body;
				$result['status']           = ( $code >= 200 && $code < 300 ) ? 'success' : 'failed';
			}

			$logged_payload = $data;
			if ( 'form' === $data_type && is_array( $data ) ) {
				$logged_payload = http_build_query( $data, '', '&', PHP_QUERY_RFC3986 );
			} elseif ( 'form' === $data_type && is_string( $data ) ) {
				$decoded = json_decode( $data, true );
				if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
					$logged_payload = http_build_query( $decoded, '', '&', PHP_QUERY_RFC3986 );
				}
			}

			$context = array(
				'form_id'     => absint( $form_id ),
				'form_name'   => $form_name,
				'webhook_url' => $webhook_url,
				'method'      => $method,
				'payload'     => $logged_payload,
				'result'      => $result,
				'settings'    => $settings,
			);

			/**
			 * Fires after a webhook request completes.
			 *
			 * @param array $context Request context.
			 */
			do_action( 'cf7apps_webhook_request_complete', $context );

			if ( 'failed' === $result['status'] ) {
				do_action( 'cf7apps_webhook_request_failed', $context );
				$this->maybe_send_failure_notification( $context );
			}

			return $result;
		}

		/**
		 * Email admin on webhook failure when enabled.
		 *
		 * HFCF7-656: Send a proper HTML email template (global + form-level).
		 *
		 * @param array $context Context.
		 * @return void
		 */
		private function maybe_send_failure_notification( $context ) {
			if ( ! function_exists( 'cf7apps_has_pro' ) || ! cf7apps_has_pro() ) {
				return;
			}

			$settings = isset( $context['settings'] ) && is_array( $context['settings'] )
				? $context['settings']
				: array();
			if ( empty( $settings['failure_notifications'] ) ) {
				return;
			}

			$admin_email = get_option( 'admin_email' );
			if ( ! $admin_email || ! is_email( $admin_email ) ) {
				return;
			}

			$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
			$subject   = sprintf(
				/* translators: %s: site name */
				__( '[%s] Webhook request failed', 'cf7apps' ),
				$site_name
			);

			$body = $this->build_failure_notification_email( $context, $site_name );
			if ( '' === $body ) {
				return;
			}

			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			wp_mail( $admin_email, $subject, $body, $headers );
		}

		/**
		 * Build HTML body for webhook failure notification.
		 *
		 * @param array  $context   Request context.
		 * @param string $site_name Site name.
		 * @return string
		 */
		private function build_failure_notification_email( $context, $site_name ) {
			$result = isset( $context['result'] ) && is_array( $context['result'] )
				? $context['result']
				: array();

			$payload_preview = '';
			if ( isset( $context['payload'] ) ) {
				if ( is_string( $context['payload'] ) ) {
					$payload_preview = $context['payload'];
				} else {
					$encoded = wp_json_encode( $context['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
					$payload_preview = false === $encoded ? '' : $encoded;
				}
			}

			$response_message = isset( $result['response_message'] )
				? (string) $result['response_message']
				: '';

			// Keep email readable for very large responses/payloads.
			if ( strlen( $response_message ) > 4000 ) {
				$response_message = substr( $response_message, 0, 4000 ) . "\n…";
			}
			if ( strlen( $payload_preview ) > 6000 ) {
				$payload_preview = substr( $payload_preview, 0, 6000 ) . "\n…";
			}

			$logo_path = CF7APPS_PLUGIN_DIR . '/includes/apps/weekly-summary/assets/images/email/cf7apps-logo.png';
			$logo_url  = file_exists( $logo_path )
				? CF7APPS_PLUGIN_DIR_URL . '/includes/apps/weekly-summary/assets/images/email/cf7apps-logo.png'
				: '';

			$logs_url = admin_url( 'admin.php?page=cf7apps#/settings/webhook/2' );

			$form_name         = isset( $context['form_name'] ) ? (string) $context['form_name'] : '';
			$form_id           = isset( $context['form_id'] ) ? absint( $context['form_id'] ) : 0;
			$webhook_url       = isset( $context['webhook_url'] ) ? (string) $context['webhook_url'] : '';
			$method            = isset( $context['method'] ) ? strtoupper( (string) $context['method'] ) : 'POST';
			$response_code     = isset( $result['response_code'] ) ? (string) $result['response_code'] : '';
			$occurred_at       = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

			$template = CF7APPS_PLUGIN_DIR . '/includes/apps/webhook/templates/failure-notification-email.php';
			if ( ! file_exists( $template ) ) {
				return '';
			}

			ob_start();
			include $template;
			$html = ob_get_clean();

			return is_string( $html ) ? $html : '';
		}

		/**
		 * Prepare headers for webhook request.
		 *
		 * @param array $settings Settings.
		 * @return array
		 */
		private function prepare_headers( $settings ) {
			$headers = array(
				'Content-Type' => 'application/json',
			);

			if ( ! empty( $settings['headers'] ) ) {
				$custom_headers = $settings['headers'];
				if ( is_string( $custom_headers ) ) {
					$decoded_headers = json_decode( $custom_headers, true );
					if ( json_last_error() === JSON_ERROR_NONE ) {
						$custom_headers = $decoded_headers;
					}
				}
				if ( is_array( $custom_headers ) ) {
					$headers = array_merge( $headers, $custom_headers );
				}
			}

			return $headers;
		}

		/**
		 * Retry a stored log payload.
		 *
		 * @param int $log_id Log ID.
		 * @return array|false
		 */
		public static function retry_log( $log_id ) {
			if ( ! class_exists( 'CF7Apps_Pro_Webhook_Logs' ) ) {
				return false;
			}

			$log = CF7Apps_Pro_Webhook_Logs::get( $log_id );
			if ( ! $log ) {
				return false;
			}

			$payload = json_decode( $log['payload'], true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$payload = $log['payload'];
			}

			$sender = new self();
			return $sender->send_webhook_request(
				array(
					'webhook_url' => $log['webhook_url'],
					'method'      => $log['method'],
					'data_type'   => 'json',
					'headers'     => '{"Content-Type":"application/json"}',
				),
				$payload,
				absint( $log['form_id'] ),
				$log['form_name']
			);
		}
	}

	add_filter( 'cf7apps_apps', array( CF7Apps_Webhook::class, 'initialize_module' ) );
endif;
