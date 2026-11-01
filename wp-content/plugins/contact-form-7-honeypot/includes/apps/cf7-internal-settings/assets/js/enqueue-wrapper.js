( function( $ ) {
//     // Prevent false "Changes you made may not be saved" warnings
//     // This runs immediately when the script loads, before React Router initializes
//     // The issue: React Router's HashRouter blocks first navigation attempt on CF7 edit page
    ( function() {
        let isFirstNavigation = true;
        
        const preventFirstWarning = function( e ) {
            // Only prevent the warning on the FIRST navigation attempt
            // This matches the user's experience - warning only on first click
            if ( isFirstNavigation ) {
                isFirstNavigation = false;
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.returnValue = '';
                return '';
            }
            // For subsequent navigations, allow normal behavior
        };
        
        // Use capture phase to intercept BEFORE React Router's handlers
        window.addEventListener( 'beforeunload', preventFirstWarning, true );
    } )();

    function getPreviewConfig() {
        const config = window.cf7appsWrapperObjects || {};

        return {
            formId: parseInt( config.formId || 0, 10 ) || 0,
            ajaxUrl: config.ajaxUrl || window.ajaxurl || '',
            previewBaseUrl: config.previewBaseUrl || window.location.origin || '',
            nonce: config.previewNonce || '',
            logoUrl: config.logoUrl || '',
            i18n: config.i18n || {},
        };
    }

    function getPreviewText( key, fallback ) {
        const config = getPreviewConfig();

        if ( config.i18n && config.i18n[ key ] ) {
            return config.i18n[ key ];
        }

        return fallback;
    }

    /**
     * HFCF7-640: Preview is only available after the form is saved (has a post ID).
     */
    function canShowPreviewButton() {
        return getPreviewConfig().formId > 0;
    }

    /**
     * HFCF7-641: track real editor edits only (ignore CF7 Apps React fields inside the form).
     */
    let formEditorDirty = false;

    function isCf7EditorField( el ) {
        if ( ! el || ! el.closest ) {
            return false;
        }

        if ( ! el.closest( '#wpcf7-admin-form-element' ) ) {
            return false;
        }

        // Exclude CF7 Apps UI, preview button, and tag-generator dialogs.
        if (
            el.closest(
                '#cf7apps-root, #cf7apps-form-promo-root, .cf7apps-internal-settings, .cf7apps-form-promo-root, .cf7apps-preview-trigger-wrap, dialog, .tag-generator-panel'
            )
        ) {
            return false;
        }

        return true;
    }

    function markFormEditorDirty( event ) {
        if ( isCf7EditorField( event.target ) ) {
            formEditorDirty = true;
        }
    }

    function hasUnsavedFormChanges() {
        return !! formEditorDirty;
    }

    function updatePreviewSaveNotice( $backdrop ) {
        const $notice = $backdrop.find( '.cf7apps-preview-modal__notice' );

        if ( ! $notice.length ) {
            return;
        }

        if ( hasUnsavedFormChanges() ) {
            $notice.removeAttr( 'hidden' ).css( 'display', '' );
        } else {
            $notice.attr( 'hidden', 'hidden' ).css( 'display', 'none' );
        }
    }

    function buildPreviewButton() {
        return (
            '<div class="cf7apps-preview-trigger-wrap">' +
                '<button type="button" class="button button-secondary cf7apps-preview-trigger" id="cf7apps-preview-trigger">' +
                    '<span class="cf7apps-preview-trigger__label">' + getPreviewText( 'previewButton', 'Preview Form' ) + '</span>' +
                '</button>' +
            '</div>'
        );
    }

    function buildPreviewModal() {
        const config = getPreviewConfig();
        const logoMarkup = config.logoUrl
            ? '<div class="cf7apps-preview-modal__branding"><span class="cf7apps-preview-modal__branding-label">Powered by</span><img src="' + config.logoUrl + '" alt="CF7 Apps" class="cf7apps-preview-modal__logo" /></div>'
            : '<span class="cf7apps-preview-modal__watermark">' + getPreviewText( 'previewNote', 'powered by CF7 Apps' ) + '</span>';

        return (
            '<div class="cf7apps-preview-backdrop" id="cf7apps-preview-backdrop" role="dialog" aria-modal="true" aria-labelledby="cf7apps-preview-title" hidden>' +
                '<div class="cf7apps-preview-modal">' +
                    '<div class="cf7apps-preview-modal__header">' +
                        '<h2 class="cf7apps-preview-modal__title" id="cf7apps-preview-title">' + getPreviewText( 'previewTitle', 'Form Preview' ) + '</h2>' +
                        '<button type="button" class="cf7apps-preview-modal__close" aria-label="' + getPreviewText( 'close', 'Close preview' ) + '"><span class="cf7apps-preview-modal__close-icon" aria-hidden="true"></span></button>' +
                    '</div>' +
                    '<div class="cf7apps-preview-modal__body">' +
                        '<div class="cf7apps-preview-modal__notice" hidden style="display:none">' + getPreviewText( 'saveNotice', 'New changes will appear in preview after saving the form.' ) + '</div>' +
                        '<div class="cf7apps-preview-modal__state is-loading" id="cf7apps-preview-loading">' + getPreviewText( 'loading', 'Loading preview...' ) + '</div>' +
                        '<div class="cf7apps-preview-modal__state is-error" id="cf7apps-preview-error" hidden></div>' +
                        '<div class="cf7apps-preview-modal__content" id="cf7apps-preview-content"></div>' +
                    '</div>' +
                    '<div class="cf7apps-preview-modal__footer">' +
                        logoMarkup +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function ensurePreviewModal() {
        if ( ! $( '#cf7apps-preview-backdrop' ).length ) {
            $( 'body' ).append( buildPreviewModal() );
        }

        return $( '#cf7apps-preview-backdrop' );
    }

    function setPreviewState( $backdrop, state, message ) {
        const $loading = $backdrop.find( '#cf7apps-preview-loading' );
        const $error = $backdrop.find( '#cf7apps-preview-error' );
        const $content = $backdrop.find( '#cf7apps-preview-content' );

        $loading.attr( 'hidden', true );
        $error.attr( 'hidden', true ).text( '' );
        $content.empty();

        if ( state === 'loading' ) {
            $loading.attr( 'hidden', false ).text( getPreviewText( 'loading', 'Loading preview...' ) );
        } else if ( state === 'error' ) {
            $error.attr( 'hidden', false ).text( message || getPreviewText( 'error', 'Preview could not be loaded.' ) );
        } else if ( state === 'content' ) {
            $content.html( message || '' );
        }
    }

    function buildPreviewUrl( formId ) {
        const config = getPreviewConfig();

        if ( ! config.previewBaseUrl || ! formId ) {
            return '';
        }

        const previewUrl = new URL( config.previewBaseUrl, window.location.origin );
        previewUrl.searchParams.set( 'cf7apps_form_preview', String( formId ) );
        previewUrl.searchParams.set( 'cf7apps_preview_t', String( Date.now() ) );

        return previewUrl.toString();
    }

    function openPreviewModal() {
        const config = getPreviewConfig();
        const $backdrop = ensurePreviewModal();

        $backdrop.removeAttr( 'hidden' );
        $( 'body' ).addClass( 'cf7apps-preview-open' );
        updatePreviewSaveNotice( $backdrop );

        if ( ! config.formId ) {
            setPreviewState( $backdrop, 'error', getPreviewText( 'missingForm', 'Save the form first to preview it.' ) );
            return;
        }

        const previewUrl = buildPreviewUrl( config.formId );

        if ( ! previewUrl ) {
            setPreviewState( $backdrop, 'error', getPreviewText( 'missingForm', 'Save the form first to preview it.' ) );
            return;
        }

        setPreviewState( $backdrop, 'loading' );

        $backdrop.find( '#cf7apps-preview-content' ).html(
            '<iframe class="cf7apps-preview-modal__iframe" id="cf7apps-preview-iframe" src="' + previewUrl + '" loading="eager"></iframe>'
        );
        const $iframe = $backdrop.find( '#cf7apps-preview-iframe' );
        const $loading = $backdrop.find( '#cf7apps-preview-loading' );
        const $error = $backdrop.find( '#cf7apps-preview-error' );

        $iframe.on( 'load', function() {
            $loading.attr( 'hidden', true );

            try {
                const iframeDocument = this.contentDocument || this.contentWindow.document;
                if ( iframeDocument && iframeDocument.body && /You are not allowed to preview this form|Form not found/i.test( iframeDocument.body.textContent || '' ) ) {
                    $error.attr( 'hidden', false ).text( getPreviewText( 'error', 'Preview could not be loaded.' ) );
                }
            } catch ( err ) {
                // Same-origin requests should allow access; ignore if browser blocks inspection.
            }
        } );
    }

    function closePreviewModal() {
        const $backdrop = $( '#cf7apps-preview-backdrop' );

        if ( ! $backdrop.length ) {
            return;
        }

        $backdrop.attr( 'hidden', true );
        $( 'body' ).removeClass( 'cf7apps-preview-open' );
    }

    const $editor = $( '#contact-form-editor' );
    const $cf7Editor = $( '#wpcf7-contact-form-editor' );
    const $minorPublishingActions = $cf7Editor.find( '#minor-publishing-actions' ).first();
    const $duplicateButton = $minorPublishingActions.find( 'input.copy.button, .copy.button' ).first();

    if ( $editor.length && ! $editor.find( '#cf7apps-root' ).length ) {
        const $layout = $( '<div class="cf7apps-cf7-edit-layout"></div>' );

        $layout.append(
            '<div id="cf7apps-root" class="cf7apps-internal-settings"></div>'
        );
        $layout.append(
            '<div id="cf7apps-form-promo-root" class="cf7apps-form-promo-root"></div>'
        );

        $editor.append( $layout );
    }

    if (
        $cf7Editor.length &&
        $minorPublishingActions.length &&
        canShowPreviewButton() &&
        ! $( '#cf7apps-preview-trigger' ).length
    ) {
        if ( $duplicateButton.length ) {
            $duplicateButton.after( buildPreviewButton() );
        } else {
            $minorPublishingActions.append( buildPreviewButton() );
        }
        ensurePreviewModal();
    }

    if ( '1' === cf7appsWrapperObjects.cf7appsInternalSettingsEnabled || '1' === cf7appsWrapperObjects.cf7appsRedirectionEnabled || '1' === cf7appsWrapperObjects.cf7appsWebhookEnabled ) {
        $( '#form-panel' ).css( 'position', 'relative' ).append(
            `<a  id="cf7apps-target-btn" style="position:absolute; top: 20px;right: 20px;padding: 0 20px;" href="#cf7apps-root" class="components-button cf7apps-btn tertiary-primary">CF7 Apps Settings</a>`
        );

        $( document ).on( 'click', '#cf7apps-target-btn', function( e ) {
            e.preventDefault();

            // target element
            const target = $( $( this ).attr( 'href' ) );

            if ( target.length ) {
                // scroll to the target element
                $( 'html, body' ).animate(
                    {
                        scrollTop: target.offset().top - 100,
                    },
                    'fast'
                );
            }
        } );
    }

    $( document ).on( 'input change', '#wpcf7-admin-form-element', markFormEditorDirty );

    $( document ).on( 'submit', '#wpcf7-admin-form-element', function() {
        formEditorDirty = false;
    } );

    $( document ).on( 'click', '#cf7apps-preview-trigger', function( e ) {
        e.preventDefault();
        openPreviewModal();
    } );

    $( document ).on( 'click', '.cf7apps-preview-modal__close', function( e ) {
        e.preventDefault();
        closePreviewModal();
    } );

    $( document ).on( 'click', '#cf7apps-preview-backdrop', function( e ) {
        if ( e.target === this ) {
            closePreviewModal();
        }
    } );

    $( document ).on( 'keydown', function( e ) {
        if ( 'Escape' === e.key && $( '#cf7apps-preview-backdrop:visible' ).length ) {
            closePreviewModal();
        }
    } );
} )( jQuery );