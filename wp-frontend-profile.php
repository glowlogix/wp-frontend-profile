<?php

/**
 * Plugin Name: WP Frontend Profile
 * Plugin URI: https://wordpress.org/plugins/wp-front-end-profile/
 * Description: This plugin allows users to easily edit their profile information on the frontend rather than having to go into the dashboard to make changes to password, email address and other user meta data.
 * Version:     1.2.4
 * Author:      Glowlogix
 * Author URI:  https://www.glowlogix.com
 * Text Domain: wp-front-end-profile
 * License:     GPL v2 or later.
 */
defined('ABSPATH') || exit;

/**
 * Main class for WP Frontend Profile.
 */
if (!defined('WPFEP_VERSION')) {
    define('WPFEP_VERSION', '1.2.4');
}
if (!defined('WPFEP_PATH')) {
    define('WPFEP_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPFEP_PLUGIN_URL')) {
    define('WPFEP_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once WPFEP_PATH . '/inc/class-wp-frontend-profile.php';

if (!function_exists('wfep_fs')) {
    // Create a helper function for easy SDK access.
    function wfep_fs()
    {
        global $wfep_fs;

        if (!isset($wfep_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wfep_fs = fs_dynamic_init(array(
                'id'                  => '5837',
                'slug'                => 'wp-front-end-profile',
                'premium_slug'        => 'wp-frontend-profile-premium',
                'type'                => 'plugin',
                'public_key'          => 'pk_ac83abfabd6c3c1498e82893f4a23',
                'is_premium'          => true,
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => true,
                'has_paid_plans'      => true,
                'trial'               => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'                => array(
                    'slug'           => 'wpfep-settings_dashboard',
                    'support'        => false,
                ),

            ));
        }

        return $wfep_fs;
    }

    // Init Freemius.
    wfep_fs();
    // Signal that SDK was initiated.
    do_action('wfep_fs_loaded');
}

/**
 * Add links to plugin's description in plugins table.
 *
 * @since 1.0.0
 *
 * @param array  $links Initial list of links.
 * @param string $file  Basename of current plugin.
 *
 * @return array
 */
function plugin_meta_links($links, $file)
{
    if (plugin_basename(__FILE__) != $file) {
        return $links;
    }
    $support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/" title="' . __('Get help', 'wp-front-end-profile') . '">' . __('Support', 'wp-front-end-profile') . '</a>';
    $rate_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/reviews/#new-post" title="' . __('Rate the plugin', 'wp-front-end-profile') . '">' . __('Rate the plugin ★★★★★', 'wp-front-end-profile') . '</a>';

    $links[] = $support_link;
    $links[] = $rate_link;

    return $links;
}
add_filter('plugin_row_meta', 'plugin_meta_links', 10, 5);

/**
 * Plugin action links.
 *
 * @param array $links setting page links.
 *
 * @since  1.0.0
 */
function plugin_action_links($links)
{
    $mylinks = [
        '<a href="' . admin_url('admin.php?page=wpfep-settings') . '">Settings</a>',
    ];

    return array_merge($links, $mylinks);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_action_links');
