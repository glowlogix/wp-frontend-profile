<?php

/**
 * Registration handler class
 */
class WPFEP_Registration {

    private $registration_errors = array();
    private $messages = array();

    private static $_instance;
    public $atts = array();
    public $userrole = '';

    function __construct() {

        add_shortcode( 'wpfep-register', array($this, 'registration_form') );

        add_action( 'init', array($this, 'process_registration') );
    }

    /**
     * Singleton object
     *
     * @return self
     */
    public static function init() {

        if ( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Shows the registration form
     *
     * @return string
     */
    function registration_form( $atts ) {
        global $wp;
        $atts = shortcode_atts(
        array(
                'role' => '',
            ), $atts
        );
        $userrole = $atts['role'];

        $roleencoded = wpfep_encryption( $userrole );

        ob_start();

        if ( is_user_logged_in() ) {

            wpfep_load_template( 'logged-in.php', array(
                'user' => wp_get_current_user()
            ) );

        } else {

            $action = isset( $_GET['action'] ) ? $_GET['action'] : 'register';

            $args = array(
                'action_url' =>  home_url( $wp->request ),
                'userrole'   => $roleencoded
            );
            wpfep_load_template( 'registration.php', $args );

        }

        return ob_get_clean();
    }

    /**
     * Process registration form
     *
     * @return void
     */
    function process_registration() {
        if ( !empty( $_POST['wpfep_registration'] ) && !empty( $_POST['_wpnonce'] ) ) {
            $userdata = array();

            if ( isset( $_POST['_wpnonce'] ) ) {
                wp_verify_nonce( $_POST['_wpnonce'], 'wpfep_registration_action' );
            }

            $validation_error = new WP_Error();
            $validation_error = apply_filters( 'wpfep_process_registration_errors', $validation_error, $_POST['wpfep_reg_email'],  $_POST['wpfep_reg_uname'], $_POST['pwd1'], $_POST['pwd2'] );

            if ( $validation_error->get_error_code() ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . $validation_error->get_error_message();
                return;
            }


            if ( empty( $_POST['wpfep_reg_email'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'Email is required.', 'wpptm' );
                return;
            }

            if ( empty( $_POST['wpfep_reg_uname'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'Username is required.', 'wpptm' );
                return;
            }

            if ( empty( $_POST['pwd1'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'Password is required.', 'wpptm' );
                return;
            }

            if ( empty( $_POST['pwd2'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'Confirm Password is required.', 'wpptm' );
                return;
            }

            if ( $_POST['pwd1'] != $_POST['pwd2'] ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'Passwords are not same.', 'wpptm' );
                return;
            }
             if ( isset ( $_POST["g-recaptcha-response"] ) ) {
                if ( empty( $_POST['g-recaptcha-response'] ) ) {
                    $this->registration_errors[] = __( 'reCaptcha is required', 'wpptm' );
                    return;
                } else {
                    $no_captcha = 1;
                    $invisible_captcha = 0;

                    WPFEP_Captcha_Recaptcha::captcha_verification();
                }
            }

            if ( get_user_by( 'login', $_POST['wpfep_reg_uname'] ) === $_POST['wpfep_reg_uname'] ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'A user with same username already exists.', 'wpptm' );
                return;
            }

            if ( is_email( $_POST['wpfep_reg_uname'] ) && apply_filters( 'wpfep_get_username_from_email', true ) ) {
                $user = get_user_by( 'email', $_POST['wpfep_reg_uname'] );

                if ( isset( $user->user_login ) ) {
                    $userdata['user_login']  = $user->user_login;
                } else {
                    $this->registration_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'wpptm' );
                    return;
                }
            } else {
                $userdata['user_login']      = $_POST['wpfep_reg_uname'];
            }

            $dec_role = wpfep_decryption( $_POST['urhidden'] );

            $userdata['first_name']     = isset($_POST['wpfep_reg_fname']) ? $_POST['wpfep_reg_fname'] : '';
            $userdata['last_name']      = isset($_POST['wpfep_reg_lname']) ? $_POST['wpfep_reg_lname'] : '';
            $userdata['user_email']     = $_POST['wpfep_reg_email'];
            $userdata['user_pass']      = $_POST['pwd1'];
            $userdata['description']    = isset($_POST['wpfep-description']) ? $_POST['wpfep-description'] : '';
            $userdata['user_url']       = isset($_POST['wpfep-website']) ? $_POST['wpfep-website'] : '';

            if ( get_role( $dec_role ) ) {
                $userdata['role'] = $dec_role;
            }

            $user = wp_insert_user( $userdata );
            if ( is_wp_error( $user ) ) {
                    $this->registration_errors[] = $user->get_error_message();
                    return;
            } else {

                $wpfep_user  = new WP_User( $user );
                $user_login = stripslashes( $wpfep_user->user_login );
                $user_email = stripslashes( $wpfep_user->user_email );
                $blogname   = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

                $message = sprintf(__('New user registration on your site %s:', 'wpptm'), get_option('blogname')) . "\r\n\r\n";
                $message .= sprintf(__('Username: %s', 'wpptm'), $user_login) . "\r\n\r\n";
                $message .= sprintf(__('E-mail: %s', 'wpptm'), $user_email) . "\r\n";
                $subject = "New User Registration";

                $subject = apply_filters( 'wpfep_default_reg_admin_mail_subject', $subject );
                $message = apply_filters( 'wpfep_default_reg_admin_mail_body', $message );

                wp_mail(get_option('admin_email'), sprintf(__('[%s] %s', 'wpptm'), $blogname, $subject ), $message);

                $message = sprintf(__('Hi, %s', 'wpptm'), $user_login) . "\r\n";
                $message .= "Congrats! You are Successfully registered to ". $blogname ."\r\n\r\n";
                $message .= "Thanks";
                $subject = "Thank you for registering";

                $subject = apply_filters( 'wpfep_default_reg_mail_subject', $subject );
                $message = apply_filters( 'wpfep_default_reg_mail_body', $message );

                wp_mail( $user_email, sprintf(__('[%s] %s', 'wpptm'), $blogname, $subject ), $message );

            }

            $autologin_after_registration = wpfep_get_option( 'autologin_after_registration', 'wpfep_profile', 'on' );

            if ( $autologin_after_registration == 'on' ) {
                wp_clear_auth_cookie();
                wp_set_current_user( $user );
                wp_set_auth_cookie( $user );
            }
            $redirect_after_registration = wpfep_get_option( 'redirect_after_registration', 'wpfep_profile' );
            $register_page = wpfep_get_option( 'register_page', 'wpfep_pages' );
            if ( is_wp_error( $user ) ) {
                $this->registration_errors[] = $user->get_error_message();
                return;
            } else {

                if ( $autologin_after_registration == 'on' && $redirect_after_registration == '' ) {
                    $redirect = home_url();
                } elseif ( $redirect_after_registration != '' ) {
                     $redirect = get_permalink( $redirect_after_registration );
                } else {
                     global $wp;
                    $redirect = get_permalink( $register_page ) . '?success=yes';
                }

                wp_redirect( apply_filters( 'wpfep_registration_redirect', $redirect, $user ) );
                exit;
            }
        }
    }

    /**
     * Show errors on the form
     *
     * @return void
     */
    function show_errors() {
        if ( $this->registration_errors ) {
            foreach ($this->registration_errors as $error) {
                echo '<div class="wpfep-error">';
                _e( $error,'wpptm' );
                echo '</div>';
            }
        }
    }

    /**
     * Show messages on the form
     *
     * @return void
     */
    function show_messages() {
        if ( $this->messages ) {
            foreach ($this->messages as $message) {
                printf( '<div class="wpfep-message">%s</div>', $message );
            }
        }
    }

}
