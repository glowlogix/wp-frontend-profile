/* Password show/hide toggle for WP Frontend Profile
 * Adds a toggle button to password inputs inside .wpfep-form-box
 */
(function ($) {
    $(function () {
        var eye = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';
        var eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.45 21.45 0 0 1 5.06-6.06"/><path d="M1 1l22 22"/></svg>';

        $('.wpfep-form-box input[type="password"]').each(function () {
            var $input = $(this);
            // skip if already wrapped
            if ($input.parent().hasClass('wpfep-input-wrap')) return;

            // wrap input for positioning
            $input.wrap('<div class="wpfep-input-wrap"></div>');
            var $wrap = $input.parent();

            // create toggle button
            var $btn = $('<button type="button" class="wpfep-password-toggle" aria-label="Show password"></button>');
            $btn.html(eye);
            $wrap.append($btn);

            $btn.on('click', function (e) {
                e.preventDefault();
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $btn.addClass('visible');
                    $btn.attr('aria-label', 'Hide password');
                    $btn.html(eyeOff);
                } else {
                    $input.attr('type', 'password');
                    $btn.removeClass('visible');
                    $btn.attr('aria-label', 'Show password');
                    $btn.html(eye);
                }
            });
        });
    });
})(jQuery);
