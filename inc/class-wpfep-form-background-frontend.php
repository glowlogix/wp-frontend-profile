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
                    background-image: url('{$bg_image}');
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: cover;
                    background-attachment: fixed;
                    filter: blur({$blur}px);
                    transform: none;
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

        $css .= <<<CSS
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

            .wpfep-form-bg-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 50px 20px;
            }

            .wpfep-form-box {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                padding: 38px;
                background: rgba(255, 255, 255, 0.65) !important;
                border: 1px solid rgba(255, 255, 255, 0.45) !important;
                border-radius: 24px;
                box-shadow: 0 38px 100px rgba(0, 0, 0, 0.18) !important;
                backdrop-filter: blur(18px);
                overflow: hidden;
            }

            .wpfep-form-box ul {
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .wpfep-form-box input[type="text"],
            .wpfep-form-box input[type="password"],
            .wpfep-form-box input[type="email"],
            .wpfep-form-box textarea {
                width: 100%;
                display: block;
                padding: 14px 16px;
                border: 1px solid rgba(0, 0, 0, 0.12);
                border-radius: 10px;
                margin-bottom: 18px;
                font-size: 1rem;
                color: #2b2b2b;
                background: #ffffff;
                box-sizing: border-box;
            }

            .wpfep-form-box input::placeholder,
            .wpfep-form-box textarea::placeholder {
                color: #999999;
            }

            .wpfep-form-box label,
            .wpfep-form-box .wpfep-field label {
                font-weight: 600;
                color: #333333;
                margin-bottom: 8px;
                display: block;
            }

            .wpfep-form-box input[type="submit"],
            .wpfep-form-box button,
            .wpfep-form-box .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 140px;
                padding: 14px 26px;
                background: linear-gradient(135deg, var(--wpfep-primary) 0%, var(--wpfep-accent) 100%);
                color: #ffffff;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                transition: background 0.2s ease, transform 0.2s ease;
                text-decoration: none;
            }

            .wpfep-form-box input[type="submit"]:hover,
            .wpfep-form-box button:hover,
            .wpfep-form-box .button:hover {
                background: linear-gradient(135deg, var(--wpfep-primary-dark) 0%, var(--wpfep-primary) 100%);
                transform: translateY(-1px);
            }

            .wpfep-form-box a {
                color: #1f6feb;
                text-decoration: none;
            }

            .wpfep-form-box a:hover {
                text-decoration: underline;
            }
        CSS;

        if (!empty($css)) {
            wp_add_inline_style('wpfep_styles', $css);
        }
    }
}