<?php
/**
 * function wpfep_register_shortcode()
 * register the a shortcode to display on front end
 */
function wpfep_register_shortcode() {
	return wpfep_show_profile();
}

add_shortcode( 'wpfep', 'wpfep_register_shortcode' );