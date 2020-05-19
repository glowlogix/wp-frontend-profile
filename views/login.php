<?php
/**
 * If you would like to edit this file, copy it to your current theme's directory and edit it there.
 * wpfep will always look in your theme's directory first, before using this default template.
 */
defined('ABSPATH') || exit;
?>
<div class="login" id="wpfep-login-form">

	<?php

    $message = apply_filters('login_message', '');
    if (!empty($message)) {
        echo esc_html($message)."\n";
    }
    if (isset($_GET['key'])) {
        $user_id = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($user_id) {
            $code = get_user_meta($user_id, 'has_to_be_activated', true);
            $manually_approve_user = wpfep_get_option('admin_manually_approve', 'wpfep_profile', 'on');
            if ($code == $_GET['key']) {
                echo "<div class='wpfep-success'>".esc_html(esc_attr__('Congratulations! Your account has been verified.', 'wp-front-end-profile')).'</div>';
                update_user_meta($user_id, 'verify', 'Yes');
            }
        }
    }
    $login_obj = WPFEP_Login::init();
    $register_obj = WPFEP_Registration::init();
    ?>

	<?php
        $login_obj->show_errors();
        $login_obj->show_messages();

    ?>

	<form name="loginform" class="wpfep-login-form" id="loginform" action="" method="post">
		<p>
			<label for="wpfep-user_login"><?php esc_attr_e('Username or Email', 'wp-front-end-profile'); ?></label>
			<input type="text" name="log" id="wpfep-user_login" class="input" value="<?php echo esc_html($register_obj->get_post_value('log')); ?>" size="20" />
		</p>
		<p>
			<label for="wpfep-user_pass"><?php esc_attr_e('Password', 'wp-front-end-profile'); ?></label>
			<input type="password" name="pwd" id="wpfep-user_pass" class="input" value="" size="20" />
		</p>
		<?php $recaptcha = wpfep_get_option('enable_captcha_login', 'wpfep_general'); ?>
		<?php if ('on' == $recaptcha) { ?>
			<p>
				<div class="wpfep-fields">
					<?php WPFEP_Captcha_Recaptcha::display_captcha(); ?>
				</div>
			</p>
		<?php } ?>
		<p class="forgetmenot">
			<input name="rememberme" type="checkbox" id="wpfep-rememberme" value="forever" />
			<label for="wpfep-rememberme"><?php esc_attr_e('Remember Me', 'wp-front-end-profile'); ?></label>
		</p>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Log In', 'wp-front-end-profile'); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_html(wp_get_referer()); ?>" />
			<input type="hidden" name="wpfep_login" value="true" />
			<input type="hidden" name="action" value="login" />
			<?php wp_nonce_field('wpfep_login_action'); ?>
		</p>
		<p>
			<?php do_action('wpfep_login_form_bottom'); ?>
		</p>
	</form>

	<?php
    $lostpass = $login_obj->lost_password_links();
            echo wp_kses(
                $lostpass,
                [
                    'a' => [
                        'href'  => [],
                        'title' => [],
                        'id'    => [],
                        'class' => [],
                    ],
                ]
            );
            ?>
</div>
