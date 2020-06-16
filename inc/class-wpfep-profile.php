<?php
/**
 * User Profile Class.
 */
defined('ABSPATH') || exit;

/**
 * User Profile handler class.
 */
if (!class_exists('WPFEP_Profile')) {
    /**
     * User Profile handler class.
     */
    class WPFEP_Profile
    {
        /**
         * Error array.
         *
         * @var array
         */
        private $login_errors = [];
        /**
         * Message array.
         *
         * @var array
         */
        private $messages = [];
        /**
         * Singleton object.
         *
         * @var array
         */
        private static $_instance;

        /**
         * Define template file.
         */
        public function __construct()
        {
            add_shortcode('wpfep-profile', [$this, 'user_profile']);
            add_action('wpfep_profile_pagination', [$this, 'get_profile_pagination']);
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
         * Shows the user profile.
         *
         * @return string
         */
        public function user_profile()
        {
            global $wp;

            $profile_page = wpfep_get_option('profile_page', 'wpfep_pages', false);

            ob_start();

            wpfep_load_template('profile.php', $profile_page);

            return ob_get_clean();
        }

        /**
         * Add Error message.
         *
         * @since 1.0.0
         *
         * @param array $message error messages.
         */
        public function add_error($message)
        {
            $this->login_errors[] = $message;
        }

        /**
         * Add info message.
         *
         * @since 1.0.0
         *
         * @param array $message error messages.
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
                    echo esc_html($error);
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

        /**
         * Prints the tab content pagination section.
         *
         * @since       1.0.0
         *
         * @param int $total Total items.
         *
         * @return void
         */
        public function get_profile_pagination($total)
        {
            ?>
			<div class="wpfep-pagination">
				<?php
                $big = 999999999; // need an unlikely integer.
                $translated = __('Page', 'wp-front-end-profile'); // Supply translatable string.

                $paginate_links = paginate_links(
                    [
                        'base'               => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format'             => '?paged=%#%',
                        'current'            => max(1, get_query_var('paged')),
                        'total'              => $total,
                        'before_page_number' => '<span class="screen-reader-text">'.$translated.' </span>',
                        'type'               => 'list',
                    ]
                );
            echo wp_kses(
                $paginate_links,
                [
                        'a' => [
                            'class' => [],
                            'href'  => [],
                        ],

                    ]
            ); ?>
			</div>
			<?php
        }

        /**
         * Get User Profile page url.
         *
         * @return bool|string
         */
        public function get_profile_url()
        {
            $page_id = wpfep_get_option('profile_page', 'wpfep_pages', false);

            if (!$page_id) {
                return false;
            }

            $url = get_permalink($page_id);

            return apply_filters('wpfep_profile_url', $url, $page_id);
        }
    }
}
