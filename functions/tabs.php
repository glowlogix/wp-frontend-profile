<?php
/**
 * function wpfep_add_profile_tab
 * adds the profile tab to the profile output
 * @param (array) current array of tabs in the filter
 * @return (array) the newly modified array of tabs
 */
function wpfep_add_profile_tab( $tabs ) {
	
	/* add our tab to the tabs array */
	$tabs[] = array(
		'id' => 'profile', // used for the callback function, if declared or exists and the tab content wrapper id
		'label' => 'Profile',
		'tab_class' => 'profile-tab',
		'content_class' => 'profile-content',
		/**
		 * (callback) this is used to display the tab output.
		 * if not declared or the function declared does not exist the default wpfep_default_tab_content() function is used instead.
		 */
		'callback' => 'wpfep_profile_tab_content'
	);
	
	/* return all the tabs */
	return $tabs;
	
}

add_filter( 'wpfep_tabs', 'wpfep_add_profile_tab', 10 );

/**
 * function wpfep_add_password_tab
 * adds the password tab to the profile output
 * @param (array) current array of tabs in the filter
 * @return (array) the newly modified array of tabs
 */
function wpfep_add_password_tab( $tabs ) {
	
	/* add our tab to the tabs array */
	$tabs[] = array(
		'id' => 'password',
		'label' => 'Password',
		'tab_class' => 'password-tab',
		'content_class' => 'password-content',
	);
	
	/* return all the tabs */
	return $tabs;
	
}

add_filter( 'wpfep_tabs', 'wpfep_add_password_tab', 20 );