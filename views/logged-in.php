<?php
/**
 * Logged-in user.
 */
defined('ABSPATH') || exit;

if (isset($_GET['success']) && 'yes' === $_GET['success']) {
    echo "<div class='wpfep-success'>".esc_html(esc_attr__('User has been successfully registered.', 'wp-front-end-profile')).'</div>';
}
if (isset($_GET['success']) && 'notactivated' === $_GET['success']) {
    echo "<div class='wpfep-success'>".esc_html(esc_attr__('User has been successfully registered manually. Activation email has been sent to user successfully.', 'wp-front-end-profile')).'</div>';
}
if (isset($_GET['success']) && 'createdmanually' === $_GET['success']) {
    echo "<div class='wpfep-success'>".esc_html(esc_attr__('User has been successfully registered manually.', 'wp-front-end-profile')).'</div>';
}
if (isset($_GET['success']) && 'notapproved' === $_GET['success']) {
    echo "<div class='wpfep-success'>".esc_html(esc_attr__('User has been successfully registered manually.', 'wp-front-end-profile')).'</div>';
}
if (isset($_GET['success']) && 'created' === $_GET['success']) {
    echo "<div class='wpfep-success'>".esc_html(esc_attr__('Registration has done successfully', 'wp-front-end-profile')).'</div>';
}
$register_page = wpfep_get_option('register_page', 'wpfep_pages');
$manually_register = wpfep_get_option('admin_can_register_user_manually', 'wpfep_profile', 'on');
if (current_user_can('administrator') && 'on' === $manually_register && is_page($register_page)) {
    $register_page = wpfep_get_option('register_page', 'wpfep_pages');
    $action_url = get_permalink($register_page);
    $register_obj = WPFEP_Registration::init();
    $login_obj = WPFEP_Login::init(); ?>

	<?php echo esc_html($register_obj->show_errors()); ?>
	<?php echo esc_html($register_obj->show_messages()); ?>
    <form name="wpfep_registration_form" class="wpfep-registration-form" id="wpfep_registration_form" action="<?php echo esc_html($action_url); ?>" method="post">
        <ul>
            <li class="wpfep-form-field wpfep-default-first-name">
                <label for="wpfep_reg_fname"><?php esc_attr_e('First Name', 'wp-front-end-profile'); ?>
                </label>
                <input type="text" name="wpfep_reg_fname" id="wpfep-user_fname" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_fname')); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-last-name">
                <label for="wpfep_reg_lname"><?php esc_attr_e('Last Name', 'wp-front-end-profile'); ?>
                </label>
                <input type="text" name="wpfep_reg_lname" id="wpfep-user_lname" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_lname')); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-email">
                <label for="wpfep_reg_email"><?php esc_attr_e('Email', 'wp-front-end-profile'); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="Email" name="wpfep_reg_email" id="wpfep-user_email" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_email')); ?>">
            </li>
            <li class="wpfep-form-field wpfep-default-username">
                <label for="wpfep_reg_uname"><?php esc_attr_e('Username', 'wp-front-end-profile'); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="text" name="wpfep_reg_uname" id="wpfep-user_login" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep_reg_uname')); ?>" />
            </li>
            <li class="wpfep-form-field wpfep-default-password">
                <label for="pwd1"><?php esc_attr_e('Password', 'wp-front-end-profile'); ?>
                    <span class="wpfep-required">*</span>
                </label>
                <input type="password" name="pwd1" id="wpfep-user_pass1" class="input" value=""  />
            </li>
            <li class="wpfep-form-field wpfep-default-confirm-password">
                <label for="pwd2"><?php esc_attr_e('Confirm Password', 'wp-front-end-profile'); ?>
                    <span class="wpfep-required">*</span>
                </label>
                    <input type="password" name="pwd2" id="wpfep-user_pass2" class="input" value=""  />
            </li>
            <li class="wpfep-form-field wpfep-default-user-website">
                <label for="wpfep-description"><?php esc_attr_e('Website', 'wp-front-end-profile'); ?>
                </label>
                <input type="text" name="wpfep-website" id="wpfep-user_website" class="input" value="<?php echo esc_html($register_obj->get_post_value('wpfep-website')); ?>"  />
            </li>
            <li class="wpfep-form-field wpfep-default-user-role">
                <label for="wpfep-default-role" class="wpfep-default-role">
                <input type="radio" class="wpfep-showform-default" value="Default role" name="showform" checked="" /><?php esc_attr_e('Default role', 'wp-front-end-profile'); ?></label>
                <?php
                $roles_obj = new WP_Roles();
    $roles_names_array = $roles_obj->get_names(); ?>
                <select name="role" id="wpfep-custom" class="input" style="display:none">
                <?php
                    foreach ($roles_names_array as $key => $value) {
                        if (in_array($key, ['subscriber'])) {
                            echo '<option value="'.$key.'">'.$value.'</option>';
                        }
                    } ?>
                </select>
                <label for="wpfep-custom-role" class="wpfep-custom-role">
                <input type="radio" class="wpfep-showform-custom" value="Custom role" name="showform" class="wpfep-role-right"/><?php esc_attr_e('Custom role', 'wp-front-end-profile'); ?></label><br><br>
                <div id="wpfep-default"></div>
                <?php
                $roles_obj = new WP_Roles();
    $roles_names_array = $roles_obj->get_names(); ?>
                <select name="role" id="wfp-user-role" class="input" style="display:none">
                    <option value="" disabled selected><?php esc_attr_e('Select user role', 'wp-front-end-profile'); ?></option>
                    <?php
                    foreach ($roles_names_array as $key => $value) {
                        if (in_array($key, ['editor', 'author', 'contributor'])) {
                            echo '<option value="'.$key.'">'.$value.'</option>';
                        }
                    } ?>
                </select>
            </li>
            <li class="wpfep-form-field wpfep-default-user-bio">
                <label for="wpfep-description"><?php esc_attr_e('Biographical Info', 'wp-front-end-profile'); ?>
                </label>
                <textarea rows="5" name="wpfep-description" maxlength="" class="default_field_description" id="description"><?php echo esc_html($register_obj->get_post_value('wpfep-description')); ?></textarea>
            </li>
            <li class="wpfep-submit">
                <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e('Register', 'wp-front-end-profile'); ?>" />
                <input type="hidden" name="urhidden" value=" <?php echo esc_html($userrole); ?>" />
                <input type="hidden" name="redirect_to" value="" />
                <input type="hidden" name="wpfep_registration" value="true" />
                <input type="hidden" name="action" value="registration" />
                <?php wp_nonce_field('wpfep_registration_action'); ?>
            </li>
            <?php do_action('wpfep_reg_form_bottom'); ?>
        </ul>
    </form>
	<?php
} elseif (is_user_logged_in() === true) { ?>
    <div class="wpfep-user-loggedin">
	<p class="alert" id="wpfep_register_pre_form_message">
	<?php printf(__("You are currently logged in. You don't need another account. %s", 'wp-front-end-profile'), wp_loginout('', false)).'</p>'; ?>
	</div>
<?php } ?>