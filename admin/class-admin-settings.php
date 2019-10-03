<?php

/**
 * Plugin settings class
 *
 * @author Glowlogix
 */
class WPFEP_Admin_Settings {

    /**
     * Static instance of this class
     *
     * @var \self
     */
    private static $_instance;

    /**
     * Settings API
     */
    private $settings_api;

    /**
     * The menu page hooks
     *
     * Used for checking if any page is under wpfep menu
     *
     * @var array
     */
    private $menu_pages = array();

    public function __construct() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/class.settings-api.php';
        $this->settings_api = new WPFEP_Settings_API();
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_init', array($this, 'clear_settings') );
       

    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function wpfep_settings_fields() {
        $user_roles = array();
        $pages = wpfep_get_pages();
        $all_roles = get_editable_roles();
        foreach( $all_roles as $key=>$value ) {
            $user_roles[$key] = $value['name'];
        }
        $settings_fields = array(
            'wpfep_profile' => apply_filters( 'wpfep_options_profile', array(
                array(
                    'name'    => 'autologin_after_registration',
                    'label'   => __( 'Auto Login After Registration', 'wpptm' ),
                    'desc'    => __( 'If enabled, users after registration will be logged in to the system', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name'    => 'redirect_after_login_page',
                    'label'   => __( 'Redirect After Login', 'wpptm' ),
                    'desc'    => __( 'After successful login, where the page will redirect to', 'wpptm' ),
                    'type'    => 'select',
                    'options' => $pages
                ),
                 array(
                    'name'    => 'redirect_after_registration',
                    'label'   => __( 'Redirect After Registration', 'wpptm' ),
                    'desc'    => __( 'After successful registration, where the page will redirect to, Make sure you have checked auto login after registration.', 'wpptm' ),
                    'type'    => 'select',
                    'options' => $pages
                ),
            ) ),
            'wpfep_general' => apply_filters( 'wpfep_options_others', array(
                 array(
                    'name'    => 'show_admin_bar_to_roles',
                    'label'   => __( 'Show Admin Bar', 'wpptm' ),
                    'desc'    => __( 'Select user by roles, who can view admin bar in frontend.', 'wpptm' ),
                    'callback'=> 'wpfep_settings_multiselect',
                    'options' => $user_roles,
                    'default' => array( 'administrator', 'editor', 'author', 'contributor' ),
                ),
                array(
                    'name'  => 'recaptcha_public',
                    'label' => __( 'reCAPTCHA Site Key', 'wpptm' ),
                ),
                array(
                    'name'  => 'recaptcha_private',
                    'label' => __( 'reCAPTCHA Secret Key', 'wpptm' ),
                    'desc'  => __( '<a target="_blank" href="https://www.google.com/recaptcha/">Register here</a> to get reCaptcha Site and Secret keys.', 'wpptm' ),
                ),
                array(
                    'name'    => 'enable_captcha_login',
                    'label'   => __( 'reCAPTCHA Login Form', 'wpptm' ),
                    'desc'    => __( 'Check to enable reCAPTCHA in login form.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'off'
                ),
                array(
                    'name'    => 'enable_captcha_registration',
                    'label'   => __( 'reCAPTCHA Registration Form', 'wpptm' ),
                    'desc'    => __( 'Check to enable reCAPTCHA in registration form', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'off'
                ),
                 array(
                    'name'    => 'wpfep_remove_data_on_uninstall',
                    'label'   => __( 'Remove Data on Uninstall?', 'wpptm' ),
                    'desc'    => __( 'Check this box if you would like WP Frontend Profile to completely remove all of its data when the plugin is deleted.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'options' => 'off'
                ),
            ) ), 
            'wpfep_pages' => apply_filters( 'wpfep_options_pages', array(
                array(
                    'name'    => 'register_page',
                    'label'   => __( 'Registration Page', 'wpptm' ),
                    'desc'    => __( 'Select the page which contains [wpfep-register] shortcode', 'wpptm' ),
                    'type'    => 'select_page',
                    'options' => $pages
                ),
                array(
                    'name'    => 'login_page',
                    'label'   => __( 'Login Page', 'wpptm' ),
                    'desc'    => __( 'Select the page which contains [wpfep-login] shortcode', 'wpptm' ),
                    'type'    => 'select_page',
                    'options' => $pages
                ),
               array(
                    'name'    => 'profile_edit_page',
                    'label'   => __( 'Profile Edit Page', 'wpptm' ),
                    'desc'    => __( 'Select the page which contains [wpfep] shortcode', 'wpptm' ),
                    'type'    => 'select_page',
                    'options' => $pages
                ),
               array(
                    'name'    => 'profile_page',
                    'label'   => __( 'Profile Page', 'wpptm' ),
                    'desc'    => __( 'Select the page which contains [wpfep-profile] shortcode', 'wpptm' ),
                    'type'    => 'select_page',
                    'options' => $pages
                ),
            ) ),
            'wpfep_emails_notification' => apply_filters( 'wpfep_options_emails_notification', array(
                array(
                    'name'    => 'register_mail',
                    'label'   => __( 'Registration success email', 'wpptm' ),
                    'desc'    => __( ' Send an email to user for when successfull registration.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name'    => 'password_change_mail',
                    'label'   => __( 'Change password email', 'wpptm' ),
                    'desc'    => __( ' Send an email to user for change password.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name'    => 'reset_password_mail',
                    'label'   => __( 'Reset password email', 'wpptm' ),
                    'desc'    => __( 'Send an email to user for reset password.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name'    => 'new_account_admin_mail',
                    'label'   => __( 'New account registration admin mail', 'wpptm' ),
                    'desc'    => __( 'Send an email to admin when user has created account on site.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
                array(
                    'name'    => 'change_password_admin_mail',
                    'label'   => __( 'Change user password admin mail', 'wpptm' ),
                    'desc'    => __( 'Send an email to admin when user has changed account password.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'on'
                ),
            ) ),
        );
        return apply_filters( 'wpfep_settings_fields', $settings_fields );
    }

    public function wpfep_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wpfep_profile',
                'title' => __( 'Login / Registration', 'wpptm' ),
                'icon' => 'dashicons-admin-users'
            ),
            array(
                'id'    => 'wpfep_pages',
                'title' => __( 'Pages', 'wpptm' ),
                'icon' => 'dashicons-admin-page'
            ),
            array(
                'id'    => 'wpfep_emails_notification',
                'title' => __( 'Emails', 'wpptm' ),
                'icon' => 'dashicons-email'
            ),
            array(
                'id'    => 'wpfep_general',
                'title' => __( 'Settings', 'wpptm' ),
                'icon' => 'dashicons-admin-generic'
            ),
        );
        return apply_filters( 'wpfep_settings_sections', $sections );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Register the admin menu
     */
    function admin_menu() {
        global $_registered_pages;
        // Translation issue: Hook name change due to translate menu title
        $this->menu_pages[] = add_menu_page( __( 'Frontend Profile', 'wpptm' ), __( 'Frontend Profile', 'wpptm' ), 'manage_options', 'wpfep-settings_dashboard', array($this, 'plugin_page'),  'dashicons-admin-users', 55 );
        $this->menu_pages[] = add_submenu_page( 'wpfep-settings_dashboard', __( 'Settings', 'wpptm' ), __( 'Settings', 'wpptm' ), 'manage_options', 'wpfep-settings', array($this, 'plugin_page') );
         $this->menu_pages[] = add_submenu_page( 'wpfep-settings_dashboard', __( 'Tools', 'wpptm' ), __( 'Tools', 'wpptm' ), 'manage_options', 'wpfep-tools', array($this, 'tool_page') );
         $this->menu_pages[] = add_submenu_page( 'wpfep-settings_dashboard', __( 'System Status', 'wpptm' ), __( 'System Status', 'wpptm' ), 'manage_options', 'wpfep-status', array($this, 'system_status') );
         remove_submenu_page( 'wpfep-settings_dashboard', 'wpfep-settings_dashboard' );
    }

   
    /**
     * wpfep Settings sections
     * @return array
     */
    function get_settings_sections() {
        return $this->wpfep_settings_sections();
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
    */
    function get_settings_fields() {
        return $this->wpfep_settings_fields();
    }

    function plugin_page() {
        ?>
        <div class="wrap">

            <h2><?php _e( 'Settings', 'wpptm' ) ?></h2>
            <div class="wpfep-settings-wrap">
                 <div class="metabox-holder">
                    <form method="post" action="options.php">
                        <?php
                        settings_errors();
                        $this->settings_api->show_navigation();
                        $this->settings_api->show_forms();
                        ?>
                    </form>
        </div>
            </div>
        </div>
        <?php
    }

    function tool_page() {
        $confirmation_message  = __( 'Are you Sure?', 'wpptm' );

        if (isset($_GET['wpfep_delete_settings']) && $_GET['wpfep_delete_settings'] == 1 ) {
            ?>
            <div class="updated updated_wpfep">
                <p>
                    <?php echo __( 'Settings has been cleared!', 'wpptm' ); ?>
                </p>
            </div>

        <?php } ?>


        <div class="metabox-holder">
            <h2>Frontend Profile Tool Page</h2>
            <div class="postbox">
                <h3><?php _e( 'Page Installation', 'wpptm' ); ?></h3>

                <div class="inside">
                    <p><?php _e( 'Clicking this button will create required pages for the plugin. Note: It\'ll not delete/replace existing pages.', 'wpptm' ); ?></p>
                    <a class="button button-primary" href="<?php echo add_query_arg( array( 'install_wpfep_pages' => true ) ); ?>"><?php _e( 'Create Pages', 'wpptm' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php _e( 'Reset Settings', 'wpptm' ); ?></h3>

                <div class="inside">
                    <p><?php _e( '<strong>Caution:</strong> This tool will delete all the plugin settings of WP Frontend Profile', 'wpptm' ); ?></p>
                    <a class="button button-primary" href="<?php echo add_query_arg( array( 'wpfep_delete_settings' => true ) ); ?>" onclick="return confirm('Are you sure?');"><?php _e( 'Reset Settings', 'wpptm' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    function system_status(){
       $Wpfep_System_Status = new Wpfep_System_Status();
       $Wpfep_System_Status->status_report();
    }
    function clear_settings() {
        if (isset($_GET['wpfep_delete_settings']) && $_GET['wpfep_delete_settings'] == 1 ) {
            // Delete Options
            delete_option( '_wpfep_page_created' );
            delete_option( 'wpfep_general' );
            delete_option( 'wpfep_profile' );
            delete_option( 'wpfep_pages' );
            delete_option( 'wpfep_uninstall' );
        }
    }
}
