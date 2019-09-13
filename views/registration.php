<?php
/*
  If you would like to edit this file, copy it to your current theme's directory and edit it there.
  wpfep will always look in your theme's directory first, before using this default template.
 */
?>

    <?php
    $message = apply_filters( 'registration_message', '' );
    if ( ! empty( $message ) ) {
        echo $message . "\n";
    }

    if ( isset($_GET['success']) && "yes" == $_GET['success'] ) {
        echo "<div class='wpfep-success'>" . __( 'Registration has been successful!', 'wpptm' ) ."</div>";
    }
    global $wp;
    $action_url = home_url( $wp->request );
    $register_obj = WPFEP_Registration::init();
    $login_obj = WPFEP_Login::init();
    ?>

    <?php echo $register_obj->show_errors();?>
    <?php echo $register_obj->show_messages();?>

    <form name="wpfep_registration_form" class="wpfep-registration-form" id="wpfep_registration_form" action="<?php echo $action_url; ?>" method="post">
        <ul>
            <li class="wpfep-form-field wpfep-default-first-name">
                <label for="wpfep_reg_fname"><?php _e( 'First Name', 'wpptm' ); ?>
                </label>
                <input type="text" name="wpfep_reg_fname" id="wpfep-user_fname" class="input" value="<?php echo get_post_value( 'wpfep_reg_fname' ); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-last-name">
                <label for="wpfep_reg_lname"><?php _e( 'Last Name', 'wpptm' ); ?>
                </label>
                <input type="text" name="wpfep_reg_lname" id="wpfep-user_lname" class="input" value="<?php echo get_post_value( 'wpfep_reg_lname' ); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-email">
                <label for="wpfep_reg_email"><?php _e( 'Email', 'wpptm' ); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="text" name="wpfep_reg_email" id="wpfep-user_email" class="input" value="<?php echo get_post_value( 'wpfep_reg_email' ); ?>">
            </li>
            <li class="wpfep-form-field wpfep-default-username">
                <label for="wpfep_reg_uname"><?php _e( 'Username', 'wpptm' ); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="text" name="wpfep_reg_uname" id="wpfep-user_login" class="input" value="<?php echo get_post_value( 'wpfep_reg_uname' ); ?>" />
            </li>
            <li class="wpfep-form-field wpfep-default-password">
                <label for="pwd1"><?php _e( 'Password', 'wpptm' ); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="password" name="pwd1" id="wpfep-user_pass1" class="input" value=""  />
            </li>
            <li class="wpfep-form-field wpfep-default-confirm-password">
                <label for="pwd2"><?php _e( 'Confirm Password', 'wpptm' ); ?>
                    <span class="wpfep-required">*</span>
                </label>
                    <input type="password" name="pwd2" id="wpfep-user_pass2" class="input" value=""  />
            </li>
            <li class="wpfep-form-field wpfep-default-user-website">
                <label for="wpfep-description"><?php _e( 'Website', 'wpptm' ); ?>
                </label>
                <input type="text" name="wpfep-website" id="wpfep-user_website" class="input" value="<?php echo get_post_value( 'wpfep-website' ); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-user-bio">
                <label for="wpfep-description"><?php _e( 'Biographical Info', 'wpptm' ); ?>
                </label>
                <textarea rows="5" name="wpfep-description" maxlength="" class="default_field_description" id="description"><?php echo get_post_value( 'wpfep-description' ); ?></textarea>
            </li>
            <li>
                <?php $recaptcha = wpfep_get_option( 'enable_captcha_registration', 'wpfep_general' ); ?>
                <?php if( $recaptcha == 'on' ) : ?>
                    <div class="wpfep-fields">
                        <?php WPFEP_Captcha_Recaptcha::display_captcha(); ?>
                    </div>
                <?php endif; ?>
            </li>
            <li class="wpfep-submit">
                <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Register', 'wpptm' ); ?>" />
                <input type="hidden" name="urhidden" value=" <?php echo $userrole; ?>" />
                <input type="hidden" name="redirect_to" value="" />
                <input type="hidden" name="wpfep_registration" value="true" />
                <input type="hidden" name="action" value="registration" />

                <?php wp_nonce_field( 'wpfep_registration_action' ); ?>
            </li>
            <!-- <li> -->
                <?php //echo $login_obj->get_action_links( array( 'register' => false ) ); ?>
            <!-- </li> -->
            <?php do_action( 'wpfep_reg_form_bottom' ); ?>
        </ul>
    </form>
