<?php
/**
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
 * wpfep will always look in your theme's directory first, before using this default template.
 */
defined('ABSPATH') || exit;
?>
<div class="login" id="wpfep-login-form">

	<?php WPFEP_Login::init()->show_errors(); ?>
	<?php WPFEP_Login::init()->show_messages(); ?>

	<form name="lostpasswordform" id="lostpasswordform" action="" method="post">
		<p>
			<label for="wpfep-user_login"><?php esc_attr_e('Username or E-mail:', 'wp-front-end-profile'); ?></label>
			<input type="text" name="user_login" id="wpfep-user_login" class="input" value="" size="20" />
		</p>

		<?php do_action('lostpassword_form'); ?>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Get New Password', 'wp-front-end-profile'); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr('redirect_to'); ?>" />
			<input type="hidden" name="wpfep_reset_password" value="true" />
			<input type="hidden" name="action" value="lostpassword" />

			<?php wp_nonce_field('wpfep_lost_pass'); ?>
		</p>
	</form>
</div>
