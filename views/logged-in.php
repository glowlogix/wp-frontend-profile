<div class="wpfep-user-loggedin">
	<?php echo '<p class="alert" id="wpfep_register_pre_form_message">';
		printf( __( "You are currently logged in as %1s. You don't need another account. %2s", 'profile-builder' ), '<a href="'.get_author_posts_url( $user->ID ).'" title="'.$user->display_name.'">'.$user->display_name.'</a>', wp_loginout( '', false ) ).'</p>';?>
</div>
