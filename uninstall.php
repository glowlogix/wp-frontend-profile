<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://glowlogix.com/
 * @since      1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
/**
 * The code that runs during plugin delete.
 * This action is documented in includes/class-gl-tables-activator.php
 */
function wpfep_delete_options() {
	delete_option( '_wpfep_page_created' );
	delete_option( 'wpfep_general' );
	delete_option( 'wpfep_profile' );
}
wpfep_delete_options();

