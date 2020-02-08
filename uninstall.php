<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://glowlogix.com/
 * @since      1.0.0
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete all pages and settings when plugin in uninstalled.
 */
function wpfep_delete_options()
{
    $wpfep_uninstall = get_option('wpfep_general');
    if ('on' == $wpfep_uninstall['wpfep_remove_data_on_uninstall']) {
        // Delete Pages.
        $wpfep_options = get_option('wpfep_profile');
        wp_delete_post($wpfep_options['login_page'], true);
        wp_delete_post($wpfep_options['register_page'], true);
        wp_delete_post($wpfep_options['edit_page'], true);
        wp_delete_post($wpfep_options['profile_page'], true);

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
