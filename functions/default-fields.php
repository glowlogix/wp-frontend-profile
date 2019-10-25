<?php
/**
 * Custom fiedls.
 *
 * @package WP Frontend Profile
 */

/**
 *
 * Function wpfep_add_profile_tab_meta_fields()
 * adds the default WordPress profile fields (major ones) to the profile tab
 *
 * @param (array) $fields are the current array of fields added to this filter.
 * @return (array) $fields are the modified array of fields to pass back to the filter
 */
function wpfep_add_profile_tab_meta_fields( $fields ) {

	$fields[] = array(
		'id'      => 'user_email',
		'label'   => __( 'Email Address', 'wpfep' ),
		'desc'    => __( 'Edit your email address - used for resetting your password etc.', 'wpfep' ),
		'type'    => 'email',
		'classes' => 'user_email',
	);

	$fields[] = array(
		'id'      => 'first_name',
		'label'   => __( 'First Name', 'wpfep' ),
		'desc'    => __( 'Edit your first name.', 'wpfep' ),
		'type'    => 'text',
		'classes' => 'first_name',
	);

	$fields[] = array(
		'id'      => 'last_name',
		'label'   => __( 'Last Name', 'wpfep' ),
		'desc'    => __( 'Edit your last name.', 'wpfep' ),
		'type'    => 'text',
		'classes' => 'last_name',
	);

	$fields[] = array(
		'id'      => 'user_url',
		'label'   => __( 'URL', 'wpfep' ),
		'desc'    => __( 'Edit your profile associated URL.', 'wpfep' ),
		'type'    => 'text',
		'classes' => 'user_url',
	);

	$fields[] = array(
		'id'      => 'description',
		'label'   => __( 'Description/Bio', 'wpfep' ),
		'desc'    => __( 'Edit your description/bio.', 'wpfep' ),
		'type'    => 'wysiwyg',
		'classes' => 'description',
	);

	return $fields;

}
add_filter( 'wpfep_fields_profile', 'wpfep_add_profile_tab_meta_fields', 10 );

/**
 * Wpfep_add_password_tab_fields()
 * adds the password update fields to the passwords tab
 *
 * @param (array) $fields are the current array of fields added to this filter.
 * @return (array) $fields are the modified array of fields to pass back to the filter
 */
function wpfep_add_password_tab_fields( $fields ) {
	$fields[] = array(
		'id'      => 'user_pass',
		'label'   => __( 'Password', 'wpfep' ),
		'desc'    => __( 'New Password', 'wpfep' ),
		'type'    => 'password',
		'classes' => 'user_pass',
	);
	return $fields;
}
add_filter( 'wpfep_fields_password', 'wpfep_add_password_tab_fields', 10 );
