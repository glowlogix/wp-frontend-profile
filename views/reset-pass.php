<?php
/**
 * @package wp-front-end-profile
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
 * wpfep will always look in your theme's directory first, before using this default template.
 */

defined('ABSPATH') || exit;
?>
<div class="login" id="wpfep-login-form">

	<?php WPFEP_Login::init()->show_errors(); ?>
	<?php WPFEP_Login::init()->show_messages(); ?>

	<form name="resetpasswordform" id="resetpasswordform" action="" method="post">
		<p>
			<label for="wpfep-pass1"><?php esc_attr_e('New password', 'wpfep'); ?></label>
			<input autocomplete="off" name="pass1" id="wpfep-pass1" class="input" size="20" value="" type="password" autocomplete="off" />
		</p>

		<p>
			<label for="wpfep-pass2"><?php esc_attr_e('Confirm new password', 'wpfep'); ?></label>
			<input autocomplete="off" name="pass2" id="wpfep-pass2" class="input" size="20" value="" type="password" autocomplete="off" />
		</p>

		<?php do_action('resetpassword_form'); ?>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Reset Password', 'wpfep'); ?>" />
			<input type="hidden" name="key" value="
			<?php
            if (! empty($_REQUEST['key'])) {
                echo esc_attr($_REQUEST['key']);
            }
            ?>
			" />
			<input type="hidden" name="login" id="user_login" value="<?php echo isset($_REQUEST['login']) ? esc_html(wp_unslash($_REQUEST['login'])) : ''; ?>" />
			<input type="hidden" name="wpfep_reset_password" value="true" />
		</p>

		<?php wp_nonce_field('wpfep_reset_pass'); ?>
	</form>
</div>
