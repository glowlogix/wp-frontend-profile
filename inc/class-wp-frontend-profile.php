<?php

/**
 * Main class for WP Frontend Profile.
 */
defined('ABSPATH') || exit;

if (!class_exists('WP_Frontend_Profile')) {
    /**
     * WP_Frontend_Profile main class.
     *
     * @author Glowlogix
     */
    class WP_Frontend_Profile
    {
        /**
         * Holds various class instances.
         *
         * @var array
         */
        private $container = [];

        /**
         * The singleton instance.
         *
         * @var WP_Frontend_Profile
         */
        private static $instance;

        /**
         * Fire up the plugin.
         */
        public function __construct()
        {
            $this->includes();
            $this->init_hooks();
            do_action('wfep_loaded');
        }

        /**
         * Initialize the hooks.
         *
         * @return void
         */
        public function init_hooks()
        {
            add_action('plugins_loaded', [$this, 'instantiate']);
            add_action('init', [$this, 'load_textdomain']);
            add_filter('show_admin_bar', [$this, 'show_admin_bar']);

            add_action('admin_notices', 'wpfep_error_notices');

            /* When plugin is activated */
            register_activation_hook(__FILE__, [&$this, 'wpfep_install_time']);
        }

        /**
         * Include the required files.
         *
         * @return void
         */
        public function includes()
        {
            require_once WPFEP_PATH . '/functions/scripts.php';
            require_once WPFEP_PATH . '/functions/default-fields.php';
            require_once WPFEP_PATH . '/functions/tabs.php';
            require_once WPFEP_PATH . '/functions/wpfep-functions.php';
            require_once WPFEP_PATH . '/functions/save-fields.php';
            require_once WPFEP_PATH . '/functions/shortcode.php';
            require_once WPFEP_PATH . '/functions/feedback.php';
            require_once WPFEP_PATH . '/functions/wpfep-gutenberg-block.php';
            require_once WPFEP_PATH . '/inc/class-wpfep-user.php';
            require_once WPFEP_PATH . '/inc/class-wpfep-roles-editor.php';
            require_once WPFEP_PATH . '/inc/class-wpfep-login-widget.php';

            if (is_admin()) {
                require_once WPFEP_PATH . '/admin/class-wpfep-admin-installer.php';
                require_once WPFEP_PATH . '/admin/class-wpfep-admin-settings.php';
                require_once WPFEP_PATH . '/admin/class-wpfep-shortcodes-button.php';
                require_once WPFEP_PATH . '/admin/class-wpfep-admin-help.php';
                require_once WPFEP_PATH . '/admin/class-wpfep-system-status.php';
            } else {
                require_once WPFEP_PATH . '/inc/class-wpfep-registration.php';
                require_once WPFEP_PATH . '/inc/class-wpfep-login.php';
                require_once WPFEP_PATH . '/inc/class-wpfep-profile.php';
                require_once WPFEP_PATH . '/inc/class-wpfep-captcha-recaptcha.php';
            }
        }

        /**
         * Instantiate the classes.
         *
         * @return void
         */
        public function instantiate()
        {
            if (is_admin()) {
                $this->container['settings'] = WPFEP_Admin_Settings::init();
                $this->container['admin_installer'] = new WPFEP_Admin_Installer();
                $this->container['System_Status'] = new Wpfep_System_Status();
            } else {
                $this->container['registration'] = WPFEP_Registration::init();
                $this->container['login'] = WPFEP_Login::init();
                $this->container['profile'] = WPFEP_Profile::init();
                $this->container['captcha'] = WPFEP_Captcha_Recaptcha::initialize();
            }
        }

        /**
         * Load the translation file for current language.
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('wp-front-end-profile', false, WPFEP_PATH . '/languages/');
        }

        /**
         * Singleton Instance.
         *
         * @return \self
         */
        public static function init()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Show/hide admin bar to the permitted user level.
         *
         * @since 1.0.0
         *
         * @param bool $show Whether to allow the admin bar to show.
         *
         * @return bool Whether the admin bar should be showing.
         */
        public function show_admin_bar($show)
        {
            if (!is_user_logged_in()) {
                return false;
            }

            $roles = wpfep_get_option('show_admin_bar_to_roles', 'wpfep_general', ['administrator', 'editor', 'author', 'contributor', 'subscriber']);
            $roles = $roles ? $roles : [];
            $current_user = wp_get_current_user();

            if (isset($current_user->roles[0])) {
                if (!in_array($current_user->roles[0], $roles)) {
                    return false;
                }
            }

            return $show;
        }

        /**
         * Update plugin install time if not set.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function wpfep_install_time()
        {
            if (false === get_option('wpfep_install_time')) {
                update_option('wpfep_install_time', time());
            }
        }
    }
    WP_Frontend_Profile::init();
}
