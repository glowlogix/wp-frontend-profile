<?php

/**
 * @package wp-front-end-profile
 * Main class for WP Frontend Profile.
 */

defined('ABSPATH') || exit;

if (! class_exists('WP_Frontend_Profile')) {
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
        private $container = array();

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
            add_action('plugins_loaded', array( $this, 'instantiate' ));
            add_action('init', array( $this, 'load_textdomain' ));
            add_filter('show_admin_bar', array( $this, 'show_admin_bar' ));

            add_action('admin_notices', 'wpfep_error_notices');

            /* When plugin is activated */
            register_activation_hook(__FILE__, array( &$this, 'wpfep_install_time' ));
        }

        /**
         * Include the required files.
         *
         * @return void
         */
        public function includes()
        {
            $base_dir       = __DIR__;
            $functions_dir  = $base_dir . '/../functions/';
            $admin_dir      = $base_dir . '/../admin/';

            $scripts_file            = $functions_dir . 'scripts.php';
            $default_fields_file     = $functions_dir . 'default-fields.php';
            $tabs_file               = $functions_dir . 'tabs.php';
            $wpfep_functions_file    = $functions_dir . 'wpfep-functions.php';
            $save_fields_file        = $functions_dir . 'save-fields.php';
            $shortcode_file          = $functions_dir . 'shortcode.php';
            $feedback_file           = $functions_dir . 'feedback.php';
            $gutenberg_block_file    = $functions_dir . 'wpfep-gutenberg-block.php';
            $user_file               = $base_dir . '/class-wpfep-user.php';
            $roles_editor_file       = $base_dir . '/class-wpfep-roles-editor.php';
            $login_widget_file       = $base_dir . '/class-wpfep-login-widget.php';

            require_once $scripts_file;
            require_once $default_fields_file;
            require_once $tabs_file;
            require_once $wpfep_functions_file;
            require_once $save_fields_file;
            require_once $shortcode_file;
            require_once $feedback_file;
            require_once $gutenberg_block_file;
            require_once $user_file;
            require_once $roles_editor_file;
            require_once $login_widget_file;

            if (is_admin()) {
                $admin_installer_file = $admin_dir . 'class-wpfep-admin-installer.php';
                $admin_settings_file  = $admin_dir . 'class-wpfep-admin-settings.php';
                $shortcodes_file      = $admin_dir . 'class-wpfep-shortcodes-button.php';
                $admin_help_file      = $admin_dir . 'class-wpfep-admin-help.php';
                $system_status_file   = $admin_dir . 'class-wpfep-system-status.php';
                $form_appearance_file = $admin_dir . 'class-wpfep-form-appearance.php';

                require_once $admin_installer_file;
                require_once $admin_settings_file;
                require_once $shortcodes_file;
                require_once $admin_help_file;
                require_once $system_status_file;
                require_once $form_appearance_file;
            } else {
                $registration_file      = $base_dir . '/class-wpfep-registration.php';
                $login_file             = $base_dir . '/class-wpfep-login.php';
                $profile_file           = $base_dir . '/class-wpfep-profile.php';
                $captcha_recaptcha_file = $base_dir . '/class-wpfep-captcha-recaptcha.php';
                $captcha_hcaptcha_file  = $base_dir . '/class-wpfep-captcha-hcaptcha.php';
                $form_background_file   = $base_dir . '/class-wpfep-form-background-frontend.php';

                require_once $registration_file;
                require_once $login_file;
                require_once $profile_file;
                require_once $captcha_recaptcha_file;
                require_once $captcha_hcaptcha_file;
                require_once $form_background_file;
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
                $this->container['settings']        = WPFEP_Admin_Settings::init();
                $this->container['admin_installer'] = new WPFEP_Admin_Installer();
                $this->container['System_Status']   = new Wpfep_System_Status();
                $this->container['form_appearance'] = new WPFEP_Form_Appearance();
            } else {
                $this->container['registration'] = WPFEP_Registration::init();
                $this->container['login']        = WPFEP_Login::init();
                $this->container['profile']      = WPFEP_Profile::init();
                $this->container['captcha']      = WPFEP_Captcha_Recaptcha::initialize();
                $this->container['form_bg_frontend'] = new WPFEP_Form_Background_Frontend();
            }
        }

        /**
         * Load the translation file for current language.
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('wpfep', false, plugin_basename(dirname(__DIR__)).'/languages');
        }

        /**
         * Singleton Instance.
         *
         * @return \self
         */
        public static function init()
        {
            if (! self::$instance) {
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
            if (! is_user_logged_in()) {
                return false;
            }

            $roles        = wpfep_get_option('show_admin_bar_to_roles', 'wpfep_general', array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ));
            $roles        = $roles ? $roles : array();
            $current_user = wp_get_current_user();

            if (isset($current_user->roles[0])) {
                if (! in_array($current_user->roles[0], $roles)) {
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
