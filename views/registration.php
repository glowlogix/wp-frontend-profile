<?php
/**
 * @package wp-front-end-profile
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
 * wpfep will always look in your theme's directory first, before using this default template.
 */

defined('ABSPATH') || exit;
?>
<?php
$message = apply_filters('registration_message', '');

if (! empty($message)) {
    echo esc_html($message) . "\n";
}
if (isset($_GET['success']) && 'yes' == $_GET['success']) {
    echo "<div class='wpfep-success'>" . esc_html('Registration has been successful!', 'wpfep') . '</div>';
}
if (isset($_GET['success']) && 'notactivated' == $_GET['success']) {
    echo "<div class='wpfep-success'>" . esc_html(esc_attr__('Registration has been successful! Please activate your account from e-mail.', 'wpfep')) . '</div>';
}
if (isset($_GET['success']) && 'notapproved' == $_GET['success']) {
    echo "<div class='wpfep-success'>" . esc_html(esc_attr__('Registration has been successful!. Please wait for admin approval.', 'wpfep')) . '</div>';
}

$register_page = wpfep_get_option('register_page', 'wpfep_pages');
$action_url    = get_permalink($register_page);
$register_obj  = WPFEP_Registration::init();
?>

<?php echo esc_html($register_obj->show_errors()); ?>
<?php echo esc_html($register_obj->show_messages()); ?>

<form name="wpfep_registration_form" class="wpfep-registration-form" id="wpfep_registration_form" action="<?php echo esc_html($action_url); ?>" method="post">
		<ul>
			<li class="wpfep-form-field wpfep-default-first-name">
				<label for="wpfep_reg_fname"><?php esc_attr_e('First Name', 'wpfep'); ?>
				</label>
				<input type="text" name="wpfep_reg_fname" id="wpfep-user_fname" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_fname')); ?>"  />
			</li>
			<li class="wpfep-form-field wpfep-default-last-name">
				<label for="wpfep_reg_lname"><?php esc_attr_e('Last Name', 'wpfep'); ?>
				</label>
				<input type="text" name="wpfep_reg_lname" id="wpfep-user_lname" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_lname')); ?>"  />
			</li>
			<li class="wpfep-form-field wpfep-default-email">
				<label for="wpfep_reg_email"><?php esc_attr_e('Email', 'wpfep'); ?>
					<span class="wpfep-required">*</span>
				</label>
				<input type="Email" name="wpfep_reg_email" id="wpfep-user_email" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_email')); ?>">
			</li>
			<li class="wpfep-form-field wpfep-default-username">
				<label for="wpfep_reg_uname"><?php esc_attr_e('Username', 'wpfep'); ?>
					<span class="wpfep-required">*</span>
				</label>
				<input type="text" name="wpfep_reg_uname" id="wpfep-user_login" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_uname')); ?>" />
			</li>
			<li class="wpfep-form-field wpfep-default-password">
				<label for="pwd1"><?php esc_attr_e('Password', 'wpfep'); ?>
					<span class="wpfep-required">*</span>
				</label>
				<input type="password" name="pwd1" id="wpfep-user_pass1" class="input" value=""  />
			</li>
			<li class="wpfep-form-field wpfep-default-confirm-password">
				<label for="pwd2"><?php esc_attr_e('Confirm Password', 'wpfep'); ?>
					<span class="wpfep-required">*</span>
				</label>
					<input type="password" name="pwd2" id="wpfep-user_pass2" class="input" value=""  />
			</li>
			<li class="wpfep-form-field wpfep-default-user-website">
				<label for="wpfep-description"><?php esc_attr_e('Website', 'wpfep'); ?>
				</label>
				<input type="text" name="wpfep-website" id="wpfep-user_website" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep-website')); ?>"  />
			</li>
			
			<li class="wpfep-form-field wpfep-default-user-bio">
				<label for="wpfep-description"><?php esc_attr_e('Biographical Info', 'wpfep'); ?>
				</label>
				<textarea rows="5" name="wpfep-description" maxlength="" class="default_field_description" id="description"><?php echo esc_html($register_obj->get_post_value('wpfep-description')); ?></textarea>
			</li>
			<li>
				<?php $recaptcha = wpfep_get_option('enable_captcha_registration', 'wpfep_general'); ?>
				<?php if ('on' == $recaptcha) { ?>
					<div class="wpfep-fields">
						<?php WPFEP_Captcha_Recaptcha::display_captcha(); ?>
					</div>
				<?php } ?>

				<?php $hcaptcha = wpfep_get_option('enable_hcaptcha_registration', 'wpfep_general'); ?>
                <?php if ('on' == $hcaptcha) { ?>
                <p>
                <div class="wpfep-fields">
                    <?php WPFEP_Captcha_hCaptcha::display_captcha(); ?>
                </div>
                </p>
                <?php } ?>
			</li>
			<li class="wpfep-submit">
				<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Register', 'wpfep'); ?>" />
				<input type="hidden" name="redirect_to" value="" />
				<input type="hidden" name="wpfep_registration" value="true" />
				<input type="hidden" name="action" value="registration" />
				<?php wp_nonce_field('wpfep_registration_action'); ?>
			</li>
			<?php do_action('wpfep_reg_form_bottom'); ?>
		</ul>
	</form>
