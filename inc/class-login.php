<?php

/**
 * Login and forgot password handler class
 */
class wpfep_Login {

    private $login_errors = array();
    private $messages = array();

    private static $_instance;

    function __construct() {
        add_shortcode( 'wpfep-login', array($this, 'login_form') );

        add_action( 'init', array($this, 'process_login') );
        add_action( 'init', array($this, 'process_logout') );
        add_action( 'init', array($this, 'process_reset_password') );

        add_action( 'init', array($this, 'wp_login_page_redirect') );
        add_action( 'init', array($this, 'activation_user_registration') );
        add_action( 'login_form', array($this, 'add_custom_fields') );

        // URL filters
        add_filter( 'login_url', array($this, 'filter_login_url'), 10, 2 );
        add_filter( 'logout_url', array($this, 'filter_logout_url'), 10, 2 );
        add_filter( 'lostpassword_url', array($this, 'filter_lostpassword_url'), 10, 2 );

        add_filter( 'login_redirect', array( $this, 'default_login_redirect' ) );

        add_filter( 'authenticate', array($this, 'successfully_authenticate'), 30, 3 );
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
     * Is override enabled
     *
     * @return boolean
     */
    function is_override_enabled() {
        $override = wpfep_get_option( 'register_link_override', 'wpfep_profile', 'off' );

        if ( $override !== 'on' ) {
            return false;
        }

        return true;
    }

    /**
     * Add custom fields to WordPress default login form
     *
     * @since 1.0.0
     */
    public function add_custom_fields() {
        $recaptcha = wpfep_get_option( 'enable_captcha_login', 'wpfep_profile');

        if ( $recaptcha == 'on' ) {
             WPFEP_Captcha_Recaptcha::display_captcha();
        }
    }

    /**
     * Get action url based on action type
     *
     * @param string $action
     * @param string $redirect_to url to redirect to
     * @return string
     */
    function get_action_url( $action = 'login', $redirect_to = '' ) {
        $root_url = $this->get_login_url();

        switch ($action) {
            case 'resetpass':
                return add_query_arg( array('action' => 'resetpass'), $root_url );
                break;

            case 'lostpassword':
                return add_query_arg( array('action' => 'lostpassword'), $root_url );
                break;

            case 'logout':
                return wp_nonce_url( add_query_arg( array('action' => 'logout'), $root_url ), 'log-out' );
                break;

            default:
                if ( empty( $redirect_to ) ) {
                    return $root_url;
                }

                return add_query_arg( array('redirect_to' => urlencode( $redirect_to )), $root_url );
                break;
        }
    }

    /**
     * Get login page url
     *
     * @return boolean|string
     */
    function get_login_url() {
        $page_id = wpfep_get_option( 'login_page', 'wpfep_profile', false );

        if ( !$page_id ) {
            return false;
        }

        $url = get_permalink( $page_id );

        return apply_filters( 'wpfep_login_url', $url, $page_id );
    }


    /**
     * Filter the login url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_login_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'login', $redirect );
    }


    /**
     * Filter the logout url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_logout_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'logout', $redirect );
    }


    /**
     * Filter the lost password url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_lostpassword_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'lostpassword', $redirect );
    }


    /**
     * Get actions links for displaying in forms
     *
     * @param array $args
     * @return string
     */
    function lost_password_links() {

        $links = sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'lostpassword' ), __( 'Lost Password', 'wpptm' ) );

        return $links ;
    }

    /**
     * Shows the login form
     *
     * @return string
     */
    function login_form() {
        global $wp;
        
        $login_page = home_url( $wp->request );

        ob_start();

        if ( is_user_logged_in() ) {

            wpfep_load_template( 'logged-in.php', array(
                'user' => wp_get_current_user()
            ) );

        } else {

            $action = isset( $_GET['action'] ) ? $_GET['action'] : 'login';

            $args = array(
                'action_url' => $login_page,
            );

            switch ($action) {
                case 'lostpassword':

                    $this->messages[] = __( 'Please enter your username or email address. You will receive a link to create a new password via email.', 'wpptm' );

                    wpfep_load_template( 'lost-pass.php', $args );
                    break;

                case 'rp':
                case 'resetpass':

                    if ( isset( $_GET['reset'] ) && $_GET['reset'] == 'true' ) {

                        printf( '<div class="wpfep-message">' . __( 'Your password has been reset.', 'wpptm' ) . '</div>' );
                        return;
                    } else {

                        $this->messages[] = __( 'Enter your new password below..', 'wpptm' );

                        wpfep_load_template( 'reset-pass.php', $args );
                    }

                    break;

                default:

                    if ( isset( $_GET['checkemail'] ) && $_GET['checkemail'] == 'confirm' ) {
                        $this->messages[] = __( 'Check your e-mail for the confirmation link.', 'wpptm' );
                    }

                    if ( isset( $_GET['loggedout'] ) && $_GET['loggedout'] == 'true' ) {
                        $this->messages[] = __( 'You are now logged out.', 'wpptm' );
                    }

                    wpfep_load_template( 'login.php', $args );

                    break;
            }
        }

        return ob_get_clean();
    }

    /**
     * Process login form
     *
     * @return void
     */
    function process_login() {
        if ( !empty( $_POST['wpfep_login'] ) && !empty( $_POST['_wpnonce'] ) ) {
            $creds = array();

            if ( isset( $_POST['_wpnonce'] ) ) {
                wp_verify_nonce( $_POST['_wpnonce'], 'wpfep_login_action' );
            }

            $validation_error = new WP_Error();
            $validation_error = apply_filters( 'wpfep_process_login_errors', $validation_error, $_POST['log'], $_POST['pwd'] );

            if ( $validation_error->get_error_code() ) {
                $this->login_errors[] = $validation_error->get_error_message();
                return;
            }

            if ( empty( $_POST['log'] ) ) {
                $this->login_errors[] = __( 'Username is required.', 'wpptm' );
                return;
            }

            if ( empty( $_POST['pwd'] ) ) {
                $this->login_errors[] = __( 'Password is required.', 'wpptm' );
                return;
            }

            if ( isset ( $_POST["g-recaptcha-response"] ) ) {
                if ( empty( $_POST['g-recaptcha-response'] ) ) {
                    $this->login_errors[] = __( 'Empty reCaptcha Field', 'wpptm' );
                    return;
                } else {
                    $no_captcha = 1;
                    $invisible_captcha = 0;

                    WPFEP_Captcha_Recaptcha::captcha_verification();
                }
            }

            if ( is_email( $_POST['log'] ) && apply_filters( 'wpfep_get_username_from_email', true ) ) {
                $user = get_user_by( 'email', $_POST['log'] );

                if ( isset( $user->user_login ) ) {
                    $creds['user_login'] = $user->user_login;
                } else {
                    $this->login_errors[] = '<strong>' . __( 'Error', 'wpptm' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'wpptm' );
                    return;
                }
            } else {
                $creds['user_login'] = $_POST['log'];
            }

            $creds['user_password'] = $_POST['pwd'];
            $creds['remember'] = isset( $_POST['rememberme'] );

            if ( isset( $user->user_login ) ) {
                $validate = wp_authenticate_email_password( null, trim( $_POST['log'] ), $creds['user_password'] );
                if ( is_wp_error( $validate ) ) {
                    $this->login_errors[] = $validate->get_error_message();
                    return;
                }
            }

            $secure_cookie = is_ssl() ? true : false;
            $user = wp_signon( apply_filters( 'wpfep_login_credentials', $creds ), $secure_cookie );

            if ( is_wp_error( $user ) ) {
                $this->login_errors[] = $user->get_error_message();
                return;
            } else {
                $redirect = $this->login_redirect();
                wp_redirect( apply_filters( 'wpfep_login_redirect', $redirect, $user ) );
                exit;
            }
        }
    }

    /**
     * Redirect user to a specific page after login
     *
     * @return  string $url
     */
    function login_redirect() {

        $redirect_to = wpfep_get_option( 'redirect_after_login_page', 'wpfep_profile', false );

        if ( 'previous_page' == $redirect_to && !empty( $_POST['redirect_to'] ) ) {
            return esc_url( $_POST['redirect_to'] );
        }

        $redirect = get_permalink( $redirect_to );

        if ( !empty( $redirect ) ) {
            return $redirect;
        }

        return home_url();
    }

    /**
     * Redirect user to a specific page after login using default WordPress login form
     *
     * @return  string $url
     */
    function default_login_redirect( $redirect ) {
        $override    = wpfep_get_option( 'wp_default_login_redirect', 'wpfep_profile', false );
        $redirect_to = wpfep_get_option( 'redirect_after_login_page', 'wpfep_profile', false );

        $link = get_permalink( $redirect_to );
        if ( $override != 'on' || 'previous_page' == $redirect_to || empty( $link ) ) {
            return $redirect;
        }

        return $this->login_redirect();
    }

    /**
     * Logout the user
     *
     * @return void
     */
    function process_logout() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'logout' ) {

            if ( !$this->is_override_enabled() ) {
                return;
            }

            check_admin_referer('log-out');
            wp_logout();

            $redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : add_query_arg( array( 'loggedout' => 'true' ), $this->get_login_url() ) ;
            wp_safe_redirect( $redirect_to );
            exit();
        }
    }


    /**
     * Handle reset password form
     *
     * @return void
     */
    public function process_reset_password() {

        if ( ! isset( $_POST['wpfep_reset_password'] ) ) {
            return;
        }

        // process lost password form
        if ( isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) ) {
            wp_verify_nonce( $_POST['_wpnonce'], 'wpfep_lost_pass' );

            if ( $this->retrieve_password() ) {
                $url = add_query_arg( array( 'checkemail' => 'confirm' ), $this->get_login_url() );
                wp_redirect( $url );
                exit;
            }
        }

        // process reset password form
        if ( isset( $_POST['pass1'] ) && isset( $_POST['pass2'] ) && isset( $_POST['key'] ) && isset( $_POST['login'] ) && isset( $_POST['_wpnonce'] ) ) {

            // verify reset key again
            $user = $this->check_password_reset_key( $_POST['key'], $_POST['login'] );

            if ( is_object( $user ) ) {

                // save these values into the form again in case of errors
                $args['key']   = $_POST['key'];
                $args['login'] = $_POST['login'];

                wp_verify_nonce( $_POST['_wpnonce'], 'wpfep_reset_pass' );

                if ( empty( $_POST['pass1'] ) || empty( $_POST['pass2'] ) ) {
                    $this->login_errors[] = __( 'Please enter your password.', 'wpptm' );
                    return;
                }

                if ( $_POST[ 'pass1' ] !== $_POST[ 'pass2' ] ) {
                    $this->login_errors[] = __( 'Passwords do not match.', 'wpptm' );
                    return;
                }

                $errors = new WP_Error();

                do_action( 'validate_password_reset', $errors, $user );

                if ( $errors->get_error_messages() ) {
                    foreach ( $errors->get_error_messages() as $error ) {
                        $this->login_errors[] = $error;
                    }

                    return;
                }

                if ( ! $this->login_errors ) {

                    $this->reset_password( $user, $_POST['pass1'] );

                    do_action( 'wpfep_customer_reset_password', $user );

                    wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );
                    exit;
                }
            }

        }
    }


    /**
     * Handles sending password retrieval email to customer.
     *
     * @access public
     * @uses $wpdb WordPress Database object
     * @return bool True: when finish. False: on error
     */
    function retrieve_password() {
        global $wpdb, $wp_hasher;

        if ( empty( $_POST['user_login'] ) ) {

            $this->login_errors[] = __( 'Enter a username or e-mail address.', 'wpptm' );
            return;

        } elseif ( strpos( $_POST['user_login'], '@' ) && apply_filters( 'wpfep_get_username_from_email', true ) ) {

            $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );

            if ( empty( $user_data ) ) {
                $this->login_errors[] = __( 'There is no user registered with that email address.', 'wpptm' );
            return;
        }

        } else {

            $login = trim( $_POST['user_login'] );

            $user_data = get_user_by( 'login', $login );
        }

        do_action('lostpassword_post');

        if ( $this->login_errors ) {
            return false;
        }

        if ( ! $user_data ) {
            $this->login_errors[] = __( 'Invalid username or e-mail.', 'wpptm' );
            return false;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if ( ! $allow ) {

            $this->login_errors[] = __( 'Password reset is not allowed for this user', 'wpptm' );
            return false;

        } elseif ( is_wp_error( $allow ) ) {

            $this->login_errors[] = $allow->get_error_message();
            return false;
        }

        $key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

        if ( empty( $key ) ) {

            // Generate something random for a key...
            $key = wp_generate_password( 20, false );

            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }

            $key = time() . ':' . $wp_hasher->HashPassword( $key );

            do_action( 'retrieve_password_key', $user_login, $user_email, $key );

            // Now insert the new hash key into the db
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
        }

        // Send email notification
        $this->email_reset_pass( $user_login, $user_email, $key );

        return true;
    }

    /**
     * Retrieves a user row based on password reset key and login
     *
     * @uses $wpdb WordPress Database object
     *
     * @access public
     * @param string $key Hash to validate sending user's password
     * @param string $login The user login
     * @return object|bool User's database row on success, false for invalid keys
     */
    function check_password_reset_key( $key, $login ) {
        global $wpdb;

        //keeping backward compatible
        if ( strlen( $key ) == 20 ) {
            $key = preg_replace( '/[^a-z0-9]/i', '', $key );
        }

        if ( empty( $key ) || ! is_string( $key ) ) {
            $this->login_errors[] = __( 'Invalid key', 'wpptm' );
            return false;
        }

        if ( empty( $login ) || ! is_string( $login ) ) {
            $this->login_errors[] = __( 'Invalid Login', 'wpptm' );
            return false;
        }

        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

        if ( empty( $user ) ) {
            $this->login_errors[] = __( 'Invalid key', 'wpptm' );
            return false;
        }

        return $user;
    }

    /**
     * Successfull authenticate when enable email verfication in registration
     *
     * @param  object $user
     * @param  string $username
     * @param  string $password
     * @return object
     */
    function successfully_authenticate( $user, $username, $password ) {

        if ( !is_wp_error( $user ) ) {

            if ( $user->ID ) {
                $resend_link = add_query_arg( 'resend_activation', $user->ID, $this->get_login_url() );
                $error = new WP_Error();
                $wpfep_user = new wpfep_User( $user->ID );
                if ( ! $wpfep_user->is_verified() ) {
                    $error->add( 'acitve_user', sprintf( __( '<strong>Your account is not active.</strong><br>Please check your email for activation link. <br><a href="%s">Click here</a> to resend the activation link', 'wpptm' ), $resend_link ) );
                    return $error;
                }
            }
        }

        return $user;
    }

    /**
     * Check in activation of user registration
     *
     * @since 1.0.0
     */
    function activation_user_registration() {

        if ( !isset( $_GET['wpfep_registration_activation'] ) && empty( $_GET['wpfep_registration_activation'] ) ) {
            return;
        }

        if ( !isset( $_GET['id'] ) && empty( $_GET['id'] ) ) {
            wpfep()->login->add_error( __('Activation URL is not valid', 'wpptm') );
            return;
        }

        $user_id          = intval( $_GET['id'] );
        $user             =  new wpfep_User( $user_id );
        $wpfep_user_active = get_user_meta( $user_id, '_wpfep_user_active', true );
        $wpfep_user_status = get_user_meta( $user_id, 'wpfep_user_status', true );

        if ( !$user ) {
            wpfep()->login->add_error( __('Invalid User activation url', 'wpptm') );
            return;
        }

        if ( $user->is_verified() ){
            wpfep()->login->add_error( __('User already verified', 'wpptm') );
            return;
        }

        $activation_key = $_GET['wpfep_registration_activation'];

        if ( $user->get_activation_key() != $activation_key ) {
            wpfep()->login->add_error( __('Activation URL is not valid', 'wpptm') );
            return;
        }

        $user->mark_verified();
        $user->remove_activation_key();

        $message = __( "Your account has been activated", "wpptm" );

        if ( $wpfep_user_status != "approved" ) {
            $message = __( "Your account has been verified , but you can't login until manually approved your account by an administrator.", "wpptm" );
        }

        wpfep()->login->add_message( $message );

        // show activation message
        add_filter( 'wp_login_errors', array( $this, 'user_activation_message' ) );

        $password_info_email = isset( $_GET['wpfep_password_info_email'] ) ? $_GET['wpfep_password_info_email'] : false;
        $the_user            = get_user_by( 'id', $user_id );
        $user_email          = $the_user->user_email;
        $blogname            = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        if ( $password_info_email ) {
            global $wpdb, $wp_hasher;

            // Generate something random for a password reset key.
            $key = wp_generate_password( 20, false );

            /** This action is documented in wp-login.php */
            add_action( 'retrieve_password_key', $the_user->user_login, $key );

            // Now insert the key, hashed, into the DB.
            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }
            $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $the_user->user_login ) );

            $subject = sprintf( __('[%s] Your username and password info', 'wpptm' ), $blogname );

            $message  = sprintf(__('Username: %s', 'wpptm'), $the_user->user_login) . "\r\n\r\n";
            $message .= __('To set your password, visit the following address:', 'wpptm') . "\r\n\r\n";
            $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($the_user->user_login), 'login') . "\r\n\r\n";
            $message .= wp_login_url() . "\r\n";

            $subject  = apply_filters( 'wpfep_password_info_mail_subject', $subject );
            $message  = apply_filters( 'wpfep_password_info_mail_body', $message );
            $message  = get_formatted_mail_body( $message, $subject );

            wp_mail( $user_email, $subject, $message );
        } else {
            $subject  = sprintf( __('[%s] Account has been activated', 'wpptm' ), $blogname );

            $message  = sprintf( __('Hi %s,', 'wpptm' ), $the_user->user_login ) ."\r\n\r\n";
            $message .= __( "Congrats! Your account has been activated. To login visit the following url:", "wpptm") ."\r\n\r\n";
            $message .= wp_login_url() ."\r\n\r\n";
            $message .= __( "Thanks", "wpptm" );

            $subject  = apply_filters( 'wpfep_mail_after_confirmation_subject', $subject );
            $message  = apply_filters( 'wpfep_mail_after_confirmation_body', $message );
            $message  = get_formatted_mail_body( $message, $subject );

            wp_mail( $user_email, $subject, $message );
        }
        add_filter('redirect_canonical', '__return_false');
        do_action( 'wpfep_user_activated', $user_id );
    }

    /**
     * Shows activation message on success to wp-login.php
     *
     * @since 1.0.0
     * @return \WP_Error
     */
    function user_activation_message() {
        return new WP_Error( 'user-activated', __( 'Your account has been activated', 'wpptm' ), 'message' );
    }

    function wp_login_page_redirect() {
        global $pagenow;

        if ( ! is_admin() && $pagenow == 'wp-login.php' && isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {

            if ( wpfep_get_option( 'register_link_override', 'wpfep_profile' ) != 'on' ) {
                return;
            }

            $reg_page = get_permalink( wpfep_get_option( 'reg_override_page', 'wpfep_profile' ) );
            wp_redirect( $reg_page );
            exit;
        }
    }

    /**
     * Handles resetting the user's password.
     *
     * @access public
     * @param object $user The user
     * @param string $new_pass New password for the user in plaintext
     * @return void
     */
    public function reset_password( $user, $new_pass ) {
        do_action( 'password_reset', $user, $new_pass );

        wp_set_password( $new_pass, $user->ID );

        wp_password_change_notification( $user );
    }

    /**
     * Email reset password link
     *
     * @param string $user_login
     * @param string $user_email
     * @param string $key
     */
    function email_reset_pass( $user_login, $user_email, $key ) {
        $reset_url = add_query_arg( array( 'action' => 'rp', 'key' => $key, 'login' => urlencode( $user_login ) ), $this->get_login_url() );

        $message = __('Someone requested that the password be reset for the following account:', 'wpptm') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'wpptm' ), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'wpptm') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:', 'wpptm') . "\r\n\r\n";
        $message .= ' ' . $reset_url . " \r\n";

        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        if ( is_multisite() ) {
            $blogname = $GLOBALS['current_site']->site_name;
        }

        $title   = sprintf( __('[%s] Password Reset', 'wpptm' ), $blogname );
        $title   = apply_filters( 'retrieve_password_title', $title );

        $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login );

        if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
            wp_die( __('The e-mail could not be sent.', 'wpptm') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', 'wpptm') );
        }
    }

    /**
     * Add Error message
     *
     * @since 1.0.0
     *
     * @param $message
     */
    public function add_error( $message ) {
        $this->login_errors[] = $message;
    }

    /**
     * Add info message
     *
     * @since 1.0.0
     *
     * @param $message
     */
    public function add_message( $message ) {
        $this->messages[] = $message;
    }
    /**
     * Show erros on the form
     *
     * @return void
     */
    function show_errors() {
        if ( $this->login_errors ) {
            foreach ($this->login_errors as $error) {
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
