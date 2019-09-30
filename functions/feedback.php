<?php
/**
 * Displays Feedback Notice
 * @return void
 */
function Wpfep_Error_Notices(){
	$current_user = wp_get_current_user();
	$Ask_Review_Date = get_option('wpfep_Ask_Review_Date');
	if (get_option("wpfep_Install_Time") == "") {update_option("wpfep_Install_Time", time());}
	if ($Ask_Review_Date == "") {$Ask_Review_Date = get_option("wpfep_Install_Time") + 3600*24*4;}
	if ($Ask_Review_Date < time() and get_option("wpfep_Install_Time") < time() - 3600*24*4) {

		global $pagenow;
		if($pagenow != 'post.php' && $pagenow != 'post-new.php' && current_user_can('administrator')){ ?>
			<div class='notice notice-info is-dismissible wpfep-main-dashboard-review-ask' style="display: none;">
				<div class='wpfep-review-ask-plugin-icon'></div>
				<div class='wpfep-review-ask-text'>
					<p class='wpfep-review-ask-starting-text'>Enjoying using the WP Frontend Profile plugin?</p>
					<p class='wpfep-review-ask-feedback-text wpfep-hidden'>Help us make the plugin better! Please take a minute to rate the plugin. Thanks!</p>
					<p class='wpfep-review-ask-review-text wpfep-hidden'>Please let us know what we could do to make the plugin better!<br /><span>Privacy disclaimer: Your email address shall be collected for support purposes when you send your feedback.</span></p>
					<p class='wpfep-review-ask-thank-you-text wpfep-hidden'>Thank you for taking the time to help us!</p>
				</div>
				<div class='wpfep-review-ask-actions'>
					<div class='wpfep-review-ask-action wpfep-review-ask-not-really wpfep-review-ask-white'>Not Really</div>
					<div class='wpfep-review-ask-action wpfep-review-ask-yes wpfep-review-ask-blue'>Yes!</div>
					<div class='wpfep-review-ask-action wpfep-review-ask-no-thanks wpfep-review-ask-white wpfep-hidden'>No Thanks</div>
					<a href='https://wordpress.org/support/plugin/wp-front-end-profile/reviews/' target='_blank'>
						<div class='wpfep-review-ask-action wpfep-review-ask-review wpfep-review-ask-blue wpfep-hidden'>OK, Sure</div>
					</a>
				</div>
				<div class='wpfep-review-ask-feedback-form wpfep-hidden'>
					<div class='wpfep-review-ask-feedback-explanation'>
						<textarea></textarea>
						<br>
						<input type="hidden" name="feedback_email_address" value="<?php echo $current_user->user_email; ?>">
					</div>
					<div class='wpfep-review-ask-send-feedback wpfep-review-ask-action wpfep-review-ask-blue'>Send Feedback</div>
				</div>
				<div class='wpfep-clear'></div>
			</div>
			<?php
		}
	}
}



