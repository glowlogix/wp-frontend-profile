<?php
/**
 * Functions file.
 */
defined('ABSPATH') || exit;

/**
 * Function wpfep_tab_list_item()
 * generates the list item for a tab heading (the actual tab!).
 *
 * @param (array) $tab the tab array.
 */
function wpfep_tab_list_item($tab)
{

    /* build the tab class */
    $tab_class = 'tab';

    /* if we have a tab class to add */
    if (' ' !== $tab['tab_class']) {

        /* add the tab class to our variable */
        $tab_class .= ' '.$tab['tab_class'];
    } ?>
	<li class="<?php echo esc_attr($tab_class); ?>">
		<a href="#<?php echo esc_attr($tab['id']); ?>"><?php echo esc_html($tab['label']); ?></a>
	</li>
	<?php
}

/**
 * Function wpfep_default_tab_content()
 * outputs the fields for a tab inside a tab
 * this function is only used if a specific callback is not declared when filtering wpfep_tabs.
 *
 * @param (array) $tab is the array of tab args.
 */
function wpfep_default_tab_content($tab)
{

    /**
     * Function wpfep_default_tab_content.
     *
     * @hook wpfep_before_tab_fields
     * fires before the fields of the tab are outputted
     *
     * @param (array) $tab             the array of tab args.
     * @param (int)   $current_user_id the user if of the current user to add things targeted to a specific user only.
     */
    do_action('wpfep_before_tab_fields', $tab, get_current_user_id());

    /**
     * Build an array of fields to output.
     *
     * @hook - wpfep_profile_fields
     * each field should added with as an array with the following elements
     * id - used for the input name and id attributes - should also be the user meta key
     * label - used for the inputs label
     * desc - the description to go with the input
     * type - the type of input to render - valid are email, text, select, checkbox, textarea, wysiwyg
     *
     * @param (integer) current user id - this can be used to add fields to certain users only
     */
    $fields = apply_filters(
        'wpfep_fields_'.$tab['id'],
        [],
        get_current_user_ID()
    );

    /* check we have some fields */
    if (!empty($fields)) {

        /* output a wrapper div and form opener */ ?>

			<div class="wpfep-fields">

				<?php

                    /* start a counter */
                    $counter = 1;

        /* get the total number of fields in the array */
        $total_fields = count($fields);

        /* lets loop through our fields array */
        foreach ($fields as $field) {

                        /* set a base counting class */
            $count_class = ' wpfep-'.$field['type'].'-field wpfep-field-'.$counter;

            /* build our counter class - check if the counter is 1 */
            if (1 === $counter) {

                            /* this is the first field element */
                $counting_class = $count_class.' first';

            /* is the counter equal to the total number of fields */
            } elseif ($counter === $total_fields) {

                            /* this is the last field element */
                $counting_class = $count_class.' last';

            /* if not first or last */
            } else {

                            /* set to base count class only */
                $counting_class = $count_class;
            }

            /* build a var for classes to add to the wrapper */
            $classes = (empty($field['classes'])) ? '' : ' '.$field['classes'];

            /* build ful classes array */
            $classes = $counting_class.$classes;

            /* output the field */
            wpfep_field($field, $classes, $tab['id'], get_current_user_id());

            /* increment the counter */
            $counter++;
        } // end for each field

                    /* output a closing wrapper div */
                ?>

			</div>

		<?php
    } // end if have fields.

    /**
     * Wpfep_after_tab_fields.
     *
     * @hook wpfep_after_tab_fields
     * fires after the fields of the tab are outputted
     *
     * @param (array) $tab             the array of tab args.
     * @param (int)   $current_user_id the user if of the current user to add things targeted to a specific user only.
     */
    do_action('wpfep_after_tab_fields', $tab, get_current_user_id());
}

/**
 * Function wpfep_field_get_options()
 * retrieves an array of valid options for a field.
 *
 * @param (array) $field the array of field data including id, label, desc and type.
 *
 * @return array list of options
 */
function wpfep_field_get_options($field)
{
    if ($field['taxonomy']) {
        $terms = get_terms($field['taxonomy'], ['hide_empty' => false]);
        $options = [];
        foreach ($terms as $term) {
            $options[] = ['value' => $term->slug, 'name' => $term->name];
        }

        return $options;
    }

    return $field['options'];
}

/**
 * Function wpfep_field()
 * outputs the an input field.
 *
 * @param (array) $field   the array of field data including id, label, desc and type.
 * @param (array) $classes the array of field classes.
 * @param (int)   $tab_id  is the id of tab.
 * @param (int)   $user_id is the id of user.
 *
 * @return void markup for the field input depending on type set in $field
 */
function wpfep_field($field, $classes, $tab_id, $user_id)
{
    ?>

	<div class="wpfep-field<?php echo esc_attr($classes); ?>" id="wpfep-field-<?php echo esc_attr($field['id']); ?>">

		<?php

            /* get the reserved meta ids */
            $reserved_ids = apply_filters(
                'wpfep_reserved_ids',
                [
                    'user_email',
                    'user_url',
                ]
            );

    /* if the current field id is in the reserved list */
    if (in_array($field['id'], $reserved_ids)) {
        $userdata = get_userdata($user_id);
        $current_field_value = $userdata->{$field['id']};
    /* not a reserved id, but is a taxonomy */
    } elseif (isset($field['taxonomy'])) {
        $terms = wp_get_object_terms($user_id, $field['taxonomy']);
        $current_field_value = [];
        foreach ($terms as $term) {
            if ($field['type'] == 'checkboxes' || $field['type'] == 'select multiple') {
                $current_field_value[] = $term->slug;
            } else {
                $current_field_value = $term->slug;
            }
        }
        /* not a reserved id - treat normally */
    } else {
        /* get the current value */
        $current_field_value = get_user_meta(get_current_user_id(), $field['id'], true);
    }
    /* output the input label */ ?>
		<label for="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]"><?php echo esc_html($field['label']); ?></label>
			<?php

            /* being a switch statement to alter the output depending on type */
            switch ($field['type']) {

                /* if this is a wysiwyg setting */
                case 'wysiwyg':
                    /* set some settings args for the editor */
                    $editor_settings = [
                        'textarea_rows' => apply_filters('wpfep_wysiwyg_textarea_rows', '5', $field['id']),
                        'media_buttons' => apply_filters('wpfep_wysiwyg_media_buttons', false, $field['id']),
                    ];

                    /* build field name. */
                    $wysiwyg_name = $field['id'];

                    /* display the wysiwyg editor */
                    wp_editor(
                        $current_field_value, // default content.
                        $wysiwyg_name, // id to give the editor element.
                        $editor_settings // edit settings from above.
                    );

                    break;

                /* if this should be rendered as a select input */
                case 'select':
                    ?>
					<select name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>">

					<?php
                    $options = wpfep_field_get_options($field);

                    /* loop through each option */
                    foreach ($options as $option) {
                        ?>
						<option value="<?php echo esc_attr($option['value']); ?>" <?php selected($current_field_value, $option['value']); ?>><?php echo esc_html($option['name']); ?></option>
						<?php
                    }
                    ?>
					</select>
					<?php
                    break;

                /* if this should be rendered as a select input */
                case 'select multiple':
                    ?>
					<select multiple name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>][]" id="<?php echo esc_attr($field['id']); ?>">
					<option>-</option>
					<?php
                    $options = wpfep_field_get_options($field);

                    /* loop through each option */
                    foreach ($options as $option) {
                        ?>
						<option value="<?php echo esc_attr($option['value']); ?>" <?php selected(true, in_array($option['value'], $current_field_value)); ?>><?php echo esc_html($option['name']); ?></option>
						<?php
                    }
                    ?>
					</select>
					<?php

                    break;

                /* if this should be rendered as a set of radio buttons */
                case 'radio':
                    $options = wpfep_field_get_options($field);

                    /* loop through each option */
                    foreach ($options as $option) {
                        ?>
						<div class="radio-wrapper"><label><input type="radio" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" value="<?php echo esc_attr($option['value']); ?>"  <?php checked($current_field_value, $option['value']); ?>> <?php echo esc_html($option['name']); ?></label></div>
						<?php
                    }
                    ?>
					<?php

                    break;

                /* if the type is set to a textarea input */
                case 'textarea':
                    ?>

					<textarea name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" rows="<?php echo absint(apply_filters('wpfep_textarea_rows', '5', $field['id'])); ?>" cols="50" id="<?php echo esc_attr($field['id']); ?>" class="regular-text"><?php echo esc_textarea($current_field_value); ?></textarea>

					<?php

                    /* break out of the switch statement */
                    break;

                /* if the type is set to a checkbox */
                case 'checkbox':
	                $options = wpfep_field_get_options($field);
                    ?>
					<input type="hidden" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" value="0" <?php checked($current_field_value, '0'); ?> />
                <?php
                    /* loop through each option */
	                foreach ($options as $option) {
	                    ?>

                    <input type="checkbox" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']);
                    ?>]" id="<?php echo esc_attr($field['id']); ?>" value="<?php echo $option['value']?>" <?php checked
                    ($current_field_value, $option['value']); ?> />
					<?php
                        echo $option['name'];
                    }

                    /* break out of the switch statement */
                    break;

                /* if this should be rendered as a set of radio buttons */
                case 'checkboxes':
                    ?>
					<input type="hidden" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>][]" value="-" />
					<?php
                    $options = wpfep_field_get_options($field);

                    /* loop through each option */
                    foreach ($options as $option) {
                        ?>
						<div class="checkbox-wrapper"><label><input type="checkbox" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>][]" value="<?php echo esc_attr($option['value']); ?>" <?php checked(true, in_array($option['value'], $current_field_value)); ?>> <?php echo esc_html($option['name']); ?></label></div>
						<?php
                    }
                    ?>
					<?php

                    break;

                /* if the type is set to an email input */
                case 'email':
                    ?>
					<input type="email" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" class="regular-text" value="<?php echo esc_attr($current_field_value); ?>" />
					<?php
                    /* break out of the switch statement */
                    break;

                /* if the type is set to a password input */
                case 'password':
                    ?>
					<input type="password" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" class="regular-text" value="" placeholder="New Password" />

					<input type="password" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>_check]" id="<?php echo esc_attr($field['id']); ?>_check" class="regular-text" value="" placeholder="Repeat New Password" />

					<?php

                    /* break out of the switch statement */
                    break;
                /* any other type of input - treat as text input */
                default:
                    ?>
					<input type="text" name="<?php echo esc_attr($tab_id); ?>[<?php echo esc_attr($field['id']); ?>]" id="<?php echo esc_attr($field['id']); ?>" class="regular-text" value="<?php echo esc_attr($current_field_value); ?>" />
					<?php

            }

    /* if we have a description lets output it */
    if ($field['desc']) {
        ?>
				<p class="description"><?php echo esc_html($field['desc']); ?></p>
				<?php
    } // end if have description

            ?>
	</div>

	<?php
}

/**
 * Function wpfep_tab_content_save.
 *
 * @param (string) $tab     return tab.
 * @param (int)    $user_id returns user id.
 */
function wpfep_tab_content_save($tab, $user_id)
{
    $profile_page = new WPFEP_Profile();
    $profile_page_obj = $profile_page->get_profile_url(); ?>
	<div class="wpfep-save">
		<label class="wpfep_save_description"><?php echo esc_html__('Save this tabs updated fields.', 'wp-front-end-profile'); ?></label>
		<input type="submit" class="wpfep_save" name="<?php echo esc_attr($tab['id']); ?>[wpfep_save]" value="Update <?php echo esc_attr($tab['label']); ?>" />
		<a class="btn" href="<?php echo esc_attr($profile_page_obj); ?>"><?php echo esc_html__('View Profile', 'wp-front-end-profile'); ?></a>
	</div>
	<?php
}

add_action('wpfep_after_tab_fields', 'wpfep_tab_content_save', 10, 2);

/**
 * Displays a multi select dropdown for a settings field.
 *
 * @param array $args settings field args.
 */
function wpfep_settings_multiselect($args)
{
    $settings = new WPFEP_Settings_API();
    $value = $settings->get_option($args['id'], $args['section'], $args['std']);
    $value = is_array($value) ? (array) $value : [];
    $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
    $html = sprintf('<select multiple="multiple" class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);

    foreach ($args['options'] as $key => $label) {
        $checked = in_array($key, $value) ? $key : '0';
        $html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($checked, $key, false), $label);
    }

    $html .= sprintf('</select>');
    $html .= $settings->get_field_description($args);

    echo wp_kses(
        $html,
        [
            'select' => [
                'multiple' => [],
                'class'    => [],
                'name'     => [],
                'id'       => [],
            ],
            'option' => [
                'value'    => [],
                'selected' => [],
            ],
            'p'      => [],
        ]
    );
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @param (string) $post_type returns post type.
 *
 * @return string HTML content, if not displaying.
 */
function wpfep_get_pages($post_type = 'page')
{
    global $wpdb;

    $array = ['' => __('-- select --', 'wp-front-end-profile')];
    $pages = get_posts(
        [
            'post_type'   => $post_type,
            'numberposts' => -1,
        ]
    );
    if ($pages) {
        foreach ($pages as $page) {
            $array[$page->ID] = esc_attr($page->post_title);
        }
    }

    return $array;
}

/**
 * Include a template file.
 *
 * Looks up first on the theme directory, if not found.
 * loads from plugins folder
 *
 * @since 1.0.0
 *
 * @param (string) $file file name or path to file.
 * @param (array)  $args set array.
 */
function wpfep_load_template($file, $args = [])
{
    $child_theme_dir = get_stylesheet_directory().'/wpfep/';
    $parent_theme_dir = get_template_directory().'/wpfep/';
    $wpfep_dir = plugin_dir_path(__DIR__).'views/';

    if (file_exists($child_theme_dir.$file)) {
        include $child_theme_dir.$file;
    } elseif (file_exists($parent_theme_dir.$file)) {
        include $parent_theme_dir.$file;
    } else {
        include $wpfep_dir.$file;
    }
}
/**
 * Get the value of a settings field.
 *
 * @param string $option  settings field name.
 * @param string $section the section name this field belongs to.
 * @param string $default default text if it's not found.
 *
 * @return mixed
 */
function wpfep_get_option($option, $section, $default = '')
{
    $options = get_option($section);

    if (isset($options[$option])) {
        return $options[$option];
    }

    return $default;
}

/**
 * Encryption function for various usage.
 *
 * @since 1.0.0
 *
 * @param string $id return id.
 *
 * @return string $encoded_id
 */
function wpfep_encryption($id)
{
    $secret_key = AUTH_KEY;
    $secret_iv = AUTH_SALT;

    $encrypt_method = 'AES-256-CBC';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $encoded_id = base64_encode(openssl_encrypt($id, $encrypt_method, $key, 0, $iv));

    return $encoded_id;
}

/**
 * Decryption function for various usage.
 *
 * @since 1.0.0
 *
 * @param string $id return id.
 *
 * @return string $encoded_id
 */
function wpfep_decryption($id)
{
    $secret_key = AUTH_KEY;
    $secret_iv = AUTH_SALT;

    $encrypt_method = 'AES-256-CBC';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $decoded_id = openssl_decrypt(base64_decode($id), $encrypt_method, $key, 0, $iv);

    return $decoded_id;
}

/**
 * Get a posted value for showing in the form field.
 */
function wpfep_hide_review_ask()
{
    if (isset($_POST['_wpnonce'])) {
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpfep_feedback_action');
    }
    $ask_review_date = isset($_POST['Ask_Review_Date']) ? sanitize_text_field(wp_unslash($_POST['Ask_Review_Date'])) : '';
    if (get_option('wpfep_Ask_Review_Date') < time() + 3600 * 24 * $ask_review_date) {
        update_option('wpfep_Ask_Review_Date', time() + 3600 * 24 * $ask_review_date);
    }
    die();
}
add_action('wp_ajax_wpfep_hide_review_ask', 'wpfep_hide_review_ask');

/**
 * Send feedback of client.
 */
function wpfep_send_feedback()
{
    if (isset($_POST['_wpnonce'])) {
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpfep_feedback_action');
    }
    $headers = 'Content-type: text/html;charset=utf-8'."\r\n";
    $feedback = 'Feedback: <br>';
    $feedback .= isset($_POST['Feedback']) ? sanitize_text_field(wp_unslash($_POST['Feedback'])) : '';
    $feedback .= '<br /><br /> site url: <a href='.site_url().'>'.site_url().'</a>';
    $feedback .= '<br />Email Address: ';
    $feedback .= isset($_POST['EmailAddress']) ? sanitize_text_field(wp_unslash($_POST['EmailAddress'])) : '';
    wp_mail('support@glowlogix.com', 'WP Frontend Profile Plugin Feedback', $feedback, $headers);
    die();
}
add_action('wp_ajax_wpfep_send_feedback', 'wpfep_send_feedback');

/**
 * Wpfep_let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 1.0.0
 *
 * @param object $size for size.
 *
 * @return int
 */
function wpfep_let_to_num($size)
{
    $l = substr($size, -1);
    $ret = substr($size, 0, -1);
    switch (strtoupper($l)) {
        case 'P':
            $ret *= 1024;
            // no break.
        case 'T':
            $ret *= 1024;
            // no break.
        case 'G':
            $ret *= 1024;
            // no break.
        case 'M':
            $ret *= 1024;
            // no break.
        case 'K':
            $ret *= 1024;
            // no break.
    }

    return $ret;
}

/**
 * * wpfep_format_decimal.
 *
 * @param object $number     return user Wpfep_format_decimal.
 * @param bool   $dp         decimal point.
 * @param bool   $trim_zeros removes zero.
 */
function wpfep_format_decimal($number, $dp = false, $trim_zeros = false)
{
    $locale = localeconv();
    $decimals = [wpfep_get_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point']];

    // Remove locale from string.
    if (!is_float($number)) {
        $number = str_replace($decimals, '.', $number);
        $number = preg_replace('/[^0-9\.,-]/', '', wpfep_clean($number));
    }

    if (false !== $dp) {
        $dp = intval('' === $dp ? wpfep_get_decimal_separator() : $dp);
        $number = number_format(floatval($number), $dp, '.', '');
    // DP is false - don't use number format, just return a string in our format.
    } elseif (is_float($number)) {
        // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
        $number = str_replace($decimals, '.', sprintf('%.'.wpfep_get_rounding_precision().'f', $number));
        // We already had a float, so trailing zeros are not needed.
        $trim_zeros = true;
    }

    if ($trim_zeros && strstr($number, '.')) {
        $number = rtrim(rtrim($number, '0'), '.');
    }

    return $number;
}

/**
 * Return the decimal separator.
 *
 * @since  1.0.0
 *
 * @return string
 */
function wpfep_get_decimal_separator()
{
    $separator = apply_filters('wpfep_decimal_separator', '.');

    return $separator ? stripslashes($separator) : '.';
}

/**
 * Get rounding precision for internal calculations.
 * Will increase the precision of wpfep_get_decimal_separator by 2 decimals, unless WPFEP_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 1.0.0
 *
 * @return int
 */
function wpfep_get_rounding_precision()
{
    $precision = wpfep_get_decimal_separator() + 2;
    if (absint(WPFEP_ROUNDING_PRECISION) > $precision) {
        $precision = absint(WPFEP_ROUNDING_PRECISION);
    }

    return $precision;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param object $var return user.
 *
 * @return string|array
 */
function wpfep_clean($var)
{
    if (is_array($var)) {
        return array_map('wpfep_clean', $var);
    } else {
        return is_scalar($var) ? sanitize_text_field($var) : $var;
    }
}
/**
 * Function wp_frontend_profile_output().
 *
 * Provides the frontend output for the frontend profile editing
 */
function wpfep_show_profile()
{

    /* First things first - if no are not logged in move on! */
    if (!is_user_logged_in()) {
        echo "<div class='wpfep-login-alert'>";
        /* translators: %s: Login link */
        printf(esc_html__('This page is restricted. Please %s to view this page.', 'wp-front-end-profile'), wp_loginout('', false));
        echo '</div>';

        return;
    }
    $user = wp_get_current_user();
    if (in_array('administrator', (array) $user->roles, true)) {
        if (current_user_can('manage_options')) {
            ob_start();
        }
        echo "<div class='wpfep_editing_disabled'>";
        printf(esc_html__('Frontend editing is disabled for administrators because of security risks.', 'wp-front-end-profile'));
        echo '</div>';

        return ob_get_clean();
    }
    /* if you're an admin - too risky to allow frontend editing */ ?>
	<div class="wpfep-wrapper">
		<?php
            /* get the tabs that have been added - see below */
            $wpfep_tabs = apply_filters(
                'wpfep_tabs',
                []
            );

    /**
     * Hook before tab content.
     *
     * @hook wpfep_before_tabs
     * fires before the tabs list items are outputted
     *
     * @param (array) $tabs            is all the tabs that have been added
     * @param (int)   $current_user_id the user if of the current user to add things targeted to a specific user only.
     */
    do_action('wpfep_before_tabs', $wpfep_tabs, get_current_user_id()); ?>
		<ul class="wpfep-tabs" id="wpfep-tabs">
			<?php
                /**
                 * Set an array of tab titles and ids
                 * the id set here should match the id given to the content wrapper
                 * which has the class tab-content included in the callback function.
                 *
                 * @hooked wpfep_add_profile_tab - 10
                 * @hooked wpfep_add_password_tab - 20
                 */
                $wpfep_tabs = apply_filters(
                    'wpfep_tabs',
                    []
                );
    /* check we have items to show */
    if (!empty($wpfep_tabs)) {
        /* loop through each item */
        foreach ($wpfep_tabs as $wpfep_tab) {
            /* output the tab name as a tab */
            wpfep_tab_list_item($wpfep_tab);
        }
    } ?>
		</ul><!-- // wpfep-tabs -->
		<?php
            global $wp;
    /* loop through each item */
    foreach ($wpfep_tabs as $wpfep_tab) {

            /* build the content class */
        $content_class = '';

        /* if we have a class provided */
        if ('' != $wpfep_tab['content_class']) {
            /* add the content class to our variable */
            $content_class .= ' '.$wpfep_tab['content_class'];
        }

        /**
         * Hook before tab content.
         *
         * @hook wpfep_before_tab_content
         * fires before the contents of the tab are outputted
         *
         * @param (string) $tab_id          the id of the tab being displayed. This can be used to target a particular tab.
         * @param (int)    $current_user_id the user if of the current user to add things targeted to a specific user only.
         */
        do_action('wpfep_before_tab_content', $wpfep_tab['id'], get_current_user_id()); ?>

			<div class="tab-content<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($wpfep_tab['id']); ?>">
				<form method="post" action="<?php echo esc_attr(get_edit_profile_page()).'#'.esc_attr($wpfep_tab['id']); ?>" class="wpfep-form-<?php echo esc_attr($wpfep_tab['id']); ?>">
					<?php
                        /* check if callback function exists */
                    if (isset($wpfep_tab['callback']) && function_exists($wpfep_tab['callback'])) {
                        /* use custom callback function */
                        $wpfep_tab['callback']($wpfep_tab);
                    } else {
                        /* use default callback function */
                        wpfep_default_tab_content($wpfep_tab);
                    } ?>
											
					<?php
                        wp_nonce_field(
                            'wpfep_nonce_action',
                            'wpfep_nonce_name'
                        ); ?>
				</form>
			</div>
				<?php
                /**
                 * Get current user id.
                 *
                 * @hook wpfep_after_tab_content
                 * fires after the contents of the tab are outputted
                 *
                 * @param (string) $tab_id          the id of the tab being displayed. This can be used to target a particular tab.
                 * @param (int)    $current_user_id the user if of the current user to add things targeted to a specific user only.
                 */
                do_action('wpfep_after_tab_content', $wpfep_tab['id'], get_current_user_id());
    } // end tabs loop
        ?>
	</div><!-- // wpfep-wrapper -->
	<?php
}

/**
 * Get edit profile page url.
 *
 * @return bool|string
 */
function get_edit_profile_page()
{
    $page_id = wpfep_get_option('profile_edit_page', 'wpfep_pages', false);

    if (!$page_id) {
        return false;
    }

    $url = get_permalink($page_id);

    return apply_filters('wpfep_profile_edit_url', $url, $page_id);
}

$manually_approve_user = wpfep_get_option('admin_manually_approve', 'wpfep_profile', 'on');
if ('on' == $manually_approve_user) {
    /**
     * Add the approve or deny link where appropriate.
     *
     * @uses user_row_actions
     *
     * @param array  $actions returns the action.
     * @param object $user    returns the user.
     *
     * @return array
     */
    function user_table_actions($actions, $user)
    {
        if (get_current_user_id() == $user->ID) {
            return $actions;
        }
        if (is_super_admin($user->ID)) {
            return $actions;
        }
        $user_status = get_user_meta($user->ID, 'wpfep_user_status', true);
        $approve_link = add_query_arg(
            [
                'action' => 'approve',
                'user'   => $user->ID,
            ]
        );
        $approve_link = remove_query_arg(['new_role'], $approve_link);
        $approve_link = wp_nonce_url($approve_link, 'new-user-approve');
        $reject_link = add_query_arg(
            [
                'action' => 'rejected',
                'user'   => $user->ID,
            ]
        );
        $reject_link = remove_query_arg(['new_role'], $reject_link);
        $reject_link = wp_nonce_url($reject_link, 'new-user-approve');
        $approve_action = '<a href="'.esc_url($approve_link).'">'.__('Approve', 'wp-front-end-profile').'</a>';
        $deny_action = '<a href="'.esc_url($reject_link).'">'.__('Rejected', 'wp-front-end-profile').'</a>';
        if ('pending' == $user_status) {
            $actions[] = $approve_action;
        } elseif ('approve' == $user_status) {
            $actions[] = $deny_action;
        } elseif ('rejected' == $user_status) {
            $actions[] = $approve_action;
        }

        return $actions;
    }
    add_filter('user_row_actions', 'user_table_actions', 10, 2);
    /**
     * Add the status column to the user table.
     *
     * @uses manage_users_columns
     *
     * @param array $columns returns columns.
     *
     * @return array
     */
    function add_column($columns)
    {
        $the_columns['wpfep_user_status'] = __('Status', 'wp-front-end-profile');
        $newcol = array_slice($columns, 0, -1);
        $newcol = array_merge($newcol, $the_columns);
        $columns = array_merge($newcol, array_slice($columns, 1));

        return $columns;
    }
    add_filter('manage_users_columns', 'add_column');
    /**
     * Show the status of the user in the status column.
     *
     * @uses manage_users_custom_column
     *
     * @param string $val_column  return column value.
     * @param string $column_name return column value.
     * @param int    $user        returns user data.
     *
     * @return string
     */
    function status_column($val_column, $column_name, $user)
    {
        $status='';
        switch ($column_name) {
            case 'wpfep_user_status':
                $user_status = get_user_meta($user, 'wpfep_user_status', true);
                if ('approve' == $user_status) {
                    $status = __('Approved', 'wp-front-end-profile');
                } elseif ('pending' == $user_status) {
                    $status = __('pending', 'wp-front-end-profile');
                } elseif ('rejected' == $user_status) {
                    $status = __('Rejected', 'wp-front-end-profile');
                }

                return $status;
            break;
            default:
        }

        return $val_column;
    }
    add_filter('manage_users_custom_column', 'status_column', 10, 3);
    /**
     * Update the user status if the approved or rejected link was clicked.
     *
     * @uses load-users.php
     */
    function update_action()
    {
        if (!empty($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '' && in_array(sanitize_text_field(wp_unslash($_GET['action'])), ['approve', 'rejected']) && !empty($_GET['new_role'] ? sanitize_text_field(wp_unslash($_GET['new_role'])) : '')) {
            $request = sanitize_text_field(wp_unslash($_GET['action']));
            $request_id = intval($_GET['user']);
            $user_data = get_userdata($request_id);
            if ('approve' == $request) {
                update_user_meta($request_id, 'wpfep_user_status', $request);
                $subject = 'Approval notification';
                $message = 'Your account is approved by admin.'."\r\n\r\n";
                $message .= 'Now you can log in to your account.'."\r\n\r\n";
                $message .= 'Thank you'."\r\n\r\n";
                wp_mail($user_data->user_email, $subject, $message);
            }
            if ('rejected' == $request) {
                update_user_meta($request_id, 'wpfep_user_status', $request);
                $subject = 'Denied notification';
                $message = 'Your account is denied by admin.'."\r\n\r\n";
                $message .= 'Now you cannot Log In to your account.'."\r\n\r\n";
                $message .= 'Thank you'."\r\n\r\n";
                wp_mail($user_data->user_email, $subject, $message);
            }
        }
    }
    add_action('load-users.php', 'update_action');
}
