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
});

/* Feedback*/
jQuery(document).ready(function($) {
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