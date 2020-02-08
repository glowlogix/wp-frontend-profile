<?php
/**
 * Login Class.
 */
defined('ABSPATH') || exit;

/**
 * Captcha Recaptcha class.
 */
if (!class_exists('WPFEP_Captcha_Recaptcha')) {
    /**
     * Captcha Recaptcha class.
     */
    class WPFEP_Captcha_Recaptcha
    {
        /**
         * Captcha Recaptcha class.
         *
         * @var string captcha site key
         */
        private static $site_key;

        /**
         * Captcha Recaptcha class.
         *
         * @var string captcha secret key
         */
        private static $secret_key;

        /**
         * Captcha Recaptcha class.
         *
         * @var string captcha error message
         */
        protected static $error_message;

        /**
         * Captcha Recaptcha class.
         *
         * @var string captcha script handler
         */
        protected static $script_handle;

        /**
         * Initialize and enqueuing the scripts.
         *
         * @since 1.0.0
         */
        public static function initialize()
        {
            self::$site_key = wpfep_get_option('recaptcha_public', 'wpfep_general');

            self::$secret_key = wpfep_get_option('recaptcha_private', 'wpfep_general');

            self::$script_handle = 'wpfep-recaptcha';

            add_action('plugins_loaded', [__CLASS__, 'load_plugin_textdomain']);

            // initialize if login is activated.
            if ((wpfep_get_option('enable_captcha_login', 'wpfep_general') === 'on') || (wpfep_get_option('enable_captcha_registration', 'wpfep_general') === 'on')) {
                add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_header_script']);
            }
        }

        /**
         * Enqueue the Google ReCAPTCHA script using the WP system.
         *
         * @since 1.0.0
         */
        public static function enqueue_header_script()
        {
            $src = 'https://www.google.com/recaptcha/api.js';

            wp_enqueue_script(self::$script_handle, $src, false, WPFEP_VERSION, true);
            wp_enqueue_script('wpfep_google_recaptcha', 'https://www.google.com/recaptcha/api.js', false, WPFEP_VERSION, false);
        }

        /** Output the reCAPTCHA form field. */
        public static function display_captcha()
        {
            if ('failed' == isset($_GET['captcha']) && sanitize_text_field(wp_unslash($_GET['captcha']))) {
                esc_attr(self::$error_message);
            }

            echo '<div class="g-recaptcha" data-sitekey="'.esc_attr(self::$site_key).'"></div>';
        }

        /**
         * Send a GET request to verify captcha challenge.
         *
         * @return bool
         */
        public static function captcha_verification()
        {
            $response = isset($_POST['g-recaptcha-response']) ? wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['g-recaptcha-response']))) : '';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $remote_ip = isset($_SERVER['HTTP_CLIENT_IP']) ? intval($_SERVER['HTTP_CLIENT_IP']) : '';
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $remote_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? intval($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
            } else {
                $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? intval($_SERVER['REMOTE_ADDR']) : '';
            }

            // make a GET request to the Google reCAPTCHA Server.
            $request = wp_remote_get(
                'https://www.google.com/recaptcha/api/siteverify?secret='.self::$secret_key.'&response='.$response.'&remoteip='.$remote_ip
            );

            // get the request response body.
            $response_body = wp_remote_retrieve_body($request);

            $result = json_decode($response_body, true);
            if (true == isset($result['success']) && $result['success']) {
                $status = true;
            } else {
                $status = false;
                $error = (isset($result['error-codes'])) ? $result['error-codes']
                    : 'invalid-input-response';
            }

            return [
                'success'     => $status,
                'error-codes' => (isset($error)) ? $error : null,
            ];
        }
    }
}
