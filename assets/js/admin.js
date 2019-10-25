jQuery( document ).ready(function() {
    jQuery('.wpfep-help-tip').tooltip().tooltip('destroy').tooltip({
        content: function () {
            return jQuery(this).prop('title');
        },
        tooltipClass: 'wpfep-ui-tooltip',
        position: {
            my: "left top+15", at: "left bottom", collision: "flipfit"
        },
        show: null,
        close: function (event, ui) {
            ui.tooltip.hover(

                function () {
                    jQuery(this).stop(true).fadeTo(400, 1);
                },

                function () {
                    jQuery(this).fadeOut("400", function () {
                        jQuery(this).remove();
                    })
                });
        }
    });

    /* Feedback*/
    jQuery('.wpfep-main-dashboard-review-ask').css('display', 'block');

    jQuery('.wpfep-main-dashboard-review-ask').on('click', function(event) {
        if (jQuery(event.srcElement).hasClass('notice-dismiss')) {
            var data = 'Ask_Review_Date=3&action=wpfep_hide_review_ask';
            jQuery.post(ajaxurl, data, function() {});
        }
    });

    jQuery('.wpfep-review-ask-yes').on('click', function() {
        jQuery('.wpfep-review-ask-feedback-text').removeClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-starting-text').addClass('wpfep-hidden');

        jQuery('.wpfep-review-ask-no-thanks').removeClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-review').removeClass('wpfep-hidden');

        jQuery('.wpfep-review-ask-not-really').addClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-yes').addClass('wpfep-hidden');

        var data = 'Ask_Review_Date=7&action=wpfep_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});
    });

    jQuery('.wpfep-review-ask-not-really').on('click', function() {
        jQuery('.wpfep-review-ask-review-text').removeClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-starting-text').addClass('wpfep-hidden');

        jQuery('.wpfep-review-ask-feedback-form').removeClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-actions').addClass('wpfep-hidden');

        var data = 'Ask_Review_Date=1000&action=wpfep_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});
    });

    jQuery('.wpfep-review-ask-no-thanks').on('click', function() {
        var data = 'Ask_Review_Date=1000&action=wpfep_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});

        jQuery('.wpfep-main-dashboard-review-ask').css('display', 'none');
    });

    jQuery('.wpfep-review-ask-review').on('click', function() {
        jQuery('.wpfep-review-ask-feedback-text').addClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-thank-you-text').removeClass('wpfep-hidden');

        var data = 'Ask_Review_Date=1000&action=wpfep_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});
    });

    jQuery('.wpfep-review-ask-send-feedback').on('click', function() {
        var Feedback = jQuery('.wpfep-review-ask-feedback-explanation textarea').val();
        var EmailAddress = jQuery('.wpfep-review-ask-feedback-explanation input[name="feedback_email_address"]').val();
        var data = 'Feedback=' + Feedback + '&EmailAddress=' + EmailAddress + '&action=wpfep_send_feedback';
        jQuery.post(ajaxurl, data, function() {});

        var data = 'Ask_Review_Date=1000&action=wpfep_hide_review_ask';
        jQuery.post(ajaxurl, data, function() {});

        jQuery('.wpfep-review-ask-feedback-form').addClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-review-text').addClass('wpfep-hidden');
        jQuery('.wpfep-review-ask-thank-you-text').removeClass('wpfep-hidden');
        jQuery('.wpfep-main-dashboard-review-ask').delay( 1000 ).fadeOut();
    });
});

/* System Status*/
jQuery( function ( $ ) {

    var wpfepSystemStatus = {
        init: function() {
            $( document.body )
                .on( 'click', 'a.debug-report', this.generateReport )
                .on( 'click', '#copy-for-system-support', this.copyReport )
                .on( 'aftercopy', '#copy-for-system-support', this.copySuccess )
        },

        /**
         * Generate system status report.
         *
         * @return {Bool}
         */
        generateReport: function() {
            var report = '';

            $( '.wpfep-status-table thead, .wpfep-status-table tbody' ).each( function() {
                if ( $( this ).is( 'thead' ) ) {
                    var label = $( this ).find( 'th:eq(0)' ).data( 'export-label' ) || $( this ).text();
                    report = report + '\n### ' + $.trim( label ) + ' ###\n\n';
                } else {
                    $( 'tr', $( this ) ).each( function() {
                        var label       = $( this ).find( 'td:eq(0)' ).data( 'export-label' ) || $( this ).find( 'td:eq(0)' ).text();
                        var the_name    = $.trim( label ).replace( /(<([^>]+)>)/ig, '' ); // Remove HTML.

                        // Find value
                        var $value_html = $( this ).find( 'td:eq(1)' ).clone();
                        $value_html.find( '.private' ).remove();
                        $value_html.find( '.dashicons-yes' ).replaceWith( '&#10004;' );
                        $value_html.find( '.dashicons-no-alt, .dashicons-warning' ).replaceWith( '&#10060;' );

                        // Format value
                        var the_value   = $.trim( $value_html.text() );
                        var value_array = the_value.split( ', ' );

                        if ( value_array.length > 1 ) {
                            // If value have a list of plugins ','.
                            // Split to add new line.
                            var temp_line ='';
                            $.each( value_array, function( key, line ) {
                                temp_line = temp_line + line + '\n';
                            });

                            the_value = temp_line;
                        }

                        report = report + '' + the_name + ': ' + the_value + '\n';
                    });
                }
            });

            try {
                $( '#debug-report' ).slideDown();
                $( '#debug-report' ).find( 'textarea' ).val( '`' + report + '`' ).focus().select();
                $( this ).fadeOut();
                return false;
            } catch ( e ) {
                console.log( e );
            }

            return false;
        },

        /**
         * Copy for report.
         *
         * @param {Object} evt Copy event.
         */
        copyReport: function( evt ) {
            wpfepClearClipboard();
            wpfepSetClipboard( $( '#debug-report' ).find( 'textarea' ).val(), $( this ) );
            evt.preventDefault();
        },

        /**
         * Display a "Copied!" alert when success copying
         */
        copySuccess: function() {
            alert('copied!');
        },

        /**
         * Displays the copy error message when failure copying.
         */
        copyFail: function() {
            $( '.copy-error' ).removeClass( 'hidden' );
            $( '#debug-report' ).find( 'textarea' ).focus().select();
        }
    };

    wpfepSystemStatus.init();

    function wpfepSetClipboard( data, $el ) {
        if ( 'undefined' === typeof $el ) {
            $el = jQuery( document );
        }
        var $temp_input = jQuery( '<textarea style="opacity:0">' );
        jQuery( 'body' ).append( $temp_input );
        $temp_input.val( data ).select();

        $el.trigger( 'beforecopy' );
        try {
            document.execCommand( 'copy' );
            $el.trigger( 'aftercopy' );
        } catch ( err ) {
            $el.trigger( 'aftercopyfailure' );
        }

        $temp_input.remove();
    }

    function wpfepClearClipboard() {
        wpfepSetClipboard( '' );
    }
});