<?php
/**
 * Login Class.
 */
defined('ABSPATH') || exit;

if (!class_exists('WPFEP_User')) {
    /**
     * The User Class.
     *
     * @since 1.0.0
     */
    class WPFEP_User
    {
        /**
         * User ID.
         *
         * @var int
         */
        public $id;

        /**
         * User Object.
         *
         * @var \WP_User return user.
         */
        public $user;

        /**
         * The constructor.
         *
         * @param int|WP_User $user return user.
         */
        public function __construct($user)
        {
            if (is_numeric($user)) {
                $the_user = get_user_by('id', $user);
                if ($the_user) {
                    $this->id = $the_user->ID;
                    $this->user = $the_user;
                }
            } elseif (is_a($user, 'WP_User')) {
                $this->id = $user->ID;
                $this->user = $user;
            }
        }

        /**
         * Check if user is verified.
         *
         * @since 1.0.0
         *
         * @return bool
         */
        public function is_verified()
        {
            if (!metadata_exists('user', $this->id, '_wpfep_user_active')) {
                return true;
            }
            if (intval(get_user_meta($this->id, '_wpfep_user_active', true)) == 1) {
                return true;
            }

            return false;
        }

        /**
         * Mark user as verified.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function mark_verified()
        {
            update_user_meta($this->id, '_wpfep_user_active', 1);
        }

        /**
         * Mark user as unverified.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function mark_unverified()
        {
            update_user_meta($this->id, '_wpfep_user_active', 0);
        }

        /**
         * Set user activation key.
         *
         * @since 1.0.0
         *
         * @param string $key returns key.
         *
         * @return void
         */
        public function set_activation_key($key)
        {
            update_user_meta($this->id, '_wpfep_activation_key', $key);
        }

        /**
         * Get user activation key.
         *
         * @since 1.0.0
         *
         * @return string
         */
        public function get_activation_key()
        {
            return get_user_meta($this->id, '_wpfep_activation_key', true);
        }

        /**
         * Remove user activation key.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function remove_activation_key()
        {
            delete_user_meta($this->id, '_wpfep_activation_key');
        }

        /**
         * Send user confirmation E-mail.
         *
         * @since 1.0.0
         *
         * @param int $user return user data.
         *
         * @return void
         */
        public function wpfep_new_user($user)
        {
            $userdata = get_userdata($user);

            $manually_approve_user = wpfep_get_option('admin_manually_approve', 'wpfep_profile', 'on');

            if ('on' == $manually_approve_user) {
                add_user_meta($user, 'wpfep_user_status', 'pending');
            } else {
                add_user_meta($user, 'wpfep_user_status', 'approve');
            }

            if ($user && !is_wp_error($user)) {
                $code = sha1($user.time());

                $register_page = wpfep_get_option('login_page', 'wpfep_pages');

                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                $register_admin_mail = wpfep_get_option('new_account_admin_mail', 'wpfep_emails_notification', 'on');

                $activation_link = add_query_arg(
                    [
                        'key'  => $code,
                        'user' => $user,
                    ],
                    get_permalink($register_page)
                );

                update_user_meta($user, 'has_to_be_activated', $code, true);
                /* translators: %s: user email */
                $message = sprintf(__('Congrats! You are Successfully registered to: %s'), $blogname).'<br><br>';

                $message .= __('Click on the link to activate your account').'<br><br>';

                $message .= '<a href='."$activation_link".'>Click Here</a>';

                $headers = ['Content-Type: text/html; charset=UTF-8'];
                $user_behave = wpfep_get_option('user_behave', 'wpfep_profile');
                $option_enabled_for_email = wpfep_get_option('register_mail', 'wpfep_emails_notification', 'on');
                if ('activate_mail' == $user_behave && 'on' == $option_enabled_for_email) {
                    wp_mail($userdata->user_email, 'Email verification for account activation', $message, $headers);
                }
            }
        }

        /**
         * Manually approve users.
         *
         * @since 1.0.0
         *
         *  @param int $user return current user id.
         *
         * @return void
         */
        public function manually_approve($user)
        {
            $userdata = get_userdata($user);

            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            $subject = 'Registration E-mail';

            $message = sprintf(esc_attr('Congrats! You are successfully registered to: %s'), $blogname)."\r\n\r\n";

            $message .= 'Your account is not approved by admin.'."\r\n\r\n";

            $message .= 'We will send confirmation when it is approved.'."\r\n\r\n";
            if ('on' == $register_user_mail) {
                wp_mail($userdata->user_email, $subject, $message);
            }

            /* translators: %s: admin mail */
            $message_admin = sprintf(esc_html__('New user registration on your site %s:', 'wp-front-end-profile'), get_option('blogname'))."\r\n\r\n";
            /* translators: %s: user login */
            $message_admin .= sprintf(esc_html__('Username: %s', 'wp-front-end-profile'), $userdata->user_login)."\r\n\r\n";
            /* translators: %s: user email */
            $message_admin .= sprintf(esc_html__('E-mail: %s', 'wp-front-end-profile'), $userdata->user_email)."\r\n";
            /* translators: %s: user subject */
            $subject = esc_html__('New user registration', 'wp-front-end-profile');
            /* translators: %s: user email */
            if ('on' == $register_admin_mail) {
                wp_mail(get_option('admin_email'), sprintf(esc_html__('[%1$s] %2$s', 'wp-front-end-profile'), $blogname, $subject), $message_admin);
            }
        }
    }
}
