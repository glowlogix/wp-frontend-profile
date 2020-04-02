<?php
/**
 * Enqueuing scripts.
 */
defined('ABSPATH') || exit;

/**
 * Function wpfep_register_scripts()
 * register the plugins scripts ready for enqueing.
 */
function wpfep_register_scripts()
{

    /* make sure that jquery is enqueued */
    wp_enqueue_script('jquery');

    /* make a filter to allow turning off styles */
    $style_output = apply_filters('wpfep_frontend_styles', true);

    /* if we should output styles - enqueue them */
    if (true == $style_output) {
        wp_enqueue_style('wpfep_styles', plugins_url('/assets/css/wpfep-style.css', dirname(__FILE__)), [], WPFEP_VERSION, 'all');
    }

    /* make a filter to allow turning off tab js */
    $tab_js_output = apply_filters('wpfep_tabs_js', true);

    /* if we turn ob tab js - enqueue them */
    if (true == $tab_js_output) {
        wp_enqueue_script('wpfep_tabs_js', plugins_url('/assets/js/tabs.js', dirname(__FILE__)), 'jquery', [], true);
    }
}
add_action('wp_enqueue_scripts', 'wpfep_register_scripts');
