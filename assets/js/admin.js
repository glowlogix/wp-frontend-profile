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