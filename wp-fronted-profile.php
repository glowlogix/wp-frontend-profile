<?php
/*
Plugin Name: WP Front End Profile
Description: This plugin allows users to easily edit their profile information on the front end rather than having to go into the dashboard to make changes to password, email address and other user meta data.
Version:     0.2.2
Author:      Mark Wilkinson
Author URI:  http://markwilkinson.me
Text Domain: wpptm
License:     GPL v2 or later
*/

/***************************************************************
* include the necessary functions files for the plugin
***************************************************************/
require_once dirname( __FILE__ ) . '/functions/scripts.php';
require_once dirname( __FILE__ ) . '/functions/default-fields.php';
require_once dirname( __FILE__ ) . '/functions/tabs.php';
require_once dirname( __FILE__ ) . '/functions/wpfep-functions.php';
require_once dirname( __FILE__ ) . '/functions/save-fields.php';
require_once dirname( __FILE__ ) . '/functions/shortcode.php';

/**
 * function wp_frontend_profile_output()
 *
 * provides the front end output for the front end profile editing
 */
function wpfep_show_profile() {
	
	/* first things first - if no are not logged in move on! */
	if( ! is_user_logged_in() )
		return;
	
	/* if you're an admin - too risky to allow front end editing */
	if( current_user_can( 'manage_options' ) )
		return;

	?>
	
	<div class="wpfep-wrapper">
		
		<?php
			
			/* get the tabs that have been added - see below */
			$wpfep_tabs = apply_filters(
				'wpfep_tabs',
				array()	
			);
			
			/**
			 * @hook wpfep_before_tabs
			 * fires before the tabs list items are outputted
			 * @param (array) $tabs is all the tabs that have been added
			 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
			 */
			do_action( 'wpfep_before_tabs', $wpfep_tabs, get_current_user_id() );	
			
		?>
		
		<ul class="wpfep-tabs" id="wpfep-tabs">
			
			<?php
				
				/**
				* set an array of tab titles and ids
				* the id set here should match the id given to the content wrapper
				* which has the class tab-content included in the callback function
				* @hooked wpfep_add_profile_tab - 10
				* @hooked wpfep_add_password_tab - 20
				*/
				$wpfep_tabs = apply_filters(
					'wpfep_tabs',
					array()
				);
				
				/* check we have items to show */
				if( ! empty( $wpfep_tabs ) ) {

					/* loop through each item */
					foreach( $wpfep_tabs as $wpfep_tab ) {
						
						/* output the tab name as a tab */
						wpfep_tab_list_item( $wpfep_tab );

					}

				}
				
			?>	
			
		</ul><!-- // wpfep-tabs -->
		
		<?php
									
			/* loop through each item */
			foreach( $wpfep_tabs as $wpfep_tab ) {
				
				/* build the content class */
				$content_class = '';
				
				/* if we have a class provided */
				if( $wpfep_tab[ 'content_class' ] != '' ) {
					
					/* add the content class to our variable */
					$content_class .= ' ' . $wpfep_tab[ 'content_class' ];
					
				}
				
				/**
				 * @hook wpfep_before_tab_content
				 * fires before the contents of the tab are outputted
				 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
				 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
				 */
				do_action( 'wpfep_before_tab_content', $wpfep_tab[ 'id' ], get_current_user_id() );
				
				?>
				
				<div class="tab-content<?php echo esc_attr( $content_class ); ?>" id="<?php echo esc_attr( $wpfep_tab[ 'id' ] ); ?>">
					
					<form method="post" action="#" class="wpfep-form-<?php echo esc_attr( $wpfep_tab[ 'id' ] ); ?>">
						
						<?php
							
							/* check if callback function exists */
							if( function_exists( $wpfep_tab[ 'callback' ] ) ) {
								
								/* use custom callback function */
								$wpfep_tab[ 'callback' ]( $wpfep_tab );
							
							/* custom callback does not exist */
							} else {
								
								/* use default callback function */
								wpfep_default_tab_content( $wpfep_tab );
								
							}
						
						?>
						
						<?php
							
							wp_nonce_field(
								'wpfep_nonce_action',
								'wpfep_nonce_name'
							);
						
						?>
					
					</form>
					
				</div>
				
				<?php
						
				/**
				 * @hook wpfep_after_tab_content
				 * fires after the contents of the tab are outputted
				 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
				 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
				 */
				do_action( 'wpfep_after_tab_content', $wpfep_tab[ 'id' ], get_current_user_id() );		
				
			} // end tabs loop

		?>
	
	</div><!-- // wpfep-wrapper -->
		
	<?php
	
}