<?php
/**
Plugin Name: WP Frontend Profile
Plugin URI: https://wordpress.org/plugins/wp-front-end-profile/
Description: This plugin allows users to easily edit their profile information on the frontend rather than having to go into the dashboard to make changes to password, email address and other user meta data.
Version:     1.0.0
Author:      Glowlogix
Author URI:  https://www.glowlogix.com
Text Domain: wpfep
License:     GPL v2 or later
 *
 * @package WP Frontend Profile
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for WP Frontend Profile
 *
 * @package WP Frontend Profile
 */
if ( ! defined( 'WPFEP_VERSION' ) ) {
	define( 'WPFEP_VERSION', '1.0.0' );
}
if ( ! defined( 'WPFEP_PATH' ) ) {
	define( 'WPFEP_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPFEP_PLUGIN_URL' ) ) {
	define( 'WPFEP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once WPFEP_PATH . '/inc/class-wp-frontend-profile.php';



/**
 * Add links to plugin's description in plugins table
 *
 * @since 1.0.0
 *
 * @param array  $links  Initial list of links.
 * @param string $file   Basename of current plugin.
 *
 * @return array
 */
function plugin_meta_links( $links, $file ) {
	if ( plugin_basename( __FILE__ ) != $file ) {
		return $links;
	}
	$support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/" title="' . __( 'Get help', 'wpfep' ) . '">' . __( 'Support', 'wpfep' ) . '</a>';
	$rate_link    = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/reviews/#new-post" title="' . __( 'Rate the plugin', 'wpfep' ) . '">' . __( 'Rate the plugin ★★★★★', 'wpfep' ) . '</a>';

	$links[] = $support_link;
	$links[] = $rate_link;
	return $links;
}
		add_filter( 'plugin_row_meta', 'plugin_meta_links', 10, 5 );


/**
 * Plugin action links.
 *
 * @param array $links setting page links.
 *
 * @since  1.0.0
 */
function plugin_action_links( $links ) {

	$mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=wpfep-settings' ) . '">Settings</a>',
	);
	return array_merge( $links, $mylinks );
}
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'plugin_action_links' );
