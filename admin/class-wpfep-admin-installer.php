<?php
/**
 * @package wp-front-end-profile
 * Installation settings.
 */

defined('ABSPATH') || exit;

if (! class_exists('WPFEP_Admin_Installer')) {
    /**
     * Page installer.
     *
     * @since 1.0.0
     */
    class WPFEP_Admin_Installer
    {

        /**
         * Adding Actions.
         *
         * @return void
         */
        public function __construct()
        {
            add_action('admin_notices', array( $this, 'admin_notice' ));
            add_action('admin_init', array( $this, 'handle_request' ));
            add_filter('display_post_states', array( $this, 'add_post_states' ), 10, 2);
        }

        /**
         * Print admin notices.
         *
         * @return void
         */
        public function admin_notice()
        {
            $page_created = get_option('_wpfep_page_created'); ?>
			<?php
            if (false === $page_created) {
                ?>
				<div class="updated error updated_wpfep">
					<p>
						<?php esc_attr_e('WP Frontend Profile needs to create several pages (User Profile, Registration, Login, Profile Edit) to function correctly.', 'wpfep'); ?>
					</p>
					<p class="submit">
						<a class="button button-primary" href="<?php echo esc_url(add_query_arg(array( 'install_wpfep_pages' => true ), admin_url('admin.php?page=wpfep-settings'))); ?>"><?php esc_attr_e('Create Pages', 'wpfep'); ?></a>
						<?php esc_attr_e('or', 'wpfep'); ?>
						<a class="button" href="<?php echo esc_url(add_query_arg(array( 'wpfep_hide_page_nag' => true ))); ?>"><?php esc_attr_e('Skip Setup', 'wpfep'); ?></a>
					</p>
				</div>
				<?php
            }
            if (true === isset($_GET['wpfep_page_installed']) && sanitize_text_field(wp_unslash($_GET['wpfep_page_installed']))) {
                ?>
				<div class="updated wpfep_updated">
					<p>
						<strong><?php esc_attr_e('Congratulations!', 'wpfep'); ?></strong> 
						<?php
                        $page_success = 'Pages for 
<strong>WP Frontend Profile</strong> has been successfully installed and saved!';

                echo wp_kses(
                    $page_success,
                    array(
                                'p'      => array(),
                                'strong' => array(),
                            )
                ); ?>
					</p>
				</div>
				<?php
            }
        }

        /**
         * Handle the page creation button requests.
         *
         * @return void
         */
        public function handle_request()
        {
            if (true === isset($_GET['install_wpfep_pages']) && sanitize_text_field(wp_unslash($_GET['install_wpfep_pages']))) {
                $this->init_pages();
            }

            if (true === isset($_POST['install_wpfep_pages']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['install_wpfep_pages'])))) {
                $this->init_pages();
            }

            if (true === isset($_GET['wpfep_hide_page_nag']) && sanitize_text_field(wp_unslash($_GET['wpfep_hide_page_nag']))) {
                update_option('_wpfep_page_created', '1');
            }
        }

        /**
         * Initialize the plugin with some default page/settings.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function init_pages()
        {
            // create a Register page.
            $register_page = $this->create_page(__('Register', 'wpfep'), '[wpfep-register]');
            // edit Account.
            $edit_page = $this->create_page(__('Profile Edit', 'wpfep'), '[wpfep]');
            // login page.
            $login_page = $this->create_page(__('Login', 'wpfep'), '[wpfep-login]');
            // profile page.
            $profile_page = $this->create_page(__('Profile', 'wpfep'), '[wpfep-profile]');
            // profile pages.
            $profile_options = array();
            $reg_page        = false;

            if ($login_page) {
                $profile_options['login_page'] = $login_page;
            }
            if ($register_page) {
                $profile_options['register_page'] = $register_page;
            }
            if ($edit_page) {
                $profile_options['edit_page'] = $edit_page;
            }
            if ($profile_page) {
                $profile_options['profile_page'] = $profile_page;
            }

            $data = apply_filters('wpfep_pro_page_install', $profile_options);

            if (is_array($data)) {
                if (isset($data['profile_options'])) {
                    $profile_options = $data['profile_options'];
                }
                if (isset($data['reg_page'])) {
                    $reg_page = $data['reg_page'];
                }
            }

            update_option('wpfep_profile', $profile_options);
            update_option(
                'wpfep_pages',
                array(
                    'login_page'        => $login_page,
                    'register_page'     => $register_page,
                    'profile_edit_page' => $edit_page,
                    'profile_page'      => $profile_page,
                )
            );
            update_option('_wpfep_page_created', '1');

            $location      = 'admin.php?page=wpfep-settings&wpfep_page_installed=true';
            $status        = 302;
            $x_redirect_by = 'WordPress';

            wp_safe_redirect($location, $status, $x_redirect_by);
            exit;
        }

        /**
         * Create a page with title and content.
         *
         * @param string $page_title   page title.
         * @param string $post_content content of page.
         * @param string $post_type    check post type.
         *
         * @return false|int
         */
        public function create_page($page_title, $post_content = '', $post_type = 'page')
        {
            $page_id = wp_insert_post(
                array(
                    'post_title'     => $page_title,
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'comment_status' => 'closed',
                    'post_content'   => $post_content,
                )
            );

            if ($page_id && ! is_wp_error($page_id)) {
                return $page_id;
            }

            return false;
        }

        /**
         * Add a post display state for Frontend Profile pages in the page list table.
         *
         * @param array   $post_states An array of post display states.
         * @param WP_Post $post        The current post object.
         *
         * @return mixed
         */
        public function add_post_states($post_states, $post)
        {
            $wpfep_options = get_option('wpfep_pages');

            if (! empty($wpfep_options['login_page']) && $wpfep_options['login_page'] === $post->ID) {
                $post_states[] = __('WPFP Login Page', 'wpfep');
            }

            if (! empty($wpfep_options['register_page']) && $wpfep_options['register_page'] === $post->ID) {
                $post_states[] = __('WPFP Register Page', 'wpfep');
            }

            if (! empty($wpfep_options['profile_edit_page']) && $wpfep_options['profile_edit_page'] === $post->ID) {
                $post_states[] = __('WPFP Profile Edit Page', 'wpfep');
            }

            if (! empty($wpfep_options['profile_page']) && $wpfep_options['profile_page'] === $post->ID) {
                $post_states[] = __('WPFP Profile Page', 'wpfep');
            }

            return $post_states;
        }
    }
}
