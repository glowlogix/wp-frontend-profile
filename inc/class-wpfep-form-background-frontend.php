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
        $theme = $this->get_button_theme($type, $bg_value);
        $css = "
            :root {
                --wpfep-primary: {$theme['primary']};
                --wpfep-primary-dark: {$theme['primary_dark']};
                --wpfep-accent: {$theme['accent']};
            }
        ";

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
                color: var(--wpfep-primary);
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

    /**
     * Choose form button colors from the selected background value.
     *
     * @param string $type Background type.
     * @param string $value Background value.
     *
     * @return array
     */
    private function get_button_theme($type, $value) {
        $default = [
            'primary'      => '#0f766e',
            'primary_dark' => '#115e59',
            'accent'       => '#14b8a6',
        ];

        if ('image' === $type) {
            $image_themes = [
                '1501785888041-af3ef285b470' => [
                    'primary'      => '#166534',
                    'primary_dark' => '#14532d',
                    'accent'       => '#22c55e',
                ],
                '1467269204594-9661b134dd2b' => [
                    'primary'      => '#1d4ed8',
                    'primary_dark' => '#1e40af',
                    'accent'       => '#38bdf8',
                ],
                '1503264116251-35a269479413' => [
                    'primary'      => '#4338ca',
                    'primary_dark' => '#3730a3',
                    'accent'       => '#818cf8',
                ],
            ];

            foreach ($image_themes as $needle => $theme) {
                if (false !== strpos($value, $needle)) {
                    return $theme;
                }
            }

            return $default;
        }

        $colors = $this->extract_hex_colors($value);

        if (! empty($colors)) {
            $primary = $colors[0];
            $accent = isset($colors[1]) ? $colors[1] : $this->shift_hex_color($primary, 34);

            return [
                'primary'      => $primary,
                'primary_dark' => $this->shift_hex_color($primary, -28),
                'accent'       => $accent,
            ];
        }

        return $default;
    }

    /**
     * Extract hex colors from a CSS color or gradient value.
     *
     * @param string $value CSS value.
     *
     * @return array
     */
    private function extract_hex_colors($value) {
        preg_match_all('/#(?:[0-9a-fA-F]{3}){1,2}\b/', $value, $matches);

        if (empty($matches[0])) {
            return [];
        }

        return array_map([$this, 'normalize_hex_color'], $matches[0]);
    }

    /**
     * Convert short hex colors to full six-character hex values.
     *
     * @param string $color Hex color.
     *
     * @return string
     */
    private function normalize_hex_color($color) {
        $color = ltrim($color, '#');

        if (3 === strlen($color)) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        return '#' . strtolower($color);
    }

    /**
     * Lighten or darken a hex color.
     *
     * @param string $color Hex color.
     * @param int    $amount Amount to shift each RGB channel.
     *
     * @return string
     */
    private function shift_hex_color($color, $amount) {
        $color = ltrim($this->normalize_hex_color($color), '#');

        $red = max(0, min(255, hexdec(substr($color, 0, 2)) + $amount));
        $green = max(0, min(255, hexdec(substr($color, 2, 2)) + $amount));
        $blue = max(0, min(255, hexdec(substr($color, 4, 2)) + $amount));

        return sprintf('#%02x%02x%02x', $red, $green, $blue);
    }
}
