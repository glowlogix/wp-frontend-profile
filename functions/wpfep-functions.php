<?php
/**
 * function wpfep_tab_list_item()
 * generates the list item for a tab heading (the actual tab!)
 * @param (array) $tab the tab array
 */
function wpfep_tab_list_item( $tab ) {
	
	/* build the tab class */
	$tab_class = 'tab';
	
	/* if we have a tab class to add */
	if( $tab[ 'tab_class' ] != '' ) {
		
		/* add the tab class to our variable */
		$tab_class .= ' ' . $tab[ 'tab_class' ];
		
	}

	?>
	<li class="<?php echo esc_attr( $tab_class ); ?>">
		<a href="#<?php echo esc_attr( $tab[ 'id' ] ); ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a>
	</li>
	<?php
	
}

/**
 * function wpfep_default_tab_content()
 * outputs the fields for a tab inside a tab
 * this function is only used if a specific callback is not declared when filtering wpfep_tabs
 * @param (array) $tab is the array of tab args
 */
function wpfep_default_tab_content( $tab ) {

	/**
	 * @hook wpfep_before_tab_fields
	 * fires before the fields of the tab are outputted
	 * @param (array) $tab the array of tab args.
	 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
	 */
	do_action( 'wpfep_before_tab_fields', $tab, get_current_user_id() );
	
	/**
	 * build an array of fields to output
	 * @hook - wpfep_profile_fields
	 * each field should added with as an arrray with the following elements
	 * id - used for the input name and id attributes - should also be the user meta key
	 * label - used for the inputs label
	 * desc - the description to go with the input
	 * type - the type of input to render - valid are email, text, select, checkbox, textarea, wysiwyg
	 * @param (integer) current user id - this can be used to add fields to certain users only
	*/
	$fields = apply_filters(
		'wpfep_fields_' . $tab[ 'id' ],
		array(),
		get_current_user_ID()
	);
	
	/* check we have some fields */
	if( ! empty( $fields ) ) {
		
		/* output a wrapper div and form opener */
		?>
		
			<div class="wpfep-fields">
				
				<?php
					
					/* start a counter */
					$counter = 1;
					
					/* get the total number of fields in the array */
					$total_fields = count( $fields );
			
					/* lets loop through our fields array */
					foreach( $fields as $field ) {
						
						/* set a base counting class */
						$count_class = ' wpfep-' . $field[ 'type' ] . '-field wpfep-field-' . $counter;
						
						/* build our counter class - check if the counter is 1 */
						if( $counter == 1 ) {
							
							/* this is the first field element */
							$counting_class = $count_class . ' first';
						
						/* is the counter equal to the total number of fields */
						} elseif( $counter == $total_fields ) {
							
							/* this is the last field element */
							$counting_class = $count_class . ' last';
						
						/* if not first or last */
						} else {
							
							/* set to base count class only */
							$counting_class = $count_class;
						}
						
						/* build a var for classes to add to the wrapper */
						$classes = ( empty( $field[ 'classes' ] ) ) ? '' : ' ' . $field[ 'classes' ];
						
						/* build ful classe array */
						$classes = $counting_class  . $classes;
						
						/* output the field */
						wpfep_field( $field, $classes, $tab[ 'id' ], get_current_user_id() );
							
						/* increment the counter */
						$counter++;
					
					} // end for each field
					
					/* output a closing wrapper div */
				?>
			
			</div>
		
		<?php
	
	} // end if have fields
	
	/**
	 * @hook wpfep_after_tab_fields
	 * fires after the fields of the tab are outputted
	 * @param (array) $tab the array of tab args.
	 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
	 */
	do_action( 'wpfep_after_tab_fields', $tab, get_current_user_id() );
	
}

/**
 * function wpfep_field()
 * outputs the an input field
 * @param (array) $field the array of field data including id, label, desc and type
 * @return markup for the field input depending on type set in $field
 */
function wpfep_field( $field, $classes, $tab_id, $user_id ) {
		
	?>
	
	<div class="wpfep-field<?php echo esc_attr( $classes ); ?>" id="wpfep-field-<?php echo esc_attr( $field[ 'id' ] ); ?>">
				
		<?php
			
			/* get the reserved meta ids */
			$reserved_ids = apply_filters(
				'wpfep_reserved_ids',
				array(
					'user_email',
					'user_url'
				)
			);
			
			/* if the current field id is in the reserved list */
			if( in_array( $field[ 'id' ], $reserved_ids ) ) {
				
				$userdata = get_userdata( $user_id );
				$current_field_value = $userdata->{$field[ 'id' ]};
			
			/* not a reserved id - treat normally */
			} else {
				
				/* get the current value */
				$current_field_value = get_user_meta( get_current_user_id(), $field[ 'id' ], true );
				
			}
			
			/* output the input label */
			?>
			<label for="<?php echo esc_attr( $tab_id ); ?>[<?php echo esc_attr( $field[ 'id' ] ); ?>]"><?php echo esc_html( $field[ 'label' ] ); ?></label>
			<?php
								
			/* being a switch statement to alter the output depending on type */
			switch( $field[ 'type' ] ) {
				
				/* if this is a wysiwyg setting */
				case 'wysiwyg':
						
					/* set some settings args for the editor */
			    	$editor_settings = array(
			    		'textarea_rows' => apply_filters( 'wpfep_wysiwyg_textarea_rows', '5', $field[ 'id' ] ),
			    		'media_buttons' => apply_filters( 'wpfep_wysiwyg_media_buttons', false, $field[ 'id' ] ),
			    	);
			    	
			    	/* buld field name */
			    	$wysiwyg_name = $field[ 'id' ];
			    						    	
			    	/* display the wysiwyg editor */
			    	wp_editor(
			    		$current_field_value, // default content
			    		$wysiwyg_name, // id to give the editor element
			    		$editor_settings // edit settings from above
			    	);
				
					break;
				
				/* if this should be rendered as a select input */
				case 'select':
											
					?>
			    	<select name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo $field[ 'id' ]; ?>">
			    	
			    	<?php
			    	/* get the setting options */
			    	$options = $field[ 'options' ];
			    	
			        /* loop through each option */
			        foreach( $options as $option ) {
				        ?>
						<option value="<?php echo esc_attr( $option[ 'value' ] ); ?>" <?php selected( $current_field_value, $option[ 'value' ] ); ?>><?php echo esc_html( $option[ 'name' ] ); ?></option>
						<?php
			        }
			        ?>
			    	</select>
			        <?php
					
					break;
				
				/* if the type is set to a textarea input */  
			    case 'textarea':
			    	
			    	?>

					<textarea name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" rows="<?php echo apply_filters( 'wpfep_textarea_rows', '5', $field[ 'id' ] ); ?>" cols="50" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" class="regular-text"><?php echo esc_textarea( $current_field_value ); ?></textarea>
			        
			        <?php
				        
			        /* break out of the switch statement */
			        break;
				
				/* if the type is set to a textarea input */
			    case 'checkbox':
			    
			    	?>
			    	<input type="hidden" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" value="0" <?php checked( $current_field_value, '0' ); ?> />
					<input type="checkbox" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" value="1" <?php checked( $current_field_value, '1' ); ?> />
					<?php
			    	
			    	/* break out of the switch statement */
			        break;
			       
			    /* if the type is set to a textarea input */  
			    case 'email':
			    
			    	?>
					<input type="email" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" class="regular-text" value="<?php echo esc_attr( $current_field_value ); ?>" />

					<?php
			    	
			    	/* break out of the switch statement */
			        break;
			       
			    /* if the type is set to a textarea input */  
			    case 'password':
			    
			    	?>
					<input type="password" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" class="regular-text" value="" placeholder="New Password" />
					
					<input type="password" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>_check]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>_check" class="regular-text" value="" placeholder="Repeat New Password" />

					<?php
			    	
			    	/* break out of the switch statement */
			        break;
				
				/* any other type of input - treat as text input */ 
				default:
				
					?>
					<input type="text" name="<?php echo esc_attr( $tab_id ); ?>[<?php echo $field[ 'id' ]; ?>]" id="<?php echo esc_attr( $field[ 'id' ] ); ?>" class="regular-text" value="<?php echo esc_attr( $current_field_value ); ?>" />
					<?php	
				
			}
			
			/* if we have a description lets output it */
			if( $field[ 'desc' ] ) {
				
				?>
				<p class="description"><?php echo esc_html( $field[ 'desc' ] ); ?></p>
				<?php
				
			} // end if have description
		
		?>
		
	</div>
	
	<?php
	
}

/**
 * function wpfep_tab_content_save
 */
function wpfep_tab_content_save( $tab, $user_id ) {
	
	$profile_page = new WPFEP_Profile();
	$profile_page_obj = $profile_page->get_profile_url();
	?>
	<div class="wpfep-save">
		<label class="wpfep_save_description">Save this tabs updated fields.</label>
		<input type="submit" class="wpfep_save" name="<?php echo esc_attr( $tab[ 'id' ] ); ?>[wpfep_save]" value="Update <?php echo esc_attr( $tab[ 'label' ] ); ?>" />
		<a class="btn" href="<?php echo $profile_page_obj;?>"><?php echo __('View Profile', 'wpptm');?></a>
	</div>
	
	<?php
	
}

add_action( 'wpfep_after_tab_fields', 'wpfep_tab_content_save', 10, 2 );

/**
 * Displays a multi select dropdown for a settings field
 *
 * @param array   $args settings field args
 */

function wpfep_settings_multiselect( $args ) {

        $settings = new WPFEP_Settings_API();
        $value = $settings->get_option( $args['id'], $args['section'], $args['std'] );
        $value = is_array($value) ? (array)$value : array();
        $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html  = sprintf( '<select multiple="multiple" class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

        foreach ( $args['options'] as $key => $label ) {
            $checked = in_array($key, $value) ? $key : '0';
            $html   .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $checked, $key, false ), $label );
        }

        $html .= sprintf( '</select>' );
        $html .= $settings->get_field_description( $args );

        echo $html;
}

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @return string HTML content, if not displaying.
 */
function wpfep_get_pages( $post_type = 'page' ) {
    global $wpdb;

    $array = array( '' => __( '-- select --', 'wpptm' ) );
    $pages = get_posts( array('post_type' => $post_type, 'numberposts' => -1) );
    if ( $pages ) {
        foreach ($pages as $page) {
            $array[$page->ID] = esc_attr( $page->post_title );
        }
    }

    return $array;
}

/**
 * Include a template file
 *
 * Looks up first on the theme directory, if not found
 * lods from plugins folder
 *
 * @since 1.0.0
 *
 * @param string $file file name or path to file
 */
function wpfep_load_template( $file, $args = array() ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }

    $child_theme_dir 	= get_stylesheet_directory() . '/wpfep/';
    $parent_theme_dir 	= get_template_directory() . '/wpfep/';
    $wpfep_dir 			= plugin_dir_path( __DIR__ ) . 'views/';

    if ( file_exists( $child_theme_dir . $file ) ) {

        include $child_theme_dir . $file;

    } else if ( file_exists( $parent_theme_dir . $file ) ) {

        include $parent_theme_dir . $file;

    } else {
        include $wpfep_dir . $file;
    }
}
/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function wpfep_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}

/**
 * Encryption function for various usage
 *
 * @since 1.0.0
 *
 * @param  string  $id
 *
 * @return string $encoded_id
 */
function wpfep_encryption ( $id ) {

    $secret_key     = AUTH_KEY;
    $secret_iv      = AUTH_SALT;

    $encrypt_method = "AES-256-CBC";
    $key            = hash( 'sha256', $secret_key );
    $iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    $encoded_id     = base64_encode( openssl_encrypt( $id, $encrypt_method, $key, 0, $iv ) );

    return $encoded_id;
}

/**
 * Decryption function for various usage
 *
 * @since 1.0.0
 *
 * @param  string  $id
 *
 * @return string $encoded_id
 */
function wpfep_decryption ( $id ) {

    $secret_key     = AUTH_KEY;
    $secret_iv      = AUTH_SALT;

    $encrypt_method = "AES-256-CBC";
    $key            = hash( 'sha256', $secret_key );
    $iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    $decoded_id     = openssl_decrypt( base64_decode( $id ), $encrypt_method, $key, 0, $iv );

    return $decoded_id;
}

/**
     * Get a posted value for showing in the form field
     *
     * @param string $key
     * @return string
*/
function get_post_value( $key ) {
    if ( isset( $_POST[$key] ) ) {
        return esc_attr( $_POST[$key] );
    }
    return '';
}

//REVIEW ASK
function Wpfep_Hide_Review_Ask(){   
    $Ask_Review_Date = sanitize_text_field($_POST['Ask_Review_Date']);

    if (get_option('wpfep_Ask_Review_Date') < time()+3600*24*$Ask_Review_Date) {
    	update_option('wpfep_Ask_Review_Date', time()+3600*24*$Ask_Review_Date);
    }

    die();
}
add_action('wp_ajax_wpfep_hide_review_ask','Wpfep_Hide_Review_Ask');

//feeback mail
function Wpfep_Send_Feedback() {   
	$headers   = 'Content-type: text/html;charset=utf-8' . "\r\n";  
    $Feedback  = 'Feedback: <br>'.sanitize_text_field($_POST['Feedback']);
    $Feedback .= '<br /><br /> site url: <a href='.site_url().'>'.site_url().'</a>';
    $Feedback .= '<br />Email Address: ';
    $Feedback .= sanitize_text_field($_POST['EmailAddress']);

    wp_mail('support@glowlogix.com', 'WP Frontend Profile Plugin Feedback', $Feedback, $headers);

    die();
}
add_action('wp_ajax_wpfep_send_feedback','Wpfep_Send_Feedback');

/**
 * wpfep_let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 1.0.0
 * @param $size
 * @return int
 */
function wpfep_let_to_num( $size ) {
    $l   = substr( $size, -1 );
    $ret = substr( $size, 0, -1 );
    switch ( strtoupper( $l ) ) {
        case 'P':
            $ret *= 1024;
        case 'T':
            $ret *= 1024;
        case 'G':
            $ret *= 1024;
        case 'M':
            $ret *= 1024;
        case 'K':
            $ret *= 1024;
    }
    return $ret;
}


function wpfep_format_decimal($number, $dp = false, $trim_zeros = false){
    $locale   = localeconv();
    $decimals = array( wpfep_get_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

    // Remove locale from string.
    if ( ! is_float( $number ) ) {
        $number = str_replace( $decimals, '.', $number );
        $number = preg_replace( '/[^0-9\.,-]/', '', wpfep_clean( $number ) );
    }

    if ( false !== $dp ) {
        $dp     = intval( '' == $dp ? wpfep_get_decimal_separator() : $dp );
        $number = number_format( floatval( $number ), $dp, '.', '' );
        // DP is false - don't use number format, just return a string in our format
    } elseif ( is_float( $number ) ) {
        // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
        $number     = str_replace( $decimals, '.', sprintf( '%.' . wpfep_get_rounding_precision() . 'f', $number ) );
        // We already had a float, so trailing zeros are not needed.
        $trim_zeros = true;
    }

    if ( $trim_zeros && strstr( $number, '.' ) ) {
        $number = rtrim( rtrim( $number, '0' ), '.' );
    }

    return $number;
}

/**
 * Return the decimal separator.
 * @since  1.0.0
 * @return string
 */
function wpfep_get_decimal_separator() {
    $separator = apply_filters( 'wpfep_decimal_separator', '.' );
    return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Get rounding precision for internal calculations.
 * Will increase the precision of wpfep_get_decimal_separator by 2 decimals, unless WPFEP_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 1.0.0
 * @return int
 */
function wpfep_get_rounding_precision() {
    $precision = wpfep_get_decimal_separator() + 2;
    if ( absint( WPFEP_ROUNDING_PRECISION ) > $precision ) {
        $precision = absint( WPFEP_ROUNDING_PRECISION );
    }
    return $precision;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function wpfep_clean( $var ) {

    if ( is_array( $var ) ) {
        return array_map( 'wpfep_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }

}