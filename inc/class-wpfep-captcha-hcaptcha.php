<?php
/**
 * hCaptcha Class.
 */
defined('ABSPATH') || exit;

/**
 * hCaptcha class.
 */
if (!class_exists('WPFEP_Captcha_hCaptcha')) {
    class WPFEP_Captcha_hCaptcha
    {
        /**
         * hCaptcha site key.
         *
         * @var string
         */
        private static $site_key;

        /**
         * hCaptcha secret key.
         *
         * @var string
         */
        private static $secret_key;

        /**
         * hCaptcha error message.
         *
         * @var string
         */
        protected static $error_message;

        /**
         * hCaptcha script handler.
         *
         * @var string
         */
        protected static $script_handle;

        /**
         * Initialize and enqueue the scripts.
         *
         * @since 1.0.0
         */
        public static function initialize()
        {
            // self::$site_key = wpfep_get_option('hcaptcha_public', 'wpfep_general');
            self::$secret_key = wpfep_get_option('hcaptcha_private', 'wpfep_general');
            self::$script_handle = 'wpfep-hcaptcha';
            add_action('plugins_loaded', [__CLASS__, 'load_plugin_textdomain']);

            if ((wpfep_get_option('enable_hcaptcha_login', 'wpfep_general') === 'on') || (wpfep_get_option('enable_hcaptcha_registration', 'wpfep_general') === 'on')) {
                add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_header_script']);
            }
        }

        /**
         * Enqueue the hCaptcha script using the WP system.
         *
         * @since 1.0.0
         */
        public static function enqueue_header_script()
        {
            // $src = 'https://js.hcaptcha.com/1/api.js';
            // wp_enqueue_script(self::$script_handle, $src, false, WPFEP_VERSION, true);
            wp_enqueue_script('hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true);
        }

        /**
         * Display hCaptcha.
         */
        public static function display_captcha()
        {
            self::enqueue_header_script();

            $wpfep_general_options = get_option('wpfep_general');
            if ($wpfep_general_options && isset($wpfep_general_options['hcaptcha_public'])) {
                self::$site_key = $wpfep_general_options['hcaptcha_public'];
            
                echo '<div class="h-captcha" data-sitekey="' . esc_attr(self::$site_key) . '"></div>';
            } else {
                echo "Site Key Not Found";
            }
        }

        /**
         * Send a POST request to verify hCaptcha challenge.
         *
         * @return bool
         */
        public static function captcha_verification()
        {
            $response = isset($_POST['h-captcha-response']) ? sanitize_text_field(wp_unslash($_POST['h-captcha-response'])) : '';
            $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $verify = wp_remote_post('https://hcaptcha.com/siteverify', [
                'body' => [
                    'secret' => self::$secret_key,
                    'response' => $response,
                    'remoteip' => $remote_ip,
                ]
            ]);

            $verify_response = wp_remote_retrieve_body($verify);
            $result = json_decode($verify_response);

            return !empty($result->success);
        }
    }
}
