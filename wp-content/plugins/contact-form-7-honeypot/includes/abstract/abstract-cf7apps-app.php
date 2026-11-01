<?php

abstract class CF7Apps_App {

    /**
     * App ID
     * 
     * @since 3.0.0
     */
    public $id;

    /**
     * Priority
     * 
     * @since 3.0.0
     */
    public $priority = 999;

    /**
     * App Title
     * 
     * @since 3.0.0
     */
    public $title;

    /**
     * App Description
     * 
     * @since 3.0.0
     */
    public $description;

    /**
     * App Icon
     * 
     * @since 3.0.0
     */
    public $icon;

    /**
     * Has Settings
     * 
     * @since 3.0.0
     */
    public $has_admin_settings = false;

    /**
     * Has Internal Settings
     *
     * @since 3.2.0
     * @var bool $has_internal_settings Has Internal Settings
     */
    public $has_internal_settings = false;

    /**
     * Is Pro
     * 
     * @since 3.0.0
     */
    public $is_pro = false;

    /**
     * Show Beta tag on dashboard card.
     *
     * @since 3.8.0
     */
    public $is_beta = false;

    /**
     * Is enabled by default
     * 
     * @since 3.0.0
     */
    public $by_default_enabled = false;

    /**
     * Documentation URL
     * 
     * @since 3.0.0
     */
    public $documentation_url;

    /**
     * Parent Menu
     * 
     * @since 3.0.0
     */
    public $parent_menu = false;

    /**
     * Parent menu sort order in the admin sidebar (lower = higher).
     *
     * @since 3.8.0
     */
    public $parent_menu_order = 50;

    /**
     * Setting Tabs
     * 
     * @since 3.0.0
     */
    public $setting_tabs = array();

    /**
     * Admin Settings
     * 
     * @since 3.0.0
     */
    public function admin_settings() {
        return array();
    }

    /**
     * Internal Settings
     *
     * @since 3.2.0
     * @return array
     */
    public function internal_settings() {
        return array();
    }

    /**
     * Saved Settings
     * 
     * @since 3.0.0
     */
    public $options = false;

    /**
     * Normalize saved checkbox/toggle values from mixed storage formats.
     *
     * @since 3.7.3
     *
     * @param mixed $value Saved setting value.
     * @return bool
     */
    protected function is_setting_enabled( $value ) {
        return true === $value || 1 === $value || '1' === $value;
    }

    /**
     * Map a saved option value onto admin field props (value/checked/selected).
     *
     * Radio fields store string keys (e.g. external_url), not booleans.
     *
     * @since 3.8.1
     *
     * @param array $field       Field schema.
     * @param mixed $saved_value Stored option value.
     * @return array<string, mixed>
     */
    protected function map_saved_value_to_admin_field( array $field, $saved_value ) {
        if ( ! isset( $field['type'] ) ) {
            return array( 'value' => $saved_value );
        }

        switch ( $field['type'] ) {
            case 'checkbox':
                return array( 'checked' => $this->is_setting_enabled( $saved_value ) );
            case 'radio':
                return array( 'value' => is_scalar( $saved_value ) ? (string) $saved_value : '' );
            case 'select':
                return array( 'selected' => $saved_value );
            default:
                return array( 'value' => $saved_value );
        }
    }

    /**
     * Map field defaults onto admin field props.
     *
     * @since 3.8.1
     *
     * @param array $field  Field schema.
     * @param array $_field Field config from admin_settings (includes default).
     * @return array<string, mixed>
     */
    protected function map_default_to_admin_field( array $field, array $_field ) {
        if ( ! isset( $field['type'] ) ) {
            return array();
        }

        switch ( $field['type'] ) {
            case 'checkbox':
                return array( 'checked' => ( isset( $_field['default'] ) && $_field['default'] ) ? true : false );
            case 'radio':
                return array( 'value' => ( isset( $_field['default'] ) ) ? (string) $_field['default'] : '' );
            case 'select':
                return array( 'selected' => ( isset( $_field['default'] ) ) ? $_field['default'] : '' );
            default:
                return array( 'value' => ( isset( $_field['default'] ) ) ? $_field['default'] : '' );
        }
    }

    /**
     * Get Option
     * 
     * @param string $key Option key to get
     * 
     * @since 3.0.0
     */
    public function get_option( $key = false ) {
        $default_settings = array();
        $default_settings['is_enabled'] = $this->by_default_enabled;
        
        $options = get_option( 'cf7apps_settings', $default_settings );

        if( $options && ! empty( $options[$this->id] ) ) {
            if( $key ) {
                return isset( $options[$this->id][$key] ) ? $options[$this->id][$key] : false;
            }
            else {
                return $options[$this->id];
            }
        }

        return false;
    }

    public function get_individual_option( $id, $key = false ) {
        $settings = get_post_meta( $id, 'cf7apps_settings', true );

        if ( empty( $settings ) ) {
            return array();
        }

        if ( isset( $settings[ $this->id ] ) ) {
            if ( $key ) {
                if ( isset( $settings[ $this->id ][ $key ] ) ) {
                    return $settings[ $this->id ][ $key ];
                } else {
                    return false;
                }
            } else {
                return $settings[ $this->id ];
            }
        }

        return array();
    }

    /**
     * Get Settings
     * 
     * @since 3.0.0
     */
    public function get_settings() {
        $default_settings = array();
        $default_settings['is_enabled'] = $this->by_default_enabled;
        
        $this->options = get_option( 'cf7apps_settings', $default_settings );

        $settings = new StdClass;
        $settings->id = $this->id;
        $settings->priority = $this->priority;
        $settings->title = $this->title;
        $settings->description = $this->description;
        $settings->icon = $this->icon;
        $settings->has_admin_settings = $this->has_admin_settings;
        $settings->is_pro = $this->is_pro;
        $settings->is_beta = $this->is_beta;
        $settings->by_default_enabled = $this->by_default_enabled;
        $settings->documentation_url = $this->documentation_url;
        $settings->parent_menu = $this->parent_menu;
        $settings->setting_tabs = $this->setting_tabs;
        $settings = (array)$settings;

        if( $this->has_admin_settings ) {
            $settings['admin_settings'] = $this->admin_settings();
            if( isset( $this->options[$this->id] ) ) {
                
                if( empty( $this->setting_tabs ) ) {
                    $setting_fields = $settings['admin_settings']['general']['fields'];
                        
                    foreach( $setting_fields as $field_key => $field ) {
                        if( array_key_exists( $field_key, $this->options[$this->id] ) ) {
                            $mapped = $this->map_saved_value_to_admin_field(
                                $field,
                                $this->options[ $this->id ][ $field_key ]
                            );
                            $settings['admin_settings']['general']['fields'][ $field_key ] = array_merge(
                                $settings['admin_settings']['general']['fields'][ $field_key ],
                                $mapped
                            );
                        } else {
                            // Set default value
                            $_field = $settings['admin_settings']['general']['fields'][$field_key];

                            if( isset( $field['type'] ) ) {
                                $mapped = $this->map_default_to_admin_field( $field, $_field );
                                $settings['admin_settings']['general']['fields'][ $field_key ] = array_merge(
                                    $settings['admin_settings']['general']['fields'][ $field_key ],
                                    $mapped
                                );
                            }
                        }

                        if ( isset( $field['sub_fields'] ) ) {
                            foreach ( $field['sub_fields'] as $sub_field_key => $sub_field ) {
                                if ( array_key_exists( $sub_field_key, $this->options[$this->id] ) ) {
                                    $mapped = $this->map_saved_value_to_admin_field(
                                        $sub_field,
                                        $this->options[ $this->id ][ $sub_field_key ]
                                    );
                                    $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ] = array_merge(
                                        $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ],
                                        $mapped
                                    );
                                }
                                else {
                                    // Set default value
                                    $_sub_field = $settings['admin_settings']['general']['fields'][$field_key]['sub_fields'][$sub_field_key];

                                    if( isset( $sub_field['type'] ) ) {
                                        $mapped = $this->map_default_to_admin_field( $sub_field, $_sub_field );
                                        $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ] = array_merge(
                                            $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ],
                                            $mapped
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
                else {
                    foreach( $this->setting_tabs as $tab_key => $tab_title ) {
                        $settings_tab_fields = $settings['admin_settings']['general']['fields'][$tab_key];
                        
                        foreach( $settings_tab_fields as $field_key => $field ) {
                            if ( 'template' === $field_key || ! is_array( $field ) || ! isset( $field['type'] ) ) {
                                continue;
                            }

                            if( array_key_exists( $field_key, $this->options[$this->id] ) ) {
                                $mapped = $this->map_saved_value_to_admin_field(
                                    $field,
                                    $this->options[ $this->id ][ $field_key ]
                                );
                                $settings['admin_settings']['general']['fields'][ $tab_key ][ $field_key ] = array_merge(
                                    $settings['admin_settings']['general']['fields'][ $tab_key ][ $field_key ],
                                    $mapped
                                );
                            }
                            else {
                                // Set default value
                                $_field = $settings['admin_settings']['general']['fields'][$tab_key][$field_key];

                                if( isset( $field['type'] ) ) {
                                    $mapped = $this->map_default_to_admin_field( $field, $_field );
                                    $settings['admin_settings']['general']['fields'][ $tab_key ][ $field_key ] = array_merge(
                                        $settings['admin_settings']['general']['fields'][ $tab_key ][ $field_key ],
                                        $mapped
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        $is_enabled = isset( $this->options[$this->id] ) ? array( 'is_enabled' => $this->options[$this->id]['is_enabled'] ) : $default_settings;
        $settings = array_merge( $settings, $is_enabled );

        return $settings;
    }

    /**
     * Get Internal Settings
     *
     * @since 3.2.0
     */
    public function get_internal_settings( $form_id ) {
        $default_settings = array();
        $default_settings['is_enabled'] = $this->by_default_enabled;
        $settings = get_post_meta( $form_id, 'cf7apps_settings', true );
        if ( empty( $settings ) ) {
            $settings = array();
        }

        $this->options = $settings;
        // merge default settings to option
        $this->options = array_merge( $this->options, $default_settings );

        $settings = new StdClass;
        $settings->id = $this->id;
        $settings->priority = $this->priority;
        $settings->title = $this->title;
        $settings->description = $this->description;
        $settings->icon = $this->icon;
        $settings->has_internal_settings = $this->has_internal_settings;
        $settings->is_pro = $this->is_pro;
        $settings->is_beta = $this->is_beta;
        $settings->by_default_enabled = $this->by_default_enabled;
        $settings->documentation_url = $this->documentation_url;
        $settings->parent_menu = $this->parent_menu;
        $settings->setting_tabs = $this->setting_tabs;
        $settings = (array)$settings;

        if ( $this->has_internal_settings ) {
            $settings['admin_settings'] = $this->internal_settings();
            $setting_fields             = $settings['admin_settings']['general']['fields'];
            $saved_app_settings         = ( isset( $this->options[ $this->id ] ) && is_array( $this->options[ $this->id ] ) )
                ? $this->options[ $this->id ]
                : array();

            foreach ( $setting_fields as $field_key => $field ) {
                if ( 'template' === $field_key || ! is_array( $field ) || ! isset( $field['type'] ) ) {
                    continue;
                }

                if ( array_key_exists( $field_key, $saved_app_settings ) ) {
                    if ( 'checkbox' === $field['type'] ) {
                        $settings['admin_settings']['general']['fields'][ $field_key ]['checked'] = cf7apps_is_internal_setting_truthy( $saved_app_settings[ $field_key ] );
                    } elseif ( 'radio' === $field['type'] ) {
                        $saved = $saved_app_settings[ $field_key ];
                        $settings['admin_settings']['general']['fields'][ $field_key ]['value'] = is_scalar( $saved ) ? (string) $saved : '';
                    } elseif ( 'select' === $field['type'] ) {
                        $settings['admin_settings']['general']['fields'][ $field_key ]['selected'] = $saved_app_settings[ $field_key ];
                    } else {
                        $settings['admin_settings']['general']['fields'][ $field_key ]['value'] = $saved_app_settings[ $field_key ];
                    }
                } else {
                    $_field = $settings['admin_settings']['general']['fields'][ $field_key ];
                    if ( isset( $field['type'] ) ) {
                        if ( 'checkbox' === $field['type'] ) {
                            $settings['admin_settings']['general']['fields'][ $field_key ]['checked'] = ( isset( $_field['default'] ) && $_field['default'] ) ? true : false;
                        } elseif ( 'radio' === $field['type'] ) {
                            $settings['admin_settings']['general']['fields'][ $field_key ]['value'] = ( isset( $_field['default'] ) ) ? (string) $_field['default'] : '';
                        } elseif ( 'select' === $field['type'] ) {
                            $settings['admin_settings']['general']['fields'][ $field_key ]['selected'] = ( isset( $_field['default'] ) ) ? $_field['default'] : '';
                        } else {
                            $settings['admin_settings']['general']['fields'][ $field_key ]['value'] = ( isset( $_field['default'] ) ) ? $_field['default'] : '';
                        }
                    }
                }

                if ( isset( $field['sub_fields'] ) ) {
                    foreach ( $field['sub_fields'] as $sub_field_key => $sub_field ) {
                        if ( array_key_exists( $sub_field_key, $saved_app_settings ) ) {
                            if ( 'checkbox' === $sub_field['type'] ) {
                                $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['checked'] = cf7apps_is_internal_setting_truthy( $saved_app_settings[ $sub_field_key ] );
                            } elseif ( 'radio' === $sub_field['type'] ) {
                                $saved = $saved_app_settings[ $sub_field_key ];
                                $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['value'] = is_scalar( $saved ) ? (string) $saved : '';
                            } elseif ( 'select' === $sub_field['type'] ) {
                                $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['selected'] = $saved_app_settings[ $sub_field_key ];
                            } else {
                                $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['value'] = $saved_app_settings[ $sub_field_key ];
                            }
                        } else {
                            $_sub_field = $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ];

                            if ( isset( $sub_field['type'] ) ) {
                                if ( 'checkbox' === $sub_field['type'] ) {
                                    $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['checked'] = ( isset( $_sub_field['default'] ) && $_sub_field['default'] ) ? true : false;
                                } elseif ( 'radio' === $sub_field['type'] ) {
                                    $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['value'] = ( isset( $_sub_field['default'] ) ) ? (string) $_sub_field['default'] : '';
                                } elseif ( 'select' === $sub_field['type'] ) {
                                    $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['selected'] = ( isset( $_sub_field['default'] ) ) ? $_sub_field['default'] : '';
                                } else {
                                    $settings['admin_settings']['general']['fields'][ $field_key ]['sub_fields'][ $sub_field_key ]['value'] = ( isset( $_sub_field['default'] ) ) ? $_sub_field['default'] : '';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $settings;
    }
}