<?php
/**
 * All setting fields of plugin.
 */
defined("ABSPATH") || exit();

if (!class_exists("FBWFP_frontend_posts_submission")) {
    /**
     * Plugin settings class.
     *
     * @since 1.0.0
     *
     * @author Glowlogix
     */
    class FBWFP_frontend_posts_submission
    {
        private $callback;
        private $config;
        /**
         * Static instance of this class.
         *
         * @var \self
         */
        private static $instance;

        /**
         * Settings API.
         *
         * @var \WPFEP_Settings_API
         */
        private $settings_api;

        /**
         * The menu page hooks.
         *
         * Used for checking if any page is under wpfep menu
         *
         * @var array
         */
        private $menu_pages = [];

        /**
         * WPFEP_Admin_Settings constructor.
         */
        public function __construct()
        {
            add_action("admin_menu", [$this, "fbwfp_admin_menu"], 20);
            add_filter("parent_file", [$this, "wbwep_plugin_select_submenu"]);
        }

        /**
         * Initialize WPFEP_Admin_Settings class.
         */
        public static function init()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Register the admin menu.
         */
        public function fbwfp_admin_menu()
        {
            add_submenu_page(
                "",
                "Form Builder",
                "Form Builder",
                "manage_options",
                "fbwfp_form_builder",
                [$this, "fbwfp_form_builder_admin_page"]
            );
            add_submenu_page(
                "wpfep-settings_dashboard",
                "Forms builder",
                "Forms builder",
                "manage_options",
                "fbwfp_all_form",
                [$this, "fbwfp_all_form_page"]
            );
        }

        public function fbwfp_form_builder_admin_page($settings_fields)
        {
            require FBWPF_PATH . "inc/admin/views/form_builder.php";
        }

        public function fbwfp_all_form_page()
        {
            // Create an instance of our package class.
            $forms_list_table = new wpfep_forms_List_Table();

            // Fetch, prepare, sort, and filter our data.
            $forms_list_table->prepare_items();

            // Include the view markup.
            include dirname(__FILE__) . "/views/forms.php";
        }

        public function wbwep_plugin_select_submenu($file)
        {
            global $plugin_page;
            if ("fbwfp_form_builder" == $plugin_page) {
                $plugin_page = "fbwfp_all_form";
            }
            return $file;
        }
    }

    new FBWFP_frontend_posts_submission();
}