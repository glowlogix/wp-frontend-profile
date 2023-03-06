<?php
/**
 * @package wp-front-end-profile
 * Register shortcode.
 */

defined('ABSPATH') || exit;

/**
 * Function wpfep_register_shortcode()
 * register the a shortcode to display on frontend.
 */
function wpfep_register_shortcode()
{
    ob_start();
    wpfep_show_profile();
    return ob_get_clean();
}
add_shortcode('wpfep', 'wpfep_register_shortcode');
