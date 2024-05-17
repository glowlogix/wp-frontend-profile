<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://glowlogix.com/
 * @since      1.0.0
 * @package wp-front-end-profile
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete all pages and settings when plugin is uninstalled.
 */
function wpfep_delete_options()
{
    $wpfep_uninstall = get_option('wpfep_general');

    // Check if $wpfep_uninstall is an array
    if (is_array($wpfep_uninstall) && 'on' == $wpfep_uninstall['wpfep_remove_data_on_uninstall']) {
        // Delete Pages.
        $wpfep_options = get_option('wpfep_profile');

        // Check if $wpfep_options is an array before accessing its indexes
        if (is_array($wpfep_options)) {
            if (isset($wpfep_options['login_page'])) {
                wp_delete_post($wpfep_options['login_page'], true);
            }
            if (isset($wpfep_options['register_page'])) {
                wp_delete_post($wpfep_options['register_page'], true);
            }
            if (isset($wpfep_options['edit_page'])) {
                wp_delete_post($wpfep_options['edit_page'], true);
            }
            if (isset($wpfep_options['profile_page'])) {
                wp_delete_post($wpfep_options['profile_page'], true);
            }
        }

        // Delete Options.
        delete_option('_wpfep_page_created');
        delete_option('wpfep_general');
        delete_option('wpfep_profile');
        delete_option('wpfep_pages');
        delete_option('wpfep_uninstall');
        delete_option('wpfep_Install_Time');
        delete_option('wpfep_Ask_Review_Date');
    }
}
wpfep_delete_options();
