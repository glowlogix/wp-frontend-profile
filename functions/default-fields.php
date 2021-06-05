<?php

/**
 * Custom fiedls.
 */
defined('ABSPATH') || exit;

/**
 * Function wpfep_add_profile_tab_meta_fields()
 * adds the default WordPress profile fields (major ones) to the profile tab.
 *
 * @param (array) $fields are the current array of fields added to this filter.
 *
 * @return (array) $fields are the modified array of fields to pass back to the filter
 */
function wpfep_add_profile_tab_meta_fields($fields)
{
    $fields[] = [
        'id'      => 'user_email',
        'label'   => __('Email Address', 'wp-front-end-profile'),
        'desc'    => __('Edit your email address - used for resetting your password etc.', 'wp-front-end-profile'),
        'type'    => 'email',
        'classes' => 'user_email',
        'disabled' => false,
    ];

    $fields[] = [
        'id'      => 'first_name',
        'label'   => __('First Name', 'wp-front-end-profile'),
        'desc'    => __('Edit your first name.', 'wp-front-end-profile'),
        'type'    => 'text',
        'classes' => 'first_name',
        'disabled' => false,
    ];

    $fields[] = [
        'id'      => 'last_name',
        'label'   => __('Last Name', 'wp-front-end-profile'),
        'desc'    => __('Edit your last name.', 'wp-front-end-profile'),
        'type'    => 'text',
        'classes' => 'last_name',
        'disabled' => false,
    ];

    $fields[] = [
        'id'      => 'user_url',
        'label'   => __('URL', 'wp-front-end-profile'),
        'desc'    => __('Edit your profile associated URL.', 'wp-front-end-profile'),
        'type'    => 'text',
        'classes' => 'user_url',
        'disabled' => false,
    ];

    $fields[] = [
        'id'      => 'description',
        'label'   => __('Description/Bio', 'wp-front-end-profile'),
        'desc'    => __('Edit your description/bio.', 'wp-front-end-profile'),
        'type'    => 'textarea',
        'classes' => 'wysiwyg',
        'disabled' => false,
    ];

    return $fields;
}
add_filter('wpfep_fields_profile', 'wpfep_add_profile_tab_meta_fields', 10);

/**
 * Function wpfeb_disable_email_for_admins()
 * Removes the email field when the current user is an admin.
 *
 * @param (array) $fields are the current array of fields added to this filter.
 *
 * @return (array) $fields are the modified array of fields to pass back to the filter
 */
function wpfep_disable_email_for_admins($fields, $userid)
{
    $user = new WP_User($userid);
    if ($user->has_cap('manage_options')) {
        foreach ($fields as $i => $field) {
            if ($field['id'] == 'user_email') {
                unset($fields[$i]);
            }
        }
    }

    return $fields;
}
add_filter('wpfep_fields_profile', 'wpfep_disable_email_for_admins', 20, 2);


/**
 * Wpfep_add_password_tab_fields()
 * adds the password update fields to the passwords tab.
 *
 * @param (array) $fields are the current array of fields added to this filter.
 *
 * @return (array) $fields are the modified array of fields to pass back to the filter
 */
function wpfep_add_password_tab_fields($fields)
{
    $fields[] = [
        'id'      => 'user_pass',
        'label'   => __('Password', 'wp-front-end-profile'),
        'desc'    => __('New Password', 'wp-front-end-profile'),
        'type'    => 'password',
        'classes' => 'user_pass',
    ];

    return $fields;
}
add_filter('wpfep_fields_password', 'wpfep_add_password_tab_fields', 10);
