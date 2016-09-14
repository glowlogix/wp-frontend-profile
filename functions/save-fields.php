<?php
/**
 * function wpfep_save_fields()
 * saves the fields from a tab (except password tab) to user meta
 * @param (array) $tabs is an array of all of the current tabs
 * @param (int) $user_id is the current logged in users id
 */
function wpfep_save_fields( $tabs, $user_id ) {
	
	/* check the nonce */
	if( ! isset( $_POST[ 'wpfep_nonce_name' ] ) || ! wp_verify_nonce( $_POST[ 'wpfep_nonce_name' ], 'wpfep_nonce_action' ) )
		return;
	
	/* set an array to store messages in */
	$messages = array();
	
	/* get the POST data */
	$tabs_data = $_POST;
	
	/**
	 * remove the following array elements from the data
	 * password
	 * nonce name
	 * wp refere - sent with nonce
	 */
	unset( $tabs_data[ 'password' ] );
	unset( $tabs_data[ 'wpfep_nonce_name' ] );
	unset( $tabs_data[ '_wp_http_referer' ] );
	
	/* lets check we have some data to save */
	if( empty( $tabs_data ) )
		return;
		
	/**
	 * setup an array of reserved meta keys
	 * to process in a different way
	 * they are not meta data in wordpress
	 * reserved names are user_url and user_email as they are stored in the users table not user meta
	 */
	$reserved_ids = apply_filters(
		'wpfep_reserved_ids',
		array(
			'user_email',
			'user_url',
		)
	);

	/**
	 * set an array of registered fields
	 */
	$registered_fields = array();
	foreach( $tabs as $tab ) {
		$tab_fields = apply_filters(
			'wpfep_fields_' . $tab[ 'id' ],
			array(),
			$user_id
		);
		$registered_fields = array_merge( $registered_fields, $tab_fields );
	}

	/* set an array of registered keys */
	$registered_keys = wp_list_pluck( $registered_fields, 'id' );

	/* loop through the data array - each element of this will be a tabs data */
	foreach( $tabs_data as $tab_data ) {
		
		/**
		 * loop through this tabs array
		 * the ket here is the meta key to save to
		 * the value is the value we want to actually save
		 */
		foreach( $tab_data as $key => $value ) {
			
			/* if the key is the save sumbit - move to next in array */
			if( $key == 'wpfep_save' || $key == 'wpfep_nonce_action' )
				continue;

			/* if the key is not in our list of registered keys - move to next in array */
			if ( ! in_array( $key, $registered_keys ) )
				continue;

			/* check whether the key is reserved - handled with wp_update_user */
			if( in_array( $key, $reserved_ids ) ) {
				
				$user_id = wp_update_user(
					array(
						'ID' => $user_id,
						$key => $value
					)
				);
				
				/* check for errors */
				if ( is_wp_error( $user_id ) ) {
					
					/* update failed */
					$messages[ 'update_failed' ] = '<p class="error">There was a problem with updating your profile.</p>';
				
				}
			
			/* just standard user meta - handle with update_user_meta */
			} else {

				/* lookup field options by key */
				$registered_field_key = array_search( $key, array_column( $registered_fields, 'id' ) );

				/* sanitize user input based on field type */
				switch ( $registered_fields[$registered_field_key]['type'] ) {
					case 'wysiwyg':
						$value = wp_filter_post_kses( $value );
						break;
					case 'select':
						$value = sanitize_text_field( $value );
						break;
					case 'textarea':
						$value = wp_filter_nohtml_kses( $value );
						break;
					case 'checkbox':
						$value = isset( $value ) && '1' === $value ? true : false;
						break;
					case 'email':
						$value = sanitize_email( $value );
						break;
					default:
						$value = sanitize_text_field( $value );
				}

				/* update the user meta data */
				$meta = update_user_meta( $user_id, $key, $value );
				
				/* check the update was succesfull */
				if( $meta == false ) {
					
					/* update failed */
					$messages[ 'update_failed' ] = '<p class="error">There was a problem with updating your profile.</p>';
					
				}
				
			}
			
		} // end tab loop
		
	} // end data loop
	
	/* check if we have an messages to output */
	if( empty( $messages ) ) {
		
		?>
		<div class="messages">
		<?php
		
		/* lets loop through the messages stored */
		foreach( $messages as $message ) {
			
			/* output the message */
			echo $message;
			
		}
		
		?>
		</div><!-- // messages -->
		<?php
		
	} else {
		
		?>
		<div class="messages"><p class="updated">Your profile was updated successfully!</p></div>
		<?php
		
	}
	
}

add_action( 'wpfep_before_tabs', 'wpfep_save_fields', 5, 2 );

/**
 * function wpfep_save_password()
 * saves the change of password on the profile password tab
 * check for length (filterable with wpfep_password_length) and complexity (upper/lower/numbers)
 * user is logged out on success with a message to login back in with new password.
 *
 * @param (array) $tabs is an array of all of the current tabs
 * @param (int) $user_id is the current logged in users id
 */
function wpfep_save_password( $tabs, $user_id ) {
	
	/* set an array to store messages in */
	$messages = array();
	
	/* get the posted data from the password tab */
	$data = $_POST[ 'password' ];
	
	/* store both password for ease of access */
	$password = $data[ 'user_pass' ];
	$password_check = $data[ 'user_pass_check' ];
	
	/* first lets check we have a password added to save */
	if( empty( $password ) )
		return;
	
	/* now lets check the password match */
	if( $password != $password_check ) {
		
		/* add message indicating no match */
		$messages[ 'password_mismatch' ] = '<p class="error">Please make sure the passwords match.</p>';
		
	}
	
	/* get the length of the password entered */
	$pass_length = strlen( $password );
	
	/* check the password match the correct length */
	if( $pass_length < apply_filters( 'wpfep_password_length', 12 ) ) {
		
		/* add message indicating length issue!! */
		$messages[ 'password_length' ] = '<p class="error">Please make sure your password is a minimum of ' . apply_filters( 'wpfep_password_length', 12 ) . ' characters long.</p>';
		
	}
	
	/**
	 * match the password against a regex of complexity
	 * at least 1 upper, 1 lower case letter and 1 number
	 */
	$pass_complexity = preg_match( apply_filters( 'wpfep_password_regex', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d,.;:]).+$/' ), $password );
	
	/* check whether the password passed the regex check of complexity */
	if( $pass_complexity == false ) {
		
		/* add message indicating complexity issue */
		$messages[ 'password_complexity' ] = '<p class="error">Your password must contain at least 1 uppercase, 1 lowercase letter and at least 1 number</p>';
		
	}
	
	/* check we have any messages in the messages array - if we have password failed at some point */
	if( empty( $messages ) ) {
		
		/**
		 * ok if we get this far we have passed all the checks above
		 * the password can now be updated and redirect the user to the login page
		 */
		
		wp_set_password( $password, $user_id );
		echo '<div class="messages"><p class="updated">You\'re password was successfully changed and you have been logged out. Please <a href="' . esc_url( wp_login_url() ) . '">login again here</a>.</p></div>';
	
	/* messages not empty therefore password failed */
	} else {
		
		?>
		<div class="messages">
		<?php
		
		/* lets loop through the messages stored */
		foreach( $messages as $message ) {
			
			/* output the message */
			echo $message;
			
		}
		
		?>
		</div><!-- // messages -->
		<?php
		
	}
	
}

add_action( 'wpfep_before_tabs', 'wpfep_save_password', 10, 2 );