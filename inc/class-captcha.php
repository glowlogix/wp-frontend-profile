<?php


class WPFEP_Captcha_Recaptcha {

	/** @var string captcha site key */
	static private $site_key;

	/** @var string captcha secrete key */
	static private $secret_key;

	static protected $error_message;

	static protected $script_handle;

	public static function initialize() {
		self::$site_key = wpfep_get_option( 'recaptcha_public', 'wpfep_general' );

		self::$secret_key = wpfep_get_option( 'recaptcha_private', 'wpfep_general' );

		self::$script_handle = 'wpfep-recaptcha';

		add_action( 'plugins_loaded', array( __CLASS__, 'load_plugin_textdomain' ) );

		// initialize if login is activated
		if (( wpfep_get_option( 'enable_captcha_login', 'wpfep_general' ) == 'on' ) || ( wpfep_get_option( 'enable_captcha_registration', 'wpfep_general' ) == 'on' )) {

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_header_script' ) );

		}

	}

	/**
	* Enqueue the Google ReCAPTCHA script using the WP system.
	*
	* @since 1.0.0
	*/
	public static function enqueue_header_script() {

		$src = 'https://www.google.com/recaptcha/api.js';

		wp_enqueue_script( self::$script_handle, $src, false, false, true );
		wp_enqueue_script( 'wpfep_google_recaptcha', 'https://www.google.com/recaptcha/api.js', false, false, false );
	}

	/** Output the reCAPTCHA form field. */
	public static function display_captcha() {

		if ( isset( $_GET['captcha'] ) && $_GET['captcha'] == 'failed' ) {
			echo self::$error_message;
		}

		echo '<div class="g-recaptcha" data-sitekey="' . self::$site_key . '"></div>';
	}

	/**
	 * Send a GET request to verify captcha challenge
	 *
	 * @return bool
	 */
	public static function captcha_verification() {

		$response = isset( $_POST['g-recaptcha-response'] ) ? esc_attr( $_POST['g-recaptcha-response'] ) : '';
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
		$remote_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
		 $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $remote_ip = $_SERVER['REMOTE_ADDR'];
		}

		// make a GET request to the Google reCAPTCHA Server
		$request = wp_remote_get(
			'https://www.google.com/recaptcha/api/siteverify?secret=' . self::$secret_key . '&response=' . $response . '&remoteip=' . $remote_ip
		);

		// get the request response body
		$response_body = wp_remote_retrieve_body( $request );

		$result = json_decode( $response_body, true );
		if (isset($result['success']) and $result['success'] == true) {
			$status = true;
		} else {
			$status = false;
			$error = (isset($result['error-codes'])) ? $result['error-codes']
				: 'invalid-input-response';
		}

		return array(
			'success' => $status,
			'error-codes' => (isset($error)) ? $error : null,
		);
	}
}
