<?php
/**
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
  wpfep will always look in your theme's directory first, before using this default template.
 *
 * @package WP Frontend Profile
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="login" id="wpfep-login-form">

	<?php

	$message = apply_filters( 'login_message', '' );
	if ( ! empty( $message ) ) {
		echo esc_html( $message ) . "\n";
	}
	  $login_obj = WPFEP_Login::init();
	?>

	<?php
		$login_obj->show_errors();
		$login_obj->show_messages();

	?>

	<form name="loginform" class="wpfep-login-form" id="loginform" action="<?php echo esc_html( $action_url ); ?>" method="post">
		<p>
			<label for="wpfep-user_login"><?php esc_attr_e( 'Username or Email', 'wpfep' ); ?></label>
			<input type="text" name="log" id="wpfep-user_login" class="input" value="" size="20" />
		</p>
		<p>
			<label for="wpfep-user_pass"><?php esc_attr_e( 'Password', 'wpfep' ); ?></label>
			<input type="password" name="pwd" id="wpfep-user_pass" class="input" value="" size="20" />
		</p>

		<?php $recaptcha = wpfep_get_option( 'enable_captcha_login', 'wpfep_general' ); ?>
		<?php if ( 'on' == $recaptcha ) : ?>
			<p>
				<div class="wpfep-fields">
					<?php WPFEP_Captcha_Recaptcha::display_captcha(); ?>
				</div>
			</p>
		<?php endif; ?>

		<p class="forgetmenot">
			<input name="rememberme" type="checkbox" id="wpfep-rememberme" value="forever" />
			<label for="wpfep-rememberme"><?php esc_attr_e( 'Remember Me', 'wpfep' ); ?></label>
		</p>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Log In', 'wpfep' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_html( wp_get_referer() ); ?>" />
			<input type="hidden" name="wpfep_login" value="true" />
			<input type="hidden" name="action" value="login" />
			<?php wp_nonce_field( 'wpfep_login_action' ); ?>
		</p>
		<p>
			<?php do_action( 'wpfep_login_form_bottom' ); ?>
		</p>
	</form>

	<?php
	$lostpass = $login_obj->lost_password_links();
			echo wp_kses(
				$lostpass,
				array(
					'a' => array(
						'href'  => array(),
						'title' => array(),
						'id'    => array(),
						'class' => array(),
					),
				)
			);
			?>
</div>
