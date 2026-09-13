/* Password show/hide toggle for WP Frontend Profile
 * Adds a toggle button to password inputs inside .wpfep-form-box
 */
(function ($) {
    $(function () {
        function createSvgIcon(isVisible) {
            var namespace = 'http://www.w3.org/2000/svg';
            var svg = document.createElementNS(namespace, 'svg');

            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('width', '18');
            svg.setAttribute('height', '18');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '1.6');
            svg.setAttribute('stroke-linecap', 'round');
            svg.setAttribute('stroke-linejoin', 'round');

            if (isVisible) {
                var hiddenPath = document.createElementNS(namespace, 'path');
                hiddenPath.setAttribute('d', 'M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.45 21.45 0 0 1 5.06-6.06');
                svg.appendChild(hiddenPath);

                var slashPath = document.createElementNS(namespace, 'path');
                slashPath.setAttribute('d', 'M1 1l22 22');
                svg.appendChild(slashPath);

                return svg;
            }

            var eyePath = document.createElementNS(namespace, 'path');
            eyePath.setAttribute('d', 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z');
            svg.appendChild(eyePath);

            var circle = document.createElementNS(namespace, 'circle');
            circle.setAttribute('cx', '12');
            circle.setAttribute('cy', '12');
            circle.setAttribute('r', '3');
            svg.appendChild(circle);

            return svg;
        }

        function setButtonIcon($button, isVisible) {
            $button.empty().append(createSvgIcon(isVisible));
        }

        $('.wpfep-form-box input[type="password"]').each(function () {
            var $input = $(this);
            // skip if already wrapped
            if ($input.parent().hasClass('wpfep-input-wrap')) return;

            // wrap input for positioning
            var wrapper = document.createElement('div');
            wrapper.className = 'wpfep-input-wrap';
            $input.wrap(wrapper);
            var $wrap = $input.parent();

            // create toggle button
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'wpfep-password-toggle';
            button.setAttribute('aria-label', 'Show password');

            var $btn = $(button);
            setButtonIcon($btn, false);
            $wrap.append($btn);

            $btn.on('click', function (e) {
                e.preventDefault();
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $btn.addClass('visible');
                    $btn.attr('aria-label', 'Hide password');
                    setButtonIcon($btn, true);
                } else {
                    $input.attr('type', 'password');
                    $btn.removeClass('visible');
                    $btn.attr('aria-label', 'Show password');
                    setButtonIcon($btn, false);
                }
            });
        });
    });
})(jQuery);
