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
        $login_redirect_pages =  array(
            'previous_page' => __( 'Previous Page', 'wpptm' )
        ) + $pages;
        $settings_fields = array(
            'wpfep_general' => apply_filters( 'wpfep_options_others', array(
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
            ) ),
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
                    'desc'    => __( 'After successfull login, where the page will redirect to', 'wpptm' ),
                    'type'    => 'select',
                    'options' => $login_redirect_pages
                ),
                array(
                    'name'    => 'wp_default_login_redirect',
                    'label'   => __( 'Default Login Redirect', 'wpptm' ),
                    'desc'    => __( 'If enabled, users who login using WordPress default login form will be redirected to the selected page.', 'wpptm' ),
                    'type'    => 'checkbox',
                    'default' => 'off'
                ),

            ) ), 
        );
        return apply_filters( 'wpfep_settings_fields', $settings_fields );
    }

    public function wpfep_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wpfep_general',
                'title' => __( 'General Options', 'wpptm' ),
                'icon' => 'dashicons-admin-generic'
            ),
            array(
                'id'    => 'wpfep_profile',
                'title' => __( 'Login / Registration', 'wpptm' ),
                'icon' => 'dashicons-admin-users'
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
        $this->menu_pages[] = add_menu_page( __( 'WP User Frontend', 'wpfep-settings' ), __( 'WP User Frontend', 'wpfep-settings' ), 'manage_options', 'wpfep-settings', array($this, 'plugin_page'), '', 55 );
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

            <h2 style="margin-bottom: 15px;"><?php _e( 'Settings', 'wpptm' ) ?></h2>
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
}
