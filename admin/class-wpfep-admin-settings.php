<?php

/**
 * All setting fields of plugin.
 */
defined('ABSPATH') || exit;

if (!class_exists('WPFEP_Admin_Settings')) {
    /**
     * Plugin settings class.
     *
     * @since 1.0.0
     *
     * @author Glowlogix
     */
    class WPFEP_Admin_Settings
    {
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
            require_once dirname(dirname(__FILE__)) . '/admin/class-wpfep-settings-api.php';
            $this->settings_api = new WPFEP_Settings_API();
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_init', [$this, 'admin_init']);
            add_action('admin_init', [$this, 'clear_settings']);
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
         * Fields on setting page.
         */
        public function wpfep_settings_fields()
        {
            $user_roles = [];
            $pages = wpfep_get_pages();
            $all_roles = get_editable_roles();
            foreach ($all_roles as $key => $value) {
                $user_roles[$key] = $value['name'];
            }
            $radio_options = ['none' => '--select--', 'auto_login' => 'Auto login after registration', 'activate_mail' => 'Send account activation email to registered users'];
            $settings_fields = [
                'wpfep_profile'             => apply_filters(
                    'wpfep_options_profile',
                    [
                        [
                            'name'    => 'user_behave',
                            'label'   => __('New user behaviour on registration ', 'wp-front-end-profile'),
                            'type'    => 'select',
                            'options' => $radio_options,
                        ],
                        [
                            'name'    => 'admin_can_register_user_manually',
                            'label'   => __('Admin can register user manually', 'wp-front-end-profile'),
                            'desc'    => __('If enabled, Admin can add users manually from frontend.', 'wp-front-end-profile'),
                            'type'    => 'checkbox',
                            'default' => 'off',
                        ],
                        [
                            'name'    => 'admin_manually_approve',
                            'label'   => __('Admin can Manually Approve User', 'wp-front-end-profile'),
                            'desc'    => __('If enabled, Admin can manually approve and deny users from backend.', 'wp-front-end-profile'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'    => 'admin_profile_edit',
                            'label'   => __('Admin can edit profile', 'wp-front-end-profile'),
                            'desc'    => __('Allow administrators to edit their own profiles on the frontend.', 'wp-front-end-profile'),
                            'type'    => 'checkbox',
                            'default' => 'off',
                        ],
                        [
                            'name'    => 'redirect_after_login_page',
                            'label'   => __('Redirect After Login', 'wp-front-end-profile'),
                            'desc'    => __('After successful login, where the page will redirect to', 'wp-front-end-profile'),
                            'type'    => 'select',
                            'options' => $pages,
                        ],
                        [
                            'name'    => 'redirect_after_registration',
                            'label'   => __('Redirect After Registration', 'wp-front-end-profile'),
                            'desc'    => __('After successful registration, where the page will redirect to, Make sure you have checked auto login after registration.', 'wp-front-end-profile'),
                            'type'    => 'select',
                            'options' => $pages,
                        ],
                    ]
                ),
                'wpfep_general'             => apply_filters(
                    'wpfep_options_others',
                    [
                        [
                            'name'     => 'show_admin_bar_to_roles',
                            'label'    => __('Show Admin Bar', 'wp-front-end-profile'),
                            'desc'     => __('Select user by roles, who can view admin bar in frontend.', 'wp-front-end-profile'),
                            'callback' => 'wpfep_settings_multiselect',
                            'options'  => $user_roles,
                            'default'  => ['administrator', 'editor', 'author', 'contributor', 'subscriber'],
                        ],
                        [
                            'name'    => 'role_editor',
                            'label'   => __('Role', 'wp-front-end-profile'),
                            'desc'    => __('Select to enable or disabled user add role feature.', 'wp-front-end-profile'),
                            'type'    => 'checkbox',
                            'default' => 'off',

                        ],
                        [
                            'name'    => 'strong_password',
                            'label'   => __('Enable Strong Password', 'wpfep'),
                            'desc'    => __('Check to enable strong password.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'  => 'recaptcha_public',
                            'label' => __('reCAPTCHA Site Key', 'wpfep'),
                        ],
                        [
                            'name'  => 'recaptcha_private',
                            'label' => __('reCAPTCHA Secret Key', 'wpfep'),
                            'desc'  => __('<a target="_blank" href="https://www.google.com/recaptcha/">Register here</a> to get reCaptcha Site and Secret keys.', 'wpfep'),
                        ],
                        [
                            'name'    => 'enable_captcha_login',
                            'label'   => __('reCAPTCHA Login Form', 'wpfep'),
                            'desc'    => __('Check to enable reCAPTCHA in login form.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'off',
                        ],
                        [
                            'name'    => 'enable_captcha_registration',
                            'label'   => __('reCAPTCHA Registration Form', 'wpfep'),
                            'desc'    => __('Check to enable reCAPTCHA in registration form', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'off',
                        ],
                        [
                            'name'    => 'wpfep_remove_data_on_uninstall',
                            'label'   => __('Remove Data on Uninstall?', 'wpfep'),
                            'desc'    => __('Check this box if you would like WP Frontend Profile to completely remove all of its data when the plugin is deleted.', 'wpfep'),
                            'type'    => 'checkbox',
                            'options' => 'off',
                        ],
                    ]
                ),
                'wpfep_pages'               => apply_filters(
                    'wpfep_options_pages',
                    [
                        [
                            'name'    => 'register_page',
                            'label'   => __('Registration Page', 'wpfep'),
                            'desc'    => __('Select the page which contains [wpfep-register] shortcode', 'wpfep'),
                            'type'    => 'select_page',
                            'options' => $pages,
                        ],
                        [
                            'name'    => 'login_page',
                            'label'   => __('Login Page', 'wpfep'),
                            'desc'    => __('Select the page which contains [wpfep-login] shortcode', 'wpfep'),
                            'type'    => 'select_page',
                            'options' => $pages,
                        ],
                        [
                            'name'    => 'profile_edit_page',
                            'label'   => __('Profile Edit Page', 'wpfep'),
                            'desc'    => __('Select the page which contains [wpfep] shortcode', 'wpfep'),
                            'type'    => 'select_page',
                            'options' => $pages,
                        ],
                        [
                            'name'    => 'profile_page',
                            'label'   => __('Profile Page', 'wpfep'),
                            'desc'    => __('Select the page which contains [wpfep-profile] shortcode', 'wpfep'),
                            'type'    => 'select_page',
                            'options' => $pages,
                        ],
                    ]
                ),
                'wpfep_emails_notification' => apply_filters(
                    'wpfep_options_emails_notification',
                    [
                        [
                            'name'    => 'register_mail',
                            'label'   => __('Registration success email', 'wpfep'),
                            'desc'    => __(' Send an email to user for successful registration.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'    => 'password_change_mail',
                            'label'   => __('Change password email', 'wpfep'),
                            'desc'    => __(' Send an email to user for change password.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'    => 'reset_password_mail',
                            'label'   => __('Reset password email', 'wpfep'),
                            'desc'    => __('Send an email to user for reset password.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'    => 'new_account_admin_mail',
                            'label'   => __('New account registration admin mail', 'wpfep'),
                            'desc'    => __('Send an email to admin when user has created account on site.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                        [
                            'name'    => 'change_password_admin_mail',
                            'label'   => __('Change user password admin mail', 'wpfep'),
                            'desc'    => __('Send an email to admin when user has changed account password.', 'wpfep'),
                            'type'    => 'checkbox',
                            'default' => 'on',
                        ],
                    ]
                ),
            ];

            return apply_filters('wpfep_settings_fields', $settings_fields);
        }

        /**
         * Tabs on setting page.
         */
        public function wpfep_settings_sections()
        {
            $sections = [
                [
                    'id'    => 'wpfep_profile',
                    'title' => __('Login / Registration', 'wpfep'),
                    'icon'  => 'dashicons-admin-users',
                ],
                [
                    'id'    => 'wpfep_pages',
                    'title' => __('Pages', 'wpfep'),
                    'icon'  => 'dashicons-admin-page',
                ],
                [
                    'id'    => 'wpfep_emails_notification',
                    'title' => __('Emails', 'wpfep'),
                    'icon'  => 'dashicons-email',
                ],
                [
                    'id'    => 'wpfep_general',
                    'title' => __('Settings', 'wpfep'),
                    'icon'  => 'dashicons-admin-generic',
                ],
            ];

            return apply_filters('wpfep_settings_sections', $sections);
        }

        /**
         * Initialize settings.
         */
        public function admin_init()
        {
            // Set the settings.
            $this->settings_api->set_sections($this->get_settings_sections());
            $this->settings_api->set_fields($this->get_settings_fields());
            $this->settings_api->admin_init();
        }

        /**
         * Register the admin menu.
         */
        public function admin_menu()
        {
            global $_registered_pages;
            // Translation issue: Hook name change due to translate menu title.
            $this->menu_pages[] = add_menu_page(__('Frontend Profile', 'wpfep'), __('Frontend Profile', 'wpfep'), 'manage_options', 'wpfep-settings_dashboard', [$this, 'plugin_page'], 'dashicons-admin-users', 55);
            $this->menu_pages[] = add_submenu_page('wpfep-settings_dashboard', __('Settings', 'wpfep'), __('Settings', 'wpfep'), 'manage_options', 'wpfep-settings', [$this, 'plugin_page']);
            $this->menu_pages[] = add_submenu_page('wpfep-settings_dashboard', __('Tools', 'wpfep'), __('Tools', 'wpfep'), 'manage_options', 'wpfep-tools', [$this, 'tool_page']);
            $this->menu_pages[] = add_submenu_page('wpfep-settings_dashboard', __('System Status', 'wpfep'), __('System Status', 'wpfep'), 'manage_options', 'wpfep-status', [$this, 'system_status']);
            remove_submenu_page('wpfep-settings_dashboard', 'wpfep-settings_dashboard');
        }

        /**
         * Settings sections.
         *
         * @return array
         */
        public function get_settings_sections()
        {
            return $this->wpfep_settings_sections();
        }

        /**
         * Returns all the settings fields.
         *
         * @return array settings fields
         */
        public function get_settings_fields()
        {
            return $this->wpfep_settings_fields();
        }

        /**
         * Display all setting fields on setting page.
         */
        public function plugin_page()
        {
            ?>
            <div class="wrap">
                <h2><?php esc_html_e('Settings', 'wp-front-end-profile'); ?></h2>
                <div class="wpfep-settings-wrap">
                    <div class="metabox-holder">
                        <form method="post" action="options.php">
                            <?php
                            settings_errors();
            $this->settings_api->show_navigation();
            $this->settings_api->show_forms(); ?>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Display all tools on tool page.
         */
        public function tool_page()
        {
            $confirmation_message = __('Are you Sure?', 'wp-front-end-profile');

            if (wp_verify_nonce(isset($_GET['wpfep_delete_settings']) && 1 === $_GET['wpfep_delete_settings'])) {
                ?>
                <div class="updated updated_wpfep">
                    <p>
                        <?php esc_html_e('Settings has been cleared!', 'wp-front-end-profile'); ?>
                    </p>
                </div>

            <?php
            } ?>

            <div class="wrap">
                <h2>Tools</h2>
                <div class="metabox-holder">
                    <div class="postbox">
                        <h3><?php esc_html_e('Page Installation', 'wp-front-end-profile'); ?></h3>

                        <div class="inside">
                            <p><?php esc_html_e('Clicking this button will create required pages for the plugin. Note: It\'ll not delete/replace existing pages.', 'wp-front-end-profile'); ?></p>
                            <a class="button button-primary" href="<?php echo esc_url(add_query_arg(['install_wpfep_pages' => true])); ?>"><?php esc_html_e('Create Pages', 'wp-front-end-profile'); ?></a>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3><?php esc_html_e('Reset Settings', 'wp-front-end-profile'); ?></h3>

                        <div class="inside">
                            <p>
                                <strong><?php esc_html_e('Caution:', 'wp-front-end-profile'); ?></strong>
                                <?php esc_html_e('This tool will delete all the plugin settings of WP Frontend Profile', 'wp-front-end-profile'); ?>
                            </p>
                            <a class="button button-primary" href="<?php echo esc_url(add_query_arg(['wpfep_delete_settings' => 1])); ?>" onclick="return confirm('Are you sure?');"><?php esc_html_e('Reset Settings', 'wp-front-end-profile'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
<?php
        }

        /**
         * Display system report on system status page.
         */
        public function system_status()
        {
            $wpfep_system_status = new Wpfep_System_Status();
            $wpfep_system_status->status_report();
        }

        /**
         * Clear all plugin settings.
         */
        public function clear_settings()
        {
            if (isset($_GET['wpfep_delete_settings']) && '1' === $_GET['wpfep_delete_settings']) {
                // Delete Pages.
                $wpfep_options = get_option('wpfep_profile');
                wp_delete_post($wpfep_options['login_page'], false);
                wp_delete_post($wpfep_options['register_page'], false);
                wp_delete_post($wpfep_options['edit_page'], false);
                wp_delete_post($wpfep_options['profile_page'], false);
                // Delete Options.
                delete_option('_wpfep_page_created');
                delete_option('wpfep_general');
                delete_option('wpfep_profile');
                delete_option('wpfep_pages');
                delete_option('wpfep_uninstall');
            }
        }
    }
}
