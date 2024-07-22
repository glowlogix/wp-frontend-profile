<?php
/**
 * @package wp-front-end-profile
 * User Registration Class.
 */

defined('ABSPATH') || exit;

if (! class_exists('WPFEP_Registration')) {
    /**
     * Registration handler class.
     */
    class WPFEP_Registration
    {
        /**
         * Error array.
         *
         * @var array
         */
        private $registration_errors = array();
        /**
         * Message array.
         *
         * @var array
         */
        private $messages = array();
        /**
         * Message array.
         *
         * @var instance
         */
        private static $instance;

        /**
         * Error array.
         *
         * @var array
         */
        public $atts = array();
        /**
         * Error array.
         *
         * @var array
         */
        public $userrole = '';

        /**
         * Define template file.
         */
        public function __construct()
        {
            add_shortcode('wpfep-register', array( $this, 'registration_form' ));
            add_action('init', array( $this, 'process_registration' ));
        }

        /**
         * Singleton object.
         *
         * @return self
         */
        public static function init()
        {
            if (! self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Shows the registration form.
         *
         * @param array $atts return attributes.
         *
         * @return string
         */
        public function registration_form()
        {
            ob_start();

            if (is_user_logged_in()) {
                wpfep_load_template(
                    'logged-in.php',
                    array(
                        'user' => wp_get_current_user(),
                    )
                );
            } else {
                $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'register';

                $args = array(
                    'action_url' => wpfep_get_option('register_page', 'wpfep_pages', false),
                );
                wpfep_load_template('registration.php', $args);
            }

            return ob_get_clean();
        }

        /**
         * Process registration form.
         *
         * @return void
         */
        public function process_registration()
        {
            $wp_general_options = get_option('wpfep_general');
            
            if ($wp_general_options && isset($wp_general_options['wpfep_new_user_role'])) {
                $default_user_role = $wp_general_options['wpfep_new_user_role'];
            } else {
                $default_user_role="subscriber";
            }
            if (! empty($_POST['wpfep_registration']) && ! empty($_POST['_wpnonce'])) {
                $nonce_action = 'wpfep_registration_action';

                if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), $nonce_action)) {
                    wp_die();
                }

                $userdata = array();
                $validation_error = new WP_Error();
                $validation_error = apply_filters('wpfep_process_registration_errors', $validation_error, sanitize_text_field(wp_unslash(isset($_POST['wpfep_reg_email']))), sanitize_text_field(wp_unslash(isset($_POST['wpfep_reg_uname']))), sanitize_text_field(wp_unslash(isset($_POST['pwd1']))), sanitize_text_field(wp_unslash(isset($_POST['pwd2']))));

                if ($validation_error->get_error_code()) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . $validation_error->get_error_message();

                    return;
                }

                if (empty($_POST['wpfep_reg_email'])) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Email is required.', 'wpfep');

                    return;
                }

                if (empty($_POST['wpfep_reg_uname'])) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Username is required.', 'wpfep');

                    return;
                }

                if (empty($_POST['pwd1'])) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Password is required.', 'wpfep');

                    return;
                }

                if (empty($_POST['pwd2'])) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Confirm Password is required.', 'wpfep');

                    return;
                }

                if ($_POST['pwd1'] != $_POST['pwd2']) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Passwords are not same.', 'wpfep');

                    return;
                }
                $enable_strong_pwd = wpfep_get_option('strong_password', 'wpfep_general');
                if ('off' !== $enable_strong_pwd) {
                    /* get the length of the password entered */
                    $password    = isset($_POST['pwd1']) ? $_POST['pwd1'] : '';
                    $pass_length = strlen($password);

                    /* check the password match the correct length. */
                    if ($pass_length < 12) {
                        /* add message indicating length issue!! */
                        $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Please make sure your password is a minimum of 12 characters long', 'wpfep');

                        return;
                    }

                    /**
                     * Match the password against a regex of complexity.
                     * at least 1 upper, 1 lower case letter and 1 number.
                     */
                    $pass_complexity = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d,.;:]).+$/', $password);

                    /* check whether the password passed the regex check of complexity */
                    if (false == $pass_complexity) {
                        /* add message indicating complexity issue */
                        $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('Your password must contain at least 1 uppercase, 1 lowercase letter and at least 1 number.', 'wpfep');

                        return;
                    }
                }
                // sanitize fields.
                if (isset($_POST['wpfep_reg_email']) != sanitize_email(wp_unslash($_POST['wpfep_reg_email']))) {
                    return;
                }

                if (isset($_POST['wpfep_reg_uname']) == sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname']))) {
                    $reg_name = sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname']));
                } else {
                    $reg_name = '';
                }

                if (isset($_POST['wpfep_reg_fname']) == sanitize_text_field(wp_unslash($_POST['wpfep_reg_fname']))) {
                    $user_fname = sanitize_text_field(wp_unslash($_POST['wpfep_reg_fname']));
                } else {
                    $user_fname = '';
                }
                if (isset($_POST['wpfep_reg_lname']) == sanitize_text_field(wp_unslash($_POST['wpfep_reg_lname']))) {
                    $user_lname = sanitize_text_field(wp_unslash($_POST['wpfep_reg_lname']));
                } else {
                    $user_lname = '';
                }
                if (isset($_POST['wpfep-description'])) {
                    $desc = sanitize_text_field(wp_unslash($_POST['wpfep-description']));
                } else {
                    $desc = '';
                }
                if (isset($_POST['wpfep-website']) == sanitize_text_field(wp_unslash($_POST['wpfep-website']))) {
                    $user_web = sanitize_text_field(wp_unslash($_POST['wpfep-website']));
                } else {
                    $user_web = '';
                }
                if (isset($_POST['g-recaptcha-response'])) {
                    if (empty($_POST['g-recaptcha-response'])) {
                        $this->registration_errors[] = __('reCaptcha is required', 'wpfep');

                        return;
                    } else {
                        $no_captcha        = 1;
                        $invisible_captcha = 0;

                        WPFEP_Captcha_Recaptcha::captcha_verification();
                    }
                }

                if (get_user_by('login', sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname']))) === sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname']))) {
                    $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('A user with same username already exists.', 'wpfep');

                    return;
                }

                if (is_email(wp_unslash($_POST['wpfep_reg_uname'])) && apply_filters('wpfep_get_username_from_email', true)) {
                    $user = get_user_by('email', sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname'])));

                    if (isset($user->user_login)) {
                        $userdata['user_login'] = $user->user_login;
                    } else {
                        $this->registration_errors[] = '<strong>' . __('Error', 'wpfep') . ':</strong> ' . __('A user could not be found with this email address.', 'wpfep');

                        return;
                    }
                } else {
                    $userdata['user_login'] = sanitize_text_field(wp_unslash($_POST['wpfep_reg_uname']));
                }
                $userdata['first_name']  = $user_fname;
                $userdata['last_name']   = $user_lname;
                $userdata['user_email']  = sanitize_email(wp_unslash($_POST['wpfep_reg_email']));
                $userdata['user_pass']   = sanitize_text_field(wp_unslash($_POST['pwd1']));
                $userdata['description'] = $desc;
                $userdata['user_url']    = $user_web;
                $userdata['role'] = $default_user_role; // Set the user's role
                $send_link_activation         = wpfep_get_option('user_behave', 'wpfep_profile');
                $manually_register            = wpfep_get_option('admin_can_register_user_manually', 'wpfep_profile', 'on');
                $manually_approve_user        = wpfep_get_option('admin_manually_approve', 'wpfep_profile', 'on');
                $autologin_after_registration = wpfep_get_option('user_behave', 'wpfep_profile');
                $user                         = wp_insert_user($userdata);
                add_user_meta($user, 'wpfep_user_status', 'pending');
                if (is_wp_error($user)) {
                    $this->registration_errors[] = $user->get_error_message();

                    return;
                } else {
                    if (current_user_can('administrator') && 'on' === $manually_register) {
                        $password = $userdata['user_pass'];
                        add_user_meta($user, 'wpfep_user_status', 'pending');
                        $wpfep_user = new WP_User($user);
                        $user_login = stripslashes($wpfep_user->user_login);
                        $user_email = stripslashes($wpfep_user->user_email);
                        $blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                        /* translators: %s: search term */
                        $message = sprintf(esc_html__('New user registration on your site %s:', 'wpfep'), get_option('blogname')) . "\r\n\r\n";
                        /* translators: %s: user login */
                        $message .= sprintf(esc_html__('Username: %s', 'wpfep'), $user_login) . "\r\n\r\n";
                        /* translators: %s: user email */
                        $message .= sprintf(esc_html__('E-mail: %s', 'wpfep'), $user_email) . "\r\n";
                        /* translators: %s: user pass */
                        $subject = esc_html__('New User Registration', 'wpfep');

                        $subject             = apply_filters('wpfep_default_reg_admin_mail_subject', $subject);
                        $message             = apply_filters('wpfep_default_reg_admin_mail_body', $message);
                        $register_admin_mail = wpfep_get_option('new_account_admin_mail', 'wpfep_emails_notification', 'on');
                        if ('on' === $register_admin_mail) {
                            /* translators: %s: user email */
                            wp_mail(get_option('admin_email'), sprintf(esc_html__('[%1$s] %2$s', 'wpfep'), $blogname, $subject), $message);
                        }
                        /* translators: %s: user login */
                        $message  = sprintf(esc_html__('Hi, %s', 'wpfep'), $user_login) . "\r\n";
                        $message .= 'Congrats! You are Successfully registered to  ' . $blogname . "\r\n\r\n";
                        /* translators: %s: user login */
                        $message .= sprintf(esc_html__('Username: %s', 'wpfep'), $user_login) . "\r\n\r\n";
                        /* translators: %s: user email */
                        $message .= sprintf(esc_html__('E-mail: %s', 'wpfep'), $user_email) . "\r\n";
                        /* translators: %s: user pass */
                        $message           .= sprintf(esc_html__('Password %s', 'wpfep'), $password) . "\r\n\r\n\r\n";
                        $message           .= 'Thanks' . "\r\n\r\n";
                        $subject            = 'Registration by admin of ' . $blogname . '';
                        $subject            = apply_filters('wpfep_default_reg_mail_subject', $subject);
                        $message            = apply_filters('wpfep_default_reg_mail_body', $message);
                        $register_user_mail = wpfep_get_option('register_mail', 'wpfep_emails_notification', 'on');
                        if ('on' === $register_user_mail) {
                            /* translators: %1s: user login */
                            wp_mail($user_email, sprintf(esc_html__('[%1$s] %2$s', 'wpfep'), $blogname, $subject), $message);
                        }
                    } else {
                        $wpfep_user = new WP_User($user);
                        $user_login = stripslashes($wpfep_user->user_login);
                        $user_email = stripslashes($wpfep_user->user_email);
                        $blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
                        /* translators: %s: search term */
                        $message = sprintf(esc_html__('New user registration on your site %s:', 'wpfep'), get_option('blogname')) . "\r\n\r\n";
                        /* translators: %s: user login */
                        $message .= sprintf(esc_html__('Username: %s', 'wpfep'), $user_login) . "\r\n\r\n";
                        /* translators: %s: user email */
                        $message .= sprintf(esc_html__('E-mail: %s', 'wpfep'), $user_email) . "\r\n";
                        $subject  = esc_html__('New User Registration', 'wpfep');

                        $subject             = apply_filters('wpfep_default_reg_admin_mail_subject', $subject);
                        $message             = apply_filters('wpfep_default_reg_admin_mail_body', $message);
                        $register_admin_mail = wpfep_get_option('new_account_admin_mail', 'wpfep_emails_notification', 'on');
                        if ('on' === $register_admin_mail) {
                            /* translators: %s: user email */
                            wp_mail(get_option('admin_email'), sprintf(esc_html__('[%1$s] %2$s', 'wpfep'), $blogname, $subject), $message);
                        }
                        /* translators: %s: user login */
                        $message  = sprintf(esc_html__('Hi, %s', 'wpfep'), $user_login) . "\r\n";
                        $message .= 'Congrats! You are Successfully registered to ' . $blogname . "\r\n\r\n";
                        $message .= 'Thanks';
                        $subject  = 'Thank you for registering';

                        $subject            = apply_filters('wpfep_default_reg_mail_subject', $subject);
                        $message            = apply_filters('wpfep_default_reg_mail_body', $message);
                        $register_user_mail = wpfep_get_option('register_mail', 'wpfep_emails_notification', 'on');
                        if ('on' === $register_user_mail) {
                            /* translators: %1s: user login */
                            wp_mail($user_email, sprintf(esc_html__('[%1$s] %2$s', 'wpfep'), $blogname, $subject), $message);
                        }
                    }
                    if ('activate_mail' == $send_link_activation) {
                        $wpfep_user = new WPFEP_User($user);
                        $wpfep_user->wpfep_new_user($user);
                        $register_page = wpfep_get_option('register_page', 'wpfep_pages');
                        wp_safe_redirect(add_query_arg(array( 'success' => 'notactivated' ), get_permalink($register_page)));
                        if ('off' === $manually_approve_user && 'off' === $manually_register) {
                            exit;
                        }
                    }
                    if ('none' == $send_link_activation) {
                        $wpfep_user = new WPFEP_User($user);
                        $wpfep_user->wpfep_new_user($user);
                        $register_page = wpfep_get_option('register_page', 'wpfep_pages');
                        $redirect      = get_permalink($register_page) . '?success=yes';
                        wp_safe_redirect($redirect);
                        exit;
                    }
                    if ('on' == $manually_approve_user) {
                        $wpfep_user = new WPFEP_User($user);
                        $wpfep_user->manually_approve($user);
                        $register_page = wpfep_get_option('register_page', 'wpfep_pages');
                        wp_safe_redirect(add_query_arg(array( 'success' => 'notapproved' ), get_permalink($register_page)));
                        exit;
                    }

                    $autologin_after_registration = wpfep_get_option('user_behave', 'wpfep_profile');

                    if ('auto_login' === $autologin_after_registration && ! current_user_can('administrator')) {
                        wp_clear_auth_cookie();
                        wp_set_current_user($user);
                        wp_set_auth_cookie($user);
                    }
                    $redirect_after_registration = wpfep_get_option('redirect_after_registration', 'wpfep_profile');
                    $register_page               = wpfep_get_option('register_page', 'wpfep_pages');
                    if (is_wp_error($user)) {
                        $this->registration_errors[] = $user->get_error_message();

                        return;
                    } else {
                        if ('on' === $manually_register && current_user_can('administrator')) {
                            $register_page = wpfep_get_option('register_page', 'wpfep_pages');
                            $redirect      = add_query_arg(array( 'success' => 'createdmanually' ), get_permalink($register_page));
                            wp_safe_redirect(apply_filters('wpfep_registration_redirect', $redirect, $user));
                            exit;
                        } elseif ('auto_login' === $autologin_after_registration) {
                            if ('auto_login' === $autologin_after_registration && '' === $redirect_after_registration) {
                                $redirect = home_url();
                            } elseif ('' !== $redirect_after_registration) {
                                $redirect = get_permalink($redirect_after_registration);
                            } else {
                                $redirect = add_query_arg(array( 'success' => 'yes' ), get_permalink($register_page));
                                add_user_meta($user, 'wpfep_user_status', 'approve');
                            }

                            wp_safe_redirect(apply_filters('wpfep_registration_redirect', $redirect, $user));
                            exit;
                        }
                    }
                }
            }
        }

        /**
         * Show errors on the form.
         *
         * @return void
         */
        public function show_errors()
        {
            if ($this->registration_errors) {
                foreach ($this->registration_errors as $error) {
                    echo '<div class="wpfep-error">';
                    echo wp_kses(
                        $error,
                        array(
                            'strong' => array(
                                'href'  => array(),
                                'title' => array(),
                            ),
                        )
                    );
                    echo '</div>';
                }
            }
        }

        /**
         * Nonce verification on the form.
         *
         * @param (string) $key returns key for the form.
         *
         * @return key
         */
        public function get_post_value($key)
        {
            // Check if the nonce is set in the POST request
            $nonce_action = 'wpfep_registration_action';

            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), $nonce_action)) {
                return '';
            }


            // Check if the key is set in the POST request
            if (isset($_POST[$key])) {
                return esc_attr(sanitize_text_field(wp_unslash($_POST[$key])));
            }

            return '';
        }



        /**
         * Show messages on the form.
         *
         * @return void
         */
        public function show_messages()
        {
            if ($this->messages) {
                foreach ($this->messages as $message) {
                    printf('<div class="wpfep-message">%s</div>', esc_html($message));
                }
            }
        }
    }
}
