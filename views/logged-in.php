<?php
/**
 * Logged-in user.
 *
 * @package WP Frontend Profile
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wpfep-user-loggedin">
	<?php
	echo '<p class="alert" id="wpfep_register_pre_form_message">';
		printf( esc_html( "You are currently logged in as %1\$1s. You don't need another account. %2\$2s", 'profile-builder' ), '<a href="' . esc_html( get_author_posts_url( $user->ID ) ) . '" title="' . esc_html( $user->display_name ) . '">' . esc_html( $user->display_name ) . '</a>', wp_loginout( '', false ) ) . '</p>';
	?>
</div>
