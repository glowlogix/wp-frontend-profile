jQuery(document).ready(function($) {
    $('.fbwfp-all-forms a.delete-form').click(function(e) {
        if (confirm('Are you sure you want to delete form')) {} else {
            e.preventDefault();
        }
    });
});

jQuery(document).ready(function($) {

    /**
     * Popup close button
     */
    $('body').on('click', '.popup--visible button', function() {
        var trigger = $(this).attr('data-for');
        $('.' + trigger).removeClass('popup--visible');
    });
});