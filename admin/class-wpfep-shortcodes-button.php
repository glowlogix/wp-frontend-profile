<?php
/**
 * Adding shortcode through tinymice.
 */
defined('ABSPATH') || exit;

if (!class_exists('WPFEP_Admin_Help')) {
    /**
     * Wpfep tinyMce Shortcode Button class.
     *
     * @since 1.0.0
     */
    class WPFEP_Shortcodes_Button
    {
        /**
         * Constructor for shortcode class.
         */
        public function __construct()
        {
            add_filter('mce_external_plugins', [$this, 'enqueue_plugin_scripts']);
            add_filter('mce_buttons', [$this, 'register_buttons_editor']);

            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'localize_shortcodes_script']);
        }

        /**
         * Enqueue scripts for shortcode of tiny mice.
         */
        public function enqueue_scripts()
        {
            global $pagenow;
            $posttype = get_post_type(get_the_ID());
            if (('page' === $posttype && 'post.php' === $pagenow) || ('page' === $posttype && 'post-new.php' === $pagenow)) {
                wp_enqueue_script('wpfep_shortcode_handle', plugins_url('/assets/js/wpfep-tmc-button.js', dirname(__FILE__)), ['jquery'], WPFEP_VERSION, true);
            }
        }

        /**
         * Localize the script with new data.
         */
        public function localize_shortcodes_script()
        {
            $shortcodes = [
                'wpfep-register' => [
                    'title'   => __('Register', 'wp-front-end-profile'),
                    'content' => '[wpfep-register]',
                ],
                'wpfep-edit'     => [
                    'title'   => __('Edit', 'wp-front-end-profile'),
                    'content' => '[wpfep]',
                ],
                'wpfep-login'    => [
                    'title'   => __('Login', 'wp-front-end-profile'),
                    'content' => '[wpfep-login]',
                ],
                'wpfep-profile'  => [
                    'title'   => __('Profile', 'wp-front-end-profile'),
                    'content' => '[wpfep-profile]',
                ],
            ];
            $assets_url = WPFEP_PLUGIN_URL;
            wp_localize_script('wpfep_shortcode_handle', 'wpfep_shortcode', $shortcodes);
            wp_localize_script('wpfep_shortcode_handle', 'wpfep_assets_url', $assets_url);
        }

        /**
         * Singleton object.
         *
         * @staticvar boolean $instance
         *
         * @return \self
         */
        public static function init()
        {
            static $instance = false;

            if (!$instance) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Add button on Post Editor.
         *
         * @since 1.0.0
         *
         * @param array $plugin_array enqueuing files.
         *
         * @return array
         */
        public function enqueue_plugin_scripts($plugin_array)
        {
            global $pagenow;
            $posttype = get_post_type(get_the_ID());
            if (('page' === $posttype && 'post.php' === $pagenow) || ('page' === $posttype && 'post-new.php' === $pagenow)) {
                // enqueue TinyMCE plugin script with its ID.
                $plugin_array['wpfep_button'] = plugins_url('/assets/js/wpfep-tmc-button.js', dirname(__FILE__));
                return $plugin_array;
            }
        }

        /**
         * Register tinyMce button.
         *
         * @since 1.0.0
         *
         * @param array $buttons buttons id.
         *
         * @return array
         */
        public function register_buttons_editor($buttons)
        {
            // register buttons with their id.
            array_push($buttons, 'wpfep_button');

            return $buttons;
        }
    }
    WPFEP_Shortcodes_Button::init();
}
