<?php
/**
 * Feedbacks.
 */
defined('ABSPATH') || exit;

/**
 * Displays Feedback Notice.
 *
 * @return void
 */
function wpfep_error_notices()
{
    $current_user = wp_get_current_user();
    $ask_review_date = get_option('wpfep_Ask_Review_Date');
    if (get_option('wpfep_Install_Time') == '') {
        update_option('wpfep_Install_Time', time());
    }
    if ('' == $ask_review_date) {
        $ask_review_date = get_option('wpfep_Install_Time') + 3600 * 24 * 4;
    }
    if ($ask_review_date < time() && get_option('wpfep_Install_Time') < time() - 3600 * 24 * 4) {
        global $pagenow;
        if ('post.php' != $pagenow && 'post-new.php' != $pagenow && current_user_can('administrator')) { ?>
			<div class='notice notice-info is-dismissible wpfep-main-dashboard-review-ask updated_wpfep' style="display: none;">
				<div class='wpfep-review-ask-plugin-icon'></div>
				<div class='wpfep-review-ask-text'>
					<p class='wpfep-review-ask-starting-text'><?php esc_attr_e('Enjoying using the WP Frontend Profile plugin', 'wp-front-end-profile'); ?></p>
					<p class='wpfep-review-ask-feedback-text wpfep-hidden'><?php esc_attr_e('Help us make the plugin better! Please take a minute to rate the plugin. Thanks!', 'wp-front-end-profile'); ?></p>
					<p class='wpfep-review-ask-review-text wpfep-hidden'>
					<?php
                    $feed_line = 'Please let us know what we could do to make the plugin better!<br /><span>Privacy disclaimer: Your email address shall be collected for support purposes when you send your feedback.';

                    echo wp_kses(
                        $feed_line,
                        [
                            'p'    => [
                                'class' => [],

                            ],
                            'br'   => [],
                            'span' => [],

                        ]
                    );
                    ?>
					</span></p>
					<p class='wpfep-review-ask-thank-you-text wpfep-hidden'><?php esc_attr_e('Thank you for taking the time to help us!', 'wp-front-end-profile'); ?></p>
				</div>
				<div class='wpfep-review-ask-actions'>
					<div class='wpfep-review-ask-action wpfep-review-ask-not-really wpfep-review-ask-white'><?php esc_attr_e('Not Really', 'wp-front-end-profile'); ?></div>
					<div class='wpfep-review-ask-action wpfep-review-ask-yes wpfep-review-ask-blue'><?php esc_attr_e('Yes!', 'wp-front-end-profile'); ?></div>
					<div class='wpfep-review-ask-action wpfep-review-ask-no-thanks wpfep-review-ask-white wpfep-hidden'><?php esc_attr_e('No Thanks', 'wp-front-end-profile'); ?></div>
					<a href='https://wordpress.org/support/plugin/wp-front-end-profile/reviews/' target='_blank'>
						<div class='wpfep-review-ask-action wpfep-review-ask-review wpfep-review-ask-blue wpfep-hidden'><?php esc_attr_e('OK, Sure', 'wp-front-end-profile'); ?></div>
					</a>
				</div>
				<div class='wpfep-review-ask-feedback-form wpfep-hidden'>
					<div class='wpfep-review-ask-feedback-explanation'>
						<textarea></textarea>
						<br>
						<input type="hidden" name="feedback_email_address" value="<?php echo esc_attr($current_user->user_email); ?>">
					</div>
					<div class='wpfep-review-ask-send-feedback wpfep-review-ask-action wpfep-review-ask-blue'><?php esc_attr_e('Submit', 'wp-front-end-profile'); ?></div>
				</div>
				<div class='wpfep-clear'></div>
			</div>
			<?php
        }
    }
}
