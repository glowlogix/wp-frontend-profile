<?php

defined('ABSPATH') || exit;

class WPFEP_Form_Background_Frontend {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'apply_background']);
    }

    public function apply_background() {

        $options = get_option('wpfep_form_background', []);

        if (empty($options['enable']) || $options['enable'] !== 'on') {
            return;
        }

        $current_page_id = get_queried_object_id();

        $login_page    = wpfep_get_option('login_page', 'wpfep_pages');
        $register_page = wpfep_get_option('register_page', 'wpfep_pages');
        $profile_page  = wpfep_get_option('profile_page', 'wpfep_pages');
        $edit_page     = wpfep_get_option('profile_edit_page', 'wpfep_pages');

        $bg_value = '';
        $blur     = isset($options['blur']) ? intval($options['blur']) : 0;
        $opacity  = isset($options['opacity']) ? floatval($options['opacity']) : 0.3;

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        // Select value per page
        if ($current_page_id == $login_page && in_array('login', $options['pages'] ?? [])) {
            $bg_value = $options['value'];
        } elseif ($current_page_id == $register_page && in_array('register', $options['pages'] ?? [])) {
            $bg_value = $options['value'];
        } elseif ($current_page_id == $login_page && $action === 'lostpassword' && in_array('lostpass', $options['pages'] ?? [])) {
            $bg_value = $options['value'];
        } elseif ($current_page_id == $login_page && in_array('resetpass', $options['pages'] ?? [])) {
            $bg_value = $options['value'];
        }

        if (empty($bg_value)) {
            return;
        }

        $type = $options['type'] ?? '';
        $css = '';

        if ($type === 'image') {
            $bg_image = esc_url($bg_value);
            $css .= "
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
            ";
        } elseif ($type === 'color') {
            // For colors, keep as-is (already sanitized)
            $css .= "
                body {
                    background: {$bg_value};
                }
            ";
        } elseif ($type === 'gradient') {
            // For gradients, don't escape - keep raw CSS
            $css .= "
                body {
                    background: {$bg_value};
                }
            ";
        }

        $css .= "
            body::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, {$opacity});
                z-index: -1;
            }

            .wpfep-wrapper,
            #wpfep-login-form,
            .wpfep-registration-form,
            .wpfep-profile-template {
                position: relative;
                min-height: 100vh;
                z-index: 1;
            }

            .wpfep-form-bg-wrapper,
            .wpfep-wrapper {
                position: relative;
                z-index: 2;
            }

            .wpfep-form-box {
                max-width: 500px;
                margin: auto;
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                padding: 30px;
                border-radius: 10px;
            }
        ";

        if (!empty($css)) {
            wp_add_inline_style('wpfep_styles', $css);
        }
    }
}