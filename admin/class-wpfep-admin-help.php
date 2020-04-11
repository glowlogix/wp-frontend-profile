<?php
/**
 * Help menu in an plugin admin page.
 */
defined('ABSPATH') || exit;

if (!class_exists('WPFEP_Admin_Help')) {
    /**
     * WPFEP_Admin_Help Class.
     */
    class WPFEP_Admin_Help
    {
        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_action('current_screen', [$this, 'add_tabs'], 50);
        }

        /**
         * Singleton object.
         *
         * @staticvar boolean $instance
         *
         * @return \self
         */
        public static function init()
        {
            static $instance = false;

            if (!$instance) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Add help tabs.
         */
        public function add_tabs()
        {
            $screen = get_current_screen();
            if ('frontend-profile_page_wpfep-settings' === $screen->id || 'frontend-profile_page_wpfep-tools' === $screen->id || 'frontend-profile_page_wpfep-status' === $screen->id) {
                $screen->add_help_tab(
                    [
                        'id'      => 'wpfep_support_tab',
                        'title'   => __('Help &amp; Support', 'wp-front-end-profile'),
                        'content' => '<h2>'.__('Help &amp; Support', 'wp-front-end-profile').'</h2>'.
                            '<p>'.sprintf(
                                /* translators: %s: Documentation URL */
                                __('Should you need help understanding, using, or extending WP Frontend Profile, <a href="%s">please read our documentation</a>.', 'wp-front-end-profile'),
                                'https://github.com/glowlogix/wp-frontend-profile/wiki'
                            ).'</p>'.
                            '<p>'.sprintf(
                                /* translators: %s: Forum URL */
                                __('For further assistance with WP Frontend Profile core you can use the <a href="%1$s">community forum</a>. ', 'wp-front-end-profile'),
                                'https://wordpress.org/support/plugin/wp-front-end-profile/'
                            ).'</p>'.
                            '<p> <a href="https://wordpress.org/support/plugin/wp-front-end-profile/" class="button">'.__('Community forum', 'wp-front-end-profile').'</a> </p>',
                    ]
                );
                $screen->add_help_tab(
                    [
                        'id'      => 'wpfep_bugs_tab',
                        'title'   => __('Found a bug?', 'wp-front-end-profile'),
                        'content' => '<h2>'.__('Found a bug?', 'wp-front-end-profile').'</h2>'.
                            /* translators: 1: GitHub issues URL 2: GitHub contribution guide URL 3: System status report URL */
                            '<p>'.sprintf(__('If you find a bug within WP Frontend Profile core you can create a ticket via <a href="%1$s">Github issues</a>.', 'wp-front-end-profile'), 'https://github.com/glowlogix/wp-frontend-profile/issues?q=is%3Aopen').'</p>'.
                            '<p><a href="https://github.com/glowlogix/wp-frontend-profile/issues/new?assignees=&labels=bug&template=1-bug-report.md&title=" class="button button-primary">'.__('Report a bug', 'wp-front-end-profile').'</a> </p>',
                    ]
                );
            }
        }
    }
    WPFEP_Admin_Help::init();
}
