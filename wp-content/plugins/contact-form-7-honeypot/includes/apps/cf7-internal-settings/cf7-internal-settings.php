<?php
/**
 * CF7Apps Internal Settings Apps Class
 *
 * @since 3.2.0
 * @package CF7Apps
 */
if ( ! class_exists( 'CF7Apps_Internal_Settings_Apps' ) ) :
    /**
     * CF7Apps Internal Settings Apps Class
     *
     * @since 3.2.0
     */
    class CF7Apps_Internal_Settings_Apps {
        /**
         * The singleton instance of the class.
         *
         * @since 3.2.0
         * @var CF7Apps_Internal_Settings_Apps $instance The single instance of the class.
         */
        private static $instance;

        /**
         * Private constructor to prevent direct instantiation.
         *
         * @since 3.2.0
         */
        private function __construct() {
            add_action( 'wp_ajax_cf7apps_preview_form', array( $this, 'ajax_preview_form' ) );
            add_action( 'template_redirect', array( $this, 'render_form_preview' ) );

            // HFCF7-640: Block mail / processing if a preview submit somehow reaches CF7.
            add_filter( 'wpcf7_skip_mail', array( $this, 'maybe_skip_mail_on_preview' ), 10, 2 );
            add_action( 'wpcf7_before_send_mail', array( $this, 'maybe_abort_preview_submission' ), 1, 3 );

            if ( isset( $_GET['page'] ) ) {
                // Run on existing form edit screen (page=wpcf7&post=ID)
                if ( 'wpcf7' === $_GET['page'] && isset( $_GET['post'] ) ) {
                    $this->run();
                }

                // Also run on "Add New" form screen (page=wpcf7-new, no post ID yet)
                if ( 'wpcf7-new' === $_GET['page'] ) {
                    $this->run();
                }
            }
        }

        /**
         * Skip CF7 mail when submission originates from Preview Form.
         *
         * @since 3.9.0
         *
         * @param bool              $skip         Whether to skip mail.
         * @param WPCF7_ContactForm $contact_form Contact form.
         * @return bool
         */
        public function maybe_skip_mail_on_preview( $skip, $contact_form = null ) {
            unset( $contact_form );

            if ( function_exists( 'cf7apps_is_form_preview_request' ) && cf7apps_is_form_preview_request() ) {
                return true;
            }

            return $skip;
        }

        /**
         * Abort CF7 submission when it originates from Preview Form.
         *
         * @since 3.9.0
         *
         * @param WPCF7_ContactForm $contact_form Contact form.
         * @param bool              $abort        Abort flag.
         * @param WPCF7_Submission  $submission   Submission.
         * @return void
         */
        public function maybe_abort_preview_submission( $contact_form, &$abort, $submission ) {
            unset( $contact_form, $submission );

            if ( function_exists( 'cf7apps_is_form_preview_request' ) && cf7apps_is_form_preview_request() ) {
                $abort = true;
            }
        }

        /**
         * Run the main functionality of the class.
         *
         * @since 3.2.0
         */
        private function run() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        }

        /**
         * Enqueue admin scripts and styles.
         *
         * @since 3.2.0
         * @param string $hook The current admin page hook.
         */
        public function admin_enqueue_scripts( $hook ) {
            // Form editor screens only (CF7 6.2+ uses wpcf7-dashboard_* screen ids).
            $is_cf7_edit_screen = in_array(
                (string) $hook,
                array(
                    'toplevel_page_wpcf7', // CF7 < 6.2 forms list / edit
                    'contact_page_wpcf7',
                    'contact_page_wpcf7-new',
                    'wpcf7-dashboard_page_wpcf7',
                    'wpcf7-dashboard_page_wpcf7-new',
                ),
                true
            );

            if ( $is_cf7_edit_screen ) {

                $form_id  = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
                $internal_settings_enabled = cf7apps_has_enabled_internal_settings_app();
                $options                 = get_option( 'cf7apps_settings' );
                $redirection_app_enabled = false;
                $webhook_app_enabled     = false;

                if ( ! empty( $options ) ) {
                    if ( isset( $options['cf7-redirection'] ) ) {
                        if ( isset( $options['cf7-redirection']['is_enabled'] ) && $options['cf7-redirection']['is_enabled'] ) {
                            $redirection_app_enabled = true;
                        }
                    }
                    if ( isset( $options['webhook'] ) ) {
                        if ( isset( $options['webhook']['is_enabled'] ) && $options['webhook']['is_enabled'] ) {
                            $webhook_app_enabled = true;
                        }
                    }
                }

                $dir_url  = CF7APPS_PLUGIN_DIR_URL . '/includes/apps/cf7-internal-settings/';
                $dir_path = CF7APPS_PLUGIN_DIR . '/includes/apps/cf7-internal-settings/';
                $assets_ver = CF7APPS_VERSION;
                $js_path    = $dir_path . 'assets/js/enqueue-wrapper.js';
                $css_path   = $dir_path . 'assets/css/gradient.css';
                if ( file_exists( $js_path ) ) {
                    $assets_ver .= '-' . (string) filemtime( $js_path );
                }
                wp_enqueue_style( 'cf7apps-gradient', $dir_url . 'assets/css/gradient.css', array(), file_exists( $css_path ) ? CF7APPS_VERSION . '-' . filemtime( $css_path ) : $assets_ver );
                wp_enqueue_script( 'enqueue-wrapper', $dir_url . 'assets/js/enqueue-wrapper.js', array( 'jquery' ), $assets_ver, true );

                if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
                    wpcf7_enqueue_scripts();
                }

                if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
                    wpcf7_enqueue_styles();
                }

                wp_localize_script(
                    'enqueue-wrapper',
                    'cf7appsWrapperObjects',
                    array(
                        'cf7appsInternalSettingsEnabled' => $internal_settings_enabled,
                        'cf7appsRedirectionEnabled'      => $redirection_app_enabled,
                        'cf7appsWebhookEnabled'          => $webhook_app_enabled,
                        'formId'                         => $form_id,
                        'ajaxUrl'                        => admin_url( 'admin-ajax.php' ),
                        'logoUrl'                        => CF7APPS_PLUGIN_DIR_URL . '/assets/images/logo.png',
                        'previewBaseUrl'                 => home_url( '/' ),
                        'previewNonce'                   => wp_create_nonce( 'cf7apps_preview_form' ),
                        'i18n'                           => array(
                            'previewButton' => __( 'Preview Form', 'cf7apps' ),
                            'previewTitle'  => __( 'Form Preview', 'cf7apps' ),
                            'previewNote'   => __( 'powered by CF7 Apps', 'cf7apps' ),
                            'saveNotice'    => __( 'New changes will appear in preview after saving the form.', 'cf7apps' ),
                            'loading'       => __( 'Loading preview...', 'cf7apps' ),
                            'close'         => __( 'Close preview', 'cf7apps' ),
                            'missingForm'   => __( 'Save the form first to preview it.', 'cf7apps' ),
                            'error'         => __( 'Preview could not be loaded.', 'cf7apps' ),
                        ),
                    )
                );

                if ( file_exists( $dir_path . 'build/index.asset.php' ) ) {
                    $asset = include $dir_path . 'build/index.asset.php';

                    wp_enqueue_style(
                        'cf7apps-internal-settings-app',
                        $dir_url . 'build/index.css',
                        array_filter(
                            $asset['dependencies'],
                            function ( $dependency ) {
                                return wp_style_is( $dependency, 'registered' );
                            }
                        ),
                        $asset['version']
                    );

                    wp_enqueue_script(
                        'cf7apps-internal-settings-app',
                        $dir_url . 'build/index.js',
                        array_filter(
                            $asset['dependencies'],
                            function ( $dependency ) {
                                return wp_script_is( $dependency, 'registered' );
                            }
                        ),
                        $asset['version'],
                        true
                    );

                    wp_set_script_translations(
                        'cf7apps-internal-settings-app',
                        'cf7apps',
                        CF7APPS_PLUGIN_DIR . '/languages'
                    );

                    wp_localize_script(
                        'cf7apps-internal-settings-app',
                        'CF7AppsInternalSettings',
                        apply_filters(
                            'cf7apps_internal_settings_localize_script',
                            array(
                            'assetsURL' => CF7APPS_PLUGIN_DIR_URL . '/assets/',
                            'restURL'   => rest_url(),
                            'nonce'     => wp_create_nonce( 'wp_rest' ),
                            'formID'    => $form_id,
                            'appIndexURL' => admin_url( 'admin.php?page=cf7apps' ),
                            'formEditorAssetsURL' => $dir_url . 'assets/images/form-editor/',
                            'formPromoAssetsURL'  => $dir_url . 'assets/images/form-promo/',
                            'postSmtpIconUrl'     => CF7APPS_PLUGIN_DIR_URL . '/assets/images/dashboard/recommended/post-smtp.gif',
                            'pluginVersion' => CF7APPS_VERSION,
                            'paymentGlobalCurrency' => array(
                                'stripe' => cf7apps_get_global_payment_currency( 'stripe' ),
                                'paypal' => cf7apps_get_global_payment_currency( 'paypal' ),
                            ),
                            'hasPro' => cf7apps_has_pro(),
                            )
                        )
                    );

                    /**
                     * Fires after CF7 Apps form-editor assets are enqueued.
                     *
                     * @since 3.8.0
                     */
                    do_action( 'cf7apps_admin_assets_enqueued' );
                }
            }
        }

        /**
         * Return rendered Contact Form 7 markup for the preview modal.
         *
         * @since 3.7.3
         * @return void
         */
        public function ajax_preview_form() {
            check_ajax_referer( 'cf7apps_preview_form', 'nonce' );

            if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
                wp_send_json_error(
                    array(
                        'message' => __( 'Permission denied.', 'cf7apps' ),
                    ),
                    403
                );
            }

            $form_id = isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0;

            if ( ! $form_id ) {
                wp_send_json_error(
                    array(
                        'message' => __( 'Save the form first to preview it.', 'cf7apps' ),
                    ),
                    400
                );
            }

            $contact_form = WPCF7_ContactForm::get_instance( $form_id );

            if ( ! $contact_form ) {
                wp_send_json_error(
                    array(
                        'message' => __( 'Form not found.', 'cf7apps' ),
                    ),
                    404
                );
            }

            wp_send_json_success(
                array(
                    'html'  => $contact_form->form_html(),
                    'title' => get_the_title( $form_id ),
                )
            );
        }

        /**
         * Render a frontend-style preview page for the iframe modal.
         *
         * @since 3.7.3
         * @return void
         */
        public function render_form_preview() {
            $form_id = isset( $_GET['cf7apps_form_preview'] ) ? absint( wp_unslash( $_GET['cf7apps_form_preview'] ) ) : 0;

            if ( ! $form_id ) {
                return;
            }

            if ( ! is_user_logged_in() || ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
                status_header( 403 );
                wp_die( esc_html__( 'You are not allowed to preview this form.', 'cf7apps' ) );
            }

            $contact_form = WPCF7_ContactForm::get_instance( $form_id );

            if ( ! $contact_form ) {
                status_header( 404 );
                wp_die( esc_html__( 'Form not found.', 'cf7apps' ) );
            }

            if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
                wpcf7_enqueue_scripts();
            }

            if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
                wpcf7_enqueue_styles();
            }

            // HFCF7-640: Preview is display-only — do not load redirection / integration scripts.
            add_action(
                'wp_print_scripts',
                static function () {
                    wp_dequeue_script( 'cf7apps-redirection' );
                    wp_deregister_script( 'cf7apps-redirection' );
                },
                100
            );

            // Mark preview forms so any accidental REST/AJAX submit can be detected server-side.
            add_filter(
                'wpcf7_form_hidden_fields',
                static function ( $fields ) {
                    $fields['_cf7apps_form_preview'] = '1';
                    return $fields;
                }
            );

            add_filter( 'show_admin_bar', '__return_false' );
            if ( function_exists( 'show_admin_bar' ) ) {
                show_admin_bar( false );
            }

            nocache_headers();
            status_header( 200 );
            ?>
            <!doctype html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo( 'charset' ); ?>">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <?php wp_head(); ?>
                <style>
                    body.cf7apps-form-preview-page {
                        margin: 0;
                        padding: 0 24px 24px;
                        background: #fff;
                    }

                    .cf7apps-form-preview-page__wrap {
                        max-width: 100%;
                    }

                    .cf7apps-form-preview-notice {
                        margin: 16px 0 20px;
                        padding: 12px 14px;
                        border: 1px solid #c3c4c7;
                        border-left: 4px solid #2271b1;
                        border-radius: 4px;
                        background: #f0f6fc;
                        color: #1d2327;
                        font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    }

                    .cf7apps-form-preview-page .wpcf7-submit,
                    .cf7apps-form-preview-page input[type="submit"],
                    .cf7apps-form-preview-page button[type="submit"] {
                        pointer-events: none;
                        opacity: 0.55;
                        cursor: not-allowed;
                    }
                </style>
            </head>
            <body <?php body_class( 'cf7apps-form-preview-page' ); ?>>
                <div class="cf7apps-form-preview-page__wrap">
                    <div class="cf7apps-form-preview-notice" role="status">
                        <?php echo esc_html__( 'Preview only - this form cannot be submitted. Sample data is shown for layout.', 'cf7apps' ); ?>
                    </div>
                    <?php echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', $form_id ) ); ?>
                </div>
                <script>
                (function () {
                    function sampleFor(el) {
                        var name = (el.getAttribute('name') || '').toLowerCase();
                        var type = (el.getAttribute('type') || el.tagName || '').toLowerCase();
                        if (type === 'email' || name.indexOf('email') !== -1) return 'john@example.com';
                        if (type === 'tel' || name.indexOf('phone') !== -1 || name.indexOf('tel') !== -1) return '+1 555 0100';
                        if (type === 'url' || name.indexOf('url') !== -1 || name.indexOf('website') !== -1) return 'https://example.com';
                        if (type === 'number') return '1';
                        if (type === 'date') return '2026-01-15';
                        if (name.indexOf('subject') !== -1) return 'Sample subject';
                        if (name.indexOf('message') !== -1 || el.tagName === 'TEXTAREA') return 'This is sample preview text.';
                        if (name.indexOf('name') !== -1) return 'John Doe';
                        return 'Sample';
                    }

                    function fillSampleData(root) {
                        if (!root) return;
                        root.querySelectorAll('input, textarea, select').forEach(function (el) {
                            var type = (el.getAttribute('type') || '').toLowerCase();
                            if (el.disabled || el.readOnly) return;
                            if (['hidden', 'submit', 'button', 'file', 'image', 'reset'].indexOf(type) !== -1) return;
                            if (type === 'checkbox' || type === 'radio') {
                                if (!el.checked && el.name) {
                                    var group = root.querySelectorAll('[name="' + el.name.replace(/"/g, '\\"') + '"]');
                                    if (group.length && !Array.prototype.some.call(group, function (g) { return g.checked; })) {
                                        group[0].checked = true;
                                    }
                                }
                                return;
                            }
                            if (el.tagName === 'SELECT') {
                                if (el.options.length > 1 && el.selectedIndex <= 0) {
                                    el.selectedIndex = 1;
                                }
                                return;
                            }
                            if (!el.value) {
                                el.value = sampleFor(el);
                            }
                        });
                    }

                    function blockSubmit(root) {
                        if (!root) return;
                        root.querySelectorAll('form').forEach(function (form) {
                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }, true);
                        });
                        root.querySelectorAll('.wpcf7-submit, input[type="submit"], button[type="submit"]').forEach(function (btn) {
                            btn.setAttribute('disabled', 'disabled');
                            btn.setAttribute('aria-disabled', 'true');
                            btn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }, true);
                        });
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        var wrap = document.querySelector('.cf7apps-form-preview-page__wrap');
                        fillSampleData(wrap);
                        blockSubmit(wrap);
                    });

                    document.addEventListener('wpcf7submit', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }, true);
                })();
                </script>
                <?php wp_footer(); ?>
            </body>
            </html>
            <?php
            exit;
        }

        /**
         * Get the singleton instance of the class.
         *
         * @since 3.2.0
         * @return CF7Apps_Internal_Settings_Apps
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }

    CF7Apps_Internal_Settings_Apps::get_instance();
endif;