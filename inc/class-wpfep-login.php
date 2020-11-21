<?php
/**
 * Login Class.
 */
defined('ABSPATH') || exit;

/**
 * Login and forgot password handler class.
 */
if (!class_exists('WPFEP_Login')) {
    /**
     * Login and forgot password handler class.
     */
    class WPFEP_Login
    {
        /**
         * Login error messages array.
         *
         * @var array
         */
        private $login_errors = [];

        /**
         * Login messages array.
         *
         * @var array
         */
        private $messages = [];

        /**
         * Login messages array.
         *
         * @var static
         */
        private static $_instance;

        /**
         * Login actions.
         */
        public function __construct()
        {
            add_shortcode('wpfep-login', [$this, 'login_form']);

            add_action('init', [$this, 'process_login']);
            add_action('init', [$this, 'process_logout']);
            add_action('init', [$this, 'process_reset_password']);

            add_action('init', [$this, 'wp_login_page_redirect']);
            add_action('init', [$this, 'activation_user_registration']);
            add_action('login_form', [$this, 'add_custom_fields']);

            // URL filters.
            add_filter('login_url', [$this, 'filter_login_url'], 10, 2);
            add_filter('logout_url', [$this, 'filter_logout_url'], 10, 2);
            add_filter('lostpassword_url', [$this, 'filter_lostpassword_url'], 10, 2);
            add_filter('authenticate', [$this, 'successfully_authenticate'], 30, 3);
        }

        /**
         * Singleton object.
         *
         * @return self
         */
        public static function init()
        {
            if (!self::$_instance) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Add custom fields to WordPress default login form.
         *
         * @since 1.0.0
         */
        public function add_custom_fields()
        {
            $recaptcha = wpfep_get_option('enable_captcha_login', 'wpfep_profile');

            if ('on' == $recaptcha) {
                WPFEP_Captcha_Recaptcha::display_captcha();
            }
        }

        /**
         * Get action url based on action type.
         *
         * @param string $action      return the action.
         * @param string $redirect_to url to redirect to.
         *
         * @return string
         */
        public function get_action_url($action = 'login', $redirect_to = '')
        {
            $root_url = $this->get_login_url();

            switch ($action) {
                case 'resetpass':
                    return add_query_arg(['action' => 'resetpass'], $root_url);
                    break;

                case 'lostpassword':
                    return add_query_arg(['action' => 'lostpassword'], $root_url);
                    break;

                case 'logout':
                    return wp_nonce_url(add_query_arg(['action' => 'logout'], $root_url), 'log-out');
                    break;

                default:
                    if (empty($redirect_to)) {
                        return $root_url;
                    }

                    return add_query_arg(['redirect_to' => urlencode($redirect_to)], $root_url);
                    break;
            }
        }

        /**
         * Get login page url.
         *
         * @return bool|string
         */
        public function get_login_url()
        {
            $page_id = wpfep_get_option('login_page', 'wpfep_pages', false);

            if (!$page_id) {
                return false;
            }

            $url = get_permalink($page_id);

            return apply_filters('wpfep_login_url', $url, $page_id);
        }

        /**
         * Filter the login url with ours.
         *
         * @param string $url      return url.
         * @param string $redirect redirect user.
         *
         * @return string
         */
        public function filter_login_url($url, $redirect)
        {
            return $this->get_action_url('login', $redirect);
        }

        /**
         * Filter the logout url with ours.
         *
         * @param string $url      return url.
         * @param string $redirect redirect user.
         *
         * @return string
         */
        public function filter_logout_url($url, $redirect)
        {
            return $this->get_action_url('logout', $redirect);
        }

        /**
         * Filter the lost password url with ours.
         *
         * @param string $url      return url.
         * @param string $redirect redirect user.
         *
         * @return string
         */
        public function filter_lostpassword_url($url, $redirect)
        {
            return $this->get_action_url('lostpassword', $redirect);
        }

        /**
         * Get actions links for displaying in forms.
         *
         * @return string
         */
        public function lost_password_links()
        {
            $links = sprintf('<a href="%s">%s</a>', $this->get_action_url('lostpassword'), __('Lost Password', 'wp-front-end-profile'));

            return $links;
        }

        /**
         * Shows the login form.
         *
         * @return string
         */
        public function login_form()
        {
            global $wp;
            $login_page = $this->get_login_url();

            ob_start();

            if (is_user_logged_in()) {
                wpfep_load_template(
                    'logged-in.php',
                    [
                        'user' => wp_get_current_user(),
                    ]
                );
            } else {
                $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'login';
                $args = [
                    'action_url' => $login_page,
                ];

                switch ($action) {
                    case 'lostpassword':
                        $this->messages[] = __('Please enter your username or email address. You will receive a link to create a new password via email.', 'wp-front-end-profile');

                        wpfep_load_template('lost-pass.php', $args);
                        break;

                    case 'rp':
                    case 'resetpass':
                        if ('true' == isset($_GET['reset']) && sanitize_text_field(wp_unslash($_GET['reset']))) {
                            printf('<div class="wpfep-message">'.esc_html__('Your password has been reset.', 'wp-front-end-profile').'</div>');
                            wpfep_load_template('login.php', $args);

                            return;
                        } else {
                            $this->messages[] = __('Enter your new password below..', 'wp-front-end-profile');

                            wpfep_load_template('reset-pass.php', $args);
                        }

                        break;

                    default:
                        if ('confirm' == isset($_GET['checkemail']) && sanitize_text_field(wp_unslash($_GET['checkemail']))) {
                            $this->messages[] = __('Check your e-mail for the confirmation link.', 'wp-front-end-profile');
                        }

                        if ('true' == isset($_GET['loggedout']) && sanitize_text_field(wp_unslash($_GET['loggedout']))) {
                            $this->messages[] = __('You are now logged out.', 'wp-front-end-profile');
                        }

                        wpfep_load_template('login.php', $args);

                        break;
                }
            }

            return ob_get_clean();
        }

        /**
         * Process login form.
         *
         * @return void
         */
        public function process_login()
        {
            if (!empty($_POST['wpfep_login']) && !empty($_POST['_wpnonce'])) {
                $creds = [];
                $manually_approve_user = wpfep_get_option('admin_manually_approve', 'wpfep_profile', 'on');
                if (isset($_POST['_wpnonce'])) {
                    wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'wpfep_login_action');
                }

                $validation_error = new WP_Error();
                $validation_error = apply_filters('wpfep_process_login_errors', $validation_error, sanitize_text_field(wp_unslash(isset($_POST['log']))), sanitize_text_field(wp_unslash(isset($_POST['pwd']))));

                if ($validation_error->get_error_code()) {
                    $this->login_errors[] = $validation_error->get_error_message();

                    return;
                }

                if (empty($_POST['log'])) {
                    $this->login_errors[] = __('Username is required.', 'wp-front-end-profile');

                    return;
                }

                if (empty($_POST['pwd'])) {
                    $this->login_errors[] = __('Password is required.', 'wp-front-end-profile');

                    return;
                }

                if (isset($_POST['g-recaptcha-response'])) {
                    if (empty($_POST['g-recaptcha-response'])) {
                        $this->login_errors[] = __('Empty reCaptcha Field', 'wp-front-end-profile');

                        return;
                    } else {
                        $no_captcha = 1;
                        $invisible_captcha = 0;

                        WPFEP_Captcha_Recaptcha::captcha_verification();
                    }
                }
                $user = '';
                if (is_email(sanitize_text_field(wp_unslash($_POST['log']))) && apply_filters('wpfep_get_username_from_email', true)) {
                    $user = get_user_by('email', sanitize_text_field(wp_unslash($_POST['log'])));
                } else {
                    $user = get_user_by('login', sanitize_text_field(wp_unslash($_POST['log'])));
                }
                $user_behave = wpfep_get_option('user_behave', 'wpfep_profile');
                $user_meta = get_user_meta($user->ID, 'wpfep_user_status', true);
                if (('activate_mail' == $user_behave) && (('Yes' != get_user_meta($user->ID, 'verify', true)) || (get_user_meta($user->ID, 'has_to_be_activated', true) == false))) {
                    $this->login_errors[] = '<strong>'.__('Error', 'wp-front-end-profile').':</strong> '.__('Please verify your account.', 'wp-front-end-profile');

                    return;
                }
                if (('on' == $manually_approve_user) && ('pending' == $user_meta)) {
                    $this->login_errors[] = '<strong>'.__('Error', 'wp-front-end-profile').':</strong> '.__("Your account hasn't been approved by the administrator.", 'wp-front-end-profile');

                    return;
                }
                if (('on' == $manually_approve_user) && ('rejected' == $user_meta)) {
                    $this->login_errors[] = '<strong>'.__('Error', 'wwp-front-end-profile').':</strong> '.__('Your account was not approved by the administrator.', 'wp-front-end-profile');

                    return;
                }
                if (isset($user->user_login)) {
                    $creds['user_login'] = $user->user_login;
                } else {
                    $this->login_errors[] = '<strong>'.__('Error', 'wp-front-end-profile').':</strong> '.__('A user could not be found with this email address.', 'wp-front-end-profile');

                    return;
                }

                $creds['user_password'] = sanitize_text_field(wp_unslash($_POST['pwd']));
                $creds['remember'] = isset($_POST['rememberme']);

                if (isset($user->user_login)) {
                    $validate = wp_authenticate_email_password(null, trim(sanitize_text_field(wp_unslash(isset($_POST['log'])))), sanitize_text_field(wp_unslash($creds['user_password'])));
                    if (is_wp_error($validate)) {
                        $this->login_errors[] = $validate->get_error_message();

                        return;
                    }
                }

                $secure_cookie = is_ssl() ? true : false;
                $user = wp_signon(apply_filters('wpfep_login_credentials', $creds), $secure_cookie);

                if (is_wp_error($user)) {
                    $this->login_errors[] = $user->get_error_message();

                    return;
                } else {
                    $redirect = $this->login_redirect();
                    wp_redirect(apply_filters('wpfep_login_redirect', $redirect, $user));
                    exit;
                }
            }
        }

        /**
         * Redirect user to a specific page after login.
         *
         * @return string $url
         */
        public function login_redirect()
        {
            $redirect_to = wpfep_get_option('redirect_after_login_page', 'wpfep_profile', false);

            if ('previous_page' == $redirect_to && !empty($_POST['redirect_to'])) {
                return esc_url(wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['redirect_to']))));
            }

            $redirect = get_permalink($redirect_to);

            if (!empty($redirect)) {
                return $redirect;
            }

            return home_url();
        }

        /**
         * Logout the user.
         *
         * @return void
         */
        public function process_logout()
        {
            if (isset($_GET['action']) && 'logout' == $_GET['action']) {
                check_admin_referer('log-out');
                wp_logout();

                $redirect_to = !empty($_REQUEST['redirect_to']) ? sanitize_text_field(wp_unslash($_REQUEST['redirect_to'])) : add_query_arg(['loggedout' => 'true'], $this->get_login_url());
                wp_safe_redirect($redirect_to);
                exit();
            }
        }

        /**
         * Handle reset password form.
         *
         * @return void
         */
        public function process_reset_password()
        {
            if (!isset($_POST['wpfep_reset_password'])) {
                return;
            }

            // process lost password form.
            if (isset($_POST['user_login']) && isset($_POST['_wpnonce'])) {
                wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpfep_lost_pass');

                if ($this->retrieve_password()) {
                    $url = add_query_arg(['checkemail' => 'confirm'], $this->get_login_url());
                    wp_redirect($url);
                    exit;
                }
            }

            // process reset password form.
            if (isset($_POST['pass1']) && isset($_POST['pass2']) && isset($_POST['key']) && isset($_POST['login']) && isset($_POST['_wpnonce'])) {

                // verify reset key again.
                $user = $this->check_password_reset_key($_POST['key'], sanitize_text_field(wp_unslash($_POST['login'])));

                if (is_object($user)) {

                    // save these values into the form again in case of errors.
                    $args['key'] = $_POST['key'];
                    $args['login'] = sanitize_text_field(wp_unslash($_POST['login']));

                    wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpfep_reset_pass');

                    if (empty($_POST['pass1']) || empty($_POST['pass2'])) {
                        $this->login_errors[] = __('Please enter your password.', 'wp-front-end-profile');

                        return;
                    }

                    if ($_POST['pass1'] !== $_POST['pass2']) {
                        $this->login_errors[] = __('Passwords do not match.', 'wp-front-end-profile');

                        return;
                    }
                    $enable_strong_pwd = wpfep_get_option('strong_password', 'wpfep_general');
                    if ('off' != $enable_strong_pwd) {
                        /* get the length of the password entered */
                        $password = $_POST['pass1'];
                        $pass_length = strlen($password);

                        /* check the password match the correct length */
                        if ($pass_length < 12) {

                            /* add message indicating length issue!! */

                            $this->login_errors[] = '<strong>'.__('Error', 'wp-front-end-profile').':</strong> '.__('Please make sure your password is a minimum of 12  characters long', 'wp-front-end-profile');

                            return;
                        }

                        /**
                         * Match the password against a regex of complexity
                         * at least 1 upper, 1 lower case letter and 1 number.
                         */
                        $pass_complexity = preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d,.;:]).+$/', $password);

                        /* check whether the password passed the regex check of complexity */
                        if (false == $pass_complexity) {

                            /* add message indicating complexity issue */
                            $this->login_errors[] = '<strong>'.__('Error', 'wp-front-end-profile').':</strong> '.__('Your password must contain at least 1 uppercase, 1 lowercase letter and at least 1 number.', 'wp-front-end-profile');

                            return;
                        }
                    }

                    $errors = new WP_Error();

                    do_action('validate_password_reset', $errors, $user);

                    if ($errors->get_error_messages()) {
                        foreach ($errors->get_error_messages() as $error) {
                            $this->login_errors[] = $error;
                        }

                        return;
                    }

                    if (!$this->login_errors) {
                        $this->reset_password($user, $_POST['pass1']);

                        do_action('wpfep_customer_reset_password', $user);

                        wp_redirect(add_query_arg('reset', 'true', remove_query_arg(['key', 'login'])));
                        exit;
                    }
                }
            }
        }

        /**
         * Handles sending password retrieval email to customer.
         *
         * @uses $wpdb WordPress Database object
         *
         * @return bool True: when finish. False: on error
         */
        public function retrieve_password()
        {
            global $wpdb, $wp_hasher;

            if (isset($_POST['_wpnonce'])) {
                wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpfep_lost_pass');
                if (empty($_POST['user_login'])) {
                    $this->login_errors[] = __('Enter a username or e-mail address.', 'wp-front-end-profile');

                    return;
                } elseif (strpos(sanitize_text_field(wp_unslash($_POST['user_login'])), '@') && apply_filters('wpfep_get_username_from_email', true)) {
                    $user_data = get_user_by('email', sanitize_text_field(wp_unslash($_POST['user_login'])));

                    if (empty($user_data)) {
                        $this->login_errors[] = __('There is no user registered with that email address.', 'wp-front-end-profile');

                        return;
                    }
                } else {
                    $login = sanitize_text_field(wp_unslash($_POST['user_login']));

                    $user_data = get_user_by('login', $login);
                }
            }

            do_action('lostpassword_post');

            if ($this->login_errors) {
                return false;
            }

            if (!$user_data) {
                $this->login_errors[] = __('Invalid username or e-mail.', 'wp-front-end-profile');

                return false;
            }

            // redefining user_login ensures we return the right case in the email.
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;

            do_action('retrieve_password', $user_login);

            $allow = apply_filters('allow_password_reset', true, $user_data->ID);

            if (!$allow) {
                $this->login_errors[] = __('Password reset is not allowed for this user', 'wp-front-end-profile');

                return false;
            } elseif (is_wp_error($allow)) {
                $this->login_errors[] = $allow->get_error_message();

                return false;
            }

            $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

            if (empty($key)) {

                // Generate something random for a key...
                $key = wp_generate_password(20, false);

                if (empty($wpfep_hasher)) {
                    require_once ABSPATH.WPINC.'/class-phpass.php';
                    $wpfep_hasher = new PasswordHash(8, true);
                }

                $key = time().':'.$wpfep_hasher->HashPassword($key);

                do_action('retrieve_password_key', $user_login, $user_email, $key);

                // Now insert the new hash key into the db.
                $wpdb->update($wpdb->users, ['user_activation_key' => $key], ['user_login' => $user_login]);
            }

            // Send email notification.
            $reset_password_mail = wpfep_get_option('reset_password_mail', 'wpfep_emails_notification', 'on');
            if ('off' != $reset_password_mail) {
                $this->email_reset_pass($user_login, $user_email, $key);
            }

            return true;
        }

        /**
         * Retrieves a user row based on password reset key and login.
         *
         * @uses $wpdb WordPress Database object
         *
         * @param string $key   Hash to validate sending user's password.
         * @param string $login The user login.
         *
         * @return object|bool User's database row on success, false for invalid keys
         */
        public function check_password_reset_key($key, $login)
        {
            global $wpdb;

            // keeping backward compatible.
            if (strlen($key) == 20) {
                $key = preg_replace('/[^a-z0-9]/i', '', $key);
            }

            if (empty($key) || !is_string($key)) {
                $this->login_errors[] = __('Invalid key', 'wp-front-end-profile');

                return false;
            }

            if (empty($login) || !is_string($login)) {
                $this->login_errors[] = __('Invalid Login', 'wp-front-end-profile');

                return false;
            }

            $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

            if (empty($user)) {
                $this->login_errors[] = __('Invalid key', 'wp-front-end-profile');

                return false;
            }

            return $user;
        }

        /**
         * Successful authenticate when enable email verification in registration.
         *
         * @param object $user     return user.
         * @param string $username return username.
         * @param string $password return password.
         *
         * @return object
         */
        public function successfully_authenticate($user, $username, $password)
        {
            if (!is_wp_error($user)) {
                if ($user->ID) {
                    $resend_link = add_query_arg('resend_activation', $user->ID, $this->get_login_url());
                    $error = new WP_Error();
                    $wpfep_user = new wpfep_User($user->ID);
                    if (!$wpfep_user->is_verified()) {
                        /* translators: %s: activation link */
                        $error->add('acitve_user', sprintf(__('<strong>Your account is not active.</strong><br>Please check your email for activation link. <br><a href="%s">Click here</a> to resend the activation link', 'wp-front-end-profile'), $resend_link));

                        return $error;
                    }
                }
            }

            return $user;
        }

        /**
         * Check in activation of user registration.
         *
         * @since 1.0.0
         */
        public function activation_user_registration()
        {
            if (!isset($_GET['wpfep_registration_activation']) && empty($_GET['wpfep_registration_activation'])) {
                return;
            }

            if (!isset($_GET['id']) && empty($_GET['id'])) {
                wpfep()->login->add_error(__('Activation URL is not valid', 'wp-front-end-profile'));

                return;
            }

            $user_id = intval($_GET['id']);
            $user = new wpfep_User($user_id);
            $wpfep_user_active = get_user_meta($user_id, '_wpfep_user_active', true);
            $wpfep_user_status = get_user_meta($user_id, 'wpfep_user_status', true);

            if (!$user) {
                wpfep()->login->add_error(__('Invalid User activation url', 'wp-front-end-profile'));

                return;
            }

            if ($user->is_verified()) {
                wpfep()->login->add_error(__('User already verified', 'wp-front-end-profile'));

                return;
            }

            $activation_key = sanitize_text_field(wp_unslash($_GET['wpfep_registration_activation']));

            if ($user->get_activation_key() != $activation_key) {
                wpfep()->login->add_error(__('Activation URL is not valid', 'wp-front-end-profile'));

                return;
            }

            $user->mark_verified();
            $user->remove_activation_key();

            $message = __('Your account has been verified', 'wp-front-end-profile');

            if ('approved' != $wpfep_user_status) {
                $message = __("Your account has been verified , but you can't login until manually approved your account by an administrator.", 'wp-front-end-profile');
            }

            wpfep()->login->add_message($message);

            // show activation message.
            add_filter('wp_login_errors', [$this, 'user_activation_message']);

            $password_info_email = isset($_GET['wpfep_password_info_email']) ? $_GET['wpfep_password_info_email'] : false;
            $the_user = get_user_by('id', $user_id);
            $user_email = $the_user->user_email;
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            if ($password_info_email) {
                global $wpdb, $wpfep_hasher;

                // Generate something random for a password reset key.
                $key = wp_generate_password(20, false);

                /** This action is documented in wp-login.php */
                add_action('retrieve_password_key', $the_user->user_login, $key);

                // Now insert the key, hashed, into the DB.
                if (empty($wpfep_hasher)) {
                    require_once ABSPATH.WPINC.'/class-phpass.php';
                    $wpfep_hasher = new PasswordHash(8, true);
                }
                $hashed = time().':'.$wpfep_hasher->HashPassword($key);
                $wpdb->update($wpdb->users, ['user_activation_key' => $hashed], ['user_login' => $the_user->user_login]);
                /* translators: %s: username value */
                $subject = sprintf(__('[%s] Your username and password info', 'wp-front-end-profile'), $blogname);
                /* translators: %s: username term */
                $message = sprintf(__('Username: %s', 'wp-front-end-profile'), $the_user->user_login)."\r\n\r\n";
                $message .= __('To set your password, visit the following address:', 'wp-front-end-profile')."\r\n\r\n";
                $message .= network_site_url("wp-login.php?action=rp&key=$key&login=".rawurlencode($the_user->user_login), 'login')."\r\n\r\n";
                $message .= wp_login_url()."\r\n";

                $subject = apply_filters('wpfep_password_info_mail_subject', $subject);
                $message = apply_filters('wpfep_password_info_mail_body', $message);
                $message = get_formatted_mail_body($message, $subject);

                wp_mail($user_email, $subject, $message);
            } else {
                /* translators: %s: blogname term */
                $subject = sprintf(__('[%s] Account has been activated', 'wp-front-end-profile'), $blogname);
                /* translators: %s: current user*/
                $message = sprintf(__('Hi %s,', 'wp-front-end-profile'), $the_user->user_login)."\r\n\r\n";
                $message .= __('Congrats! Your account has been verified. To login visit the following url:', 'wp-front-end-profile')."\r\n\r\n";
                $message .= wp_login_url()."\r\n\r\n";
                $message .= __('Thanks', 'wp-front-end-profile');

                $subject = apply_filters('wpfep_mail_after_confirmation_subject', $subject);
                $message = apply_filters('wpfep_mail_after_confirmation_body', $message);
                $message = get_formatted_mail_body($message, $subject);

                wp_mail($user_email, $subject, $message);
            }
            add_filter('redirect_canonical', '__return_false');
            do_action('wpfep_user_activated', $user_id);
        }

        /**
         * Shows activation message on success to wp-login.php.
         *
         * @since 1.0.0
         *
         * @return \WP_Error
         */
        public function user_activation_message()
        {
            return new WP_Error('user-activated', __('Your account has been verified', 'wp-front-end-profile'), 'message');
        }

        /**
         * Redirect user to page after log in.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function wp_login_page_redirect()
        {
            global $pagenow;

            if (!is_admin() && 'wp-login.php' == $pagenow && isset($_GET['action']) && 'register' == $_GET['action']) {
                $reg_page = get_permalink(wpfep_get_option('register_page', 'wpfep_pages'));
                wp_redirect($reg_page);
                exit;
            }
        }

        /**
         * Handles resetting the user's password.
         *
         * @param object $user     the user.
         * @param string $new_pass new password for the user in plaintext.
         *
         * @return void
         */
        public function reset_password($user, $new_pass)
        {
            do_action('password_reset', $user, $new_pass);
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            wp_set_password($new_pass, $user->ID);
            // User password change email to admin.
            $change_password_admin_mail = wpfep_get_option('change_password_admin_mail', 'wpfep_emails_notification', 'on');
            if ('on' == $change_password_admin_mail) {
                wp_password_change_notification($user);
            }
            // User password change email to admin.
            $message = $user->user_login.' Your password has been changed.';
            $subject = '['.$blogname.'] Password changed';
            $password_change_mail = wpfep_get_option('password_change_mail', 'wpfep_emails_notification', 'on');
            if ('on' == $password_change_mail) {
                wp_mail($user->user_email, $subject, $message);
            }
        }

        /**
         * Email reset password link.
         *
         * @param string $user_login user login.
         * @param string $user_email user email.
         * @param string $key        returns key.
         */
        public function email_reset_pass($user_login, $user_email, $key)
        {
            $reset_url = add_query_arg(
                [
                    'action' => 'rp',
                    'key'    => $key,
                    'login'  => urlencode($user_login),
                ],
                $this->get_login_url()
            );

            $message = __('Someone requested that the password be reset for the following account:', 'wp-front-end-profile')."\r\n\r\n";
            $message .= network_home_url('/')."\r\n\r\n";
            /* translators: %s: username term */
            $message .= sprintf(esc_html__('Username: %s', 'wp-front-end-profile'), $user_login)."\r\n\r\n";
            $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'wp-front-end-profile')."\r\n\r\n";
            $message .= esc_html_e('To reset your password, visit the following address:', 'wp-front-end-profile')."\r\n\r\n";
            $message .= ' '.$reset_url." \r\n";

            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            if (is_multisite()) {
                $blogname = $GLOBALS['current_site']->site_name;
            }
            /* translators: %s: blogname term */
            $title = sprintf(esc_html__('[%s] Password Reset', 'wp-front-end-profile'), $blogname);
            $title = apply_filters('retrieve_password_title', $title);

            $message = apply_filters('retrieve_password_message', $message, $key, $user_login);

            if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
                wp_die(esc_html_e('The e-mail could not be sent.', 'wp-front-end-profile')."<br />\n".esc_html_e('Possible reason: your host may have disabled the mail() function.', 'wp-front-end-profile'));
            }
        }

        /**
         * Add info message.
         *
         * @param array $message return login error message.
         *
         * @since 1.0.0
         */
        public function add_error($message)
        {
            $this->login_errors[] = $message;
        }

        /**
         * Add info message.
         *
         * @param array $message return login error message.
         *
         * @since 1.0.0
         */
        public function add_message($message)
        {
            $this->messages[] = $message;
        }

        /**
         * Show errors on the form.
         *
         * @return void
         */
        public function show_errors()
        {
            if ($this->login_errors) {
                foreach ($this->login_errors as $error) {
                    echo '<div class="wpfep-error">';
                    echo wp_kses(
                        $error,
                        [
                            'input' => [
                                'type'  => [],
                                'class' => [],
                                'id'    => [],
                                'name'  => [],
                                'value' => [],
                            ],
                            'p'     => [],
                            'a'     => [
                                'target' => [],
                                'href'   => [],
                            ],

                        ]
                    );
                    echo '</div>';
                }
            }
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
