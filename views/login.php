<?php
/*
  If you would like to edit this file, copy it to your current theme's directory and edit it there.
  wpfep will always look in your theme's directory first, before using this default template.
 */
?>
<div class="login" id="wpfep-login-form">

    <?php

    $message = apply_filters( 'login_message', '' );
    if ( ! empty( $message ) ) {
        echo $message . "\n";
    }
      $login_obj = WPFEP_Login::init(); 
    ?>

    <?php 
        $login_obj->show_errors(); 
        $login_obj->show_messages(); 

    ?>

    <form name="loginform" class="wpfep-login-form" id="loginform" action="<?php echo $action_url; ?>" method="post">
        <p>
            <label for="wpfep-user_login"><?php _e( 'Username or Email', 'wpptm' ); ?></label>
            <input type="text" name="log" id="wpfep-user_login" class="input" value="" size="20" />
        </p>
        <p>
            <label for="wpfep-user_pass"><?php _e( 'Password', 'wpptm' ); ?></label>
            <input type="password" name="pwd" id="wpfep-user_pass" class="input" value="" size="20" />
        </p>

        <?php $recaptcha = wpfep_get_option( 'enable_captcha_login', 'wpfep_general' ); ?>
        <?php if( $recaptcha == 'on' ) : ?>
            <p>
                <div class="wpfep-fields">
                    <?php WPFEP_Captcha_Recaptcha::display_captcha(); ?>
                </div>
            </p>
        <?php endif; ?>

        <p class="forgetmenot">
            <input name="rememberme" type="checkbox" id="wpfep-rememberme" value="forever" />
            <label for="wpfep-rememberme"><?php esc_attr_e( 'Remember Me', 'wpptm' ); ?></label>
        </p>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Log In', 'wpptm' ); ?>" />
            <input type="hidden" name="redirect_to" value="<?php echo wp_get_referer() ?>" />
            <input type="hidden" name="wpfep_login" value="true" />
            <input type="hidden" name="action" value="login" />
            <?php wp_nonce_field( 'wpfep_login_action' ); ?>
        </p>
        <p>
            <?php do_action( 'wpfep_login_form_bottom' ); ?>
        </p>
    </form>

    <?php echo $login_obj->lost_password_links(); ?>
</div>
