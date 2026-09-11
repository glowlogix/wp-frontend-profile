<?php
/**
 * @package wp-front-end-profile
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
        wp_enqueue_style('wpfep_styles', WPFEP_PLUGIN_URL . 'assets/css/wpfep-style.css', array(), WPFEP_VERSION, 'all');
    }

    /* make a filter to allow turning off tab js */
    $tab_js_output = apply_filters('wpfep_tabs_js', true);

    /* if we turn ob tab js - enqueue them */
    if (true == $tab_js_output) {
        wp_enqueue_script('wpfep_tabs_js', plugins_url('/assets/js/tabs.js', dirname(__FILE__)), array( 'jquery' ), WPFEP_VERSION, true);
    }

    /* small password show/hide toggle */
    wp_enqueue_script('wpfep_password_toggle', WPFEP_PLUGIN_URL . 'assets/js/password-toggle.js', array( 'jquery' ), WPFEP_VERSION, true);
}
add_action('wp_enqueue_scripts', 'wpfep_register_scripts');

function wpfep_apply_form_background()
{
    $options = get_option('wpfep_form_background');
    //error_log('Current Page ID: ' . $current_page_id);


    if (empty($options)) {
        return;
    }

    $type  = $options['type'] ?? '';
    $value = $options['value'] ?? '';
    $pages = $options['pages'] ?? [];

    if (!$type || !$value || empty($pages)) {
        return;
    }

    $current_page_id = get_queried_object_id();
    $login_page    = wpfep_get_option('login_page', 'wpfep_pages');
    $register_page = wpfep_get_option('register_page', 'wpfep_pages');

    $apply = false;

    $action = sanitize_text_field(wp_unslash((string) filter_input(INPUT_GET, 'action')));

    if ($current_page_id == $login_page && in_array('login', $pages)) {
        $apply = true;
    }

    if ($current_page_id == $register_page && in_array('register', $pages)) {
        $apply = true;
    }

    if ($current_page_id == $login_page) {
        if ($action === 'lostpassword' && in_array('lostpass', $pages)) {
            $apply = true;
        }

        if (($action === 'rp' || $action === 'resetpass') && in_array('resetpass', $pages)) {
            $apply = true;
        }
    }

    if (!$apply) {
        return;
    }

    // sanitize
    if ($type === 'image') {
        $value = esc_url($value);
        $css = "background-image: url('{$value}'); background-size: cover; background-position: center;";
    }

    if ($type === 'color') {
        $value = esc_attr($value);
        $css = "background: {$value};";
    }

    if ($type === 'gradient') {
        $css = $value;
    }

    if ($type === 'animation') {
        $css = $value;
    }

    $custom_css = "
        .wpfep-form-bg-wrapper {
            {$css}
            min-height: 100vh;
            padding: 60px 20px;
            position: relative;
        }

        .wpfep-form-box {
            max-width: 500px;
            margin: auto;
            background: rgba(255,255,255,0.9);
            padding: 30px;
            border-radius: 10px;
        }
    ";

    wp_add_inline_style('wpfep_styles', $custom_css);
}

/**
 * Load background images from URL settings
 */
function wpfep_load_url_background()
{
    $options = get_option('wpfep_form_background', array());

    if (empty($options['enable_form_background']) || 'off' === $options['enable_form_background']) {
        return;
    }

    $current_page_id = get_queried_object_id();

    $login_page    = wpfep_get_option('login_page', 'wpfep_pages');
    $register_page = wpfep_get_option('register_page', 'wpfep_pages');
    $profile_page  = wpfep_get_option('profile_page', 'wpfep_pages');
    $edit_page     = wpfep_get_option('profile_edit_page', 'wpfep_pages');

    $bg_image = '';
    $blur     = isset($options['blur']) ? (int) $options['blur'] : 0;
    $opacity  = isset($options['opacity']) ? floatval($options['opacity']) : 0.3;

    // Select image per page
    if ($current_page_id == $login_page && !empty($options['login_bg_image'])) {
        $bg_image = $options['login_bg_image'];
    } elseif ($current_page_id == $register_page && !empty($options['register_bg_image'])) {
        $bg_image = $options['register_bg_image'];
    } elseif (($current_page_id == $profile_page || $current_page_id == $edit_page) && !empty($options['profile_bg_image'])) {
        $bg_image = $options['profile_bg_image'];
    }

    if (empty($bg_image)) {
        return;
    }

    $bg_image = esc_url($bg_image);

    $custom_css = "
        .wpfep-wrapper,
        #wpfep-login-form,
        .wpfep-registration-form,
        .wpfep-profile-template {
            position: relative;
            min-height: 100vh;
            z-index: 1;
        }

        /* Background Layer */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: url('{$bg_image}') no-repeat center center;
            background-size: cover;
            filter: blur({$blur}px);
            transform: scale(1.05);
            z-index: -2;
        }

        /* Dark Overlay */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,{$opacity});
            z-index: -1;
        }

        /* Form container */
        .wpfep-form-bg-wrapper,
        .wpfep-wrapper {
            position: relative;
            z-index: 2;
        }
    ";

    wp_add_inline_style('wpfep_styles', $custom_css);
}
