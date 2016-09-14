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
				$current_field_value = $userdata->$field[ 'id' ];
			
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
			    	$wysiwyg_name = $tab_id . '[' . $field[ 'id' ] . ']';
			    						    	
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
	
	?>
	
	<div class="wpfep-save">
		<label class="wpfep_save_description">Save this tabs updated fields.</label>
		<input type="submit" class="wpfep_save" name="<?php echo esc_attr( $tab[ 'id' ] ); ?>[wpfep_save]" value="Update <?php echo esc_attr( $tab[ 'label' ] ); ?>" />
	</div>
	
	<?php
	
}

add_action( 'wpfep_after_tab_fields', 'wpfep_tab_content_save', 10, 2 );