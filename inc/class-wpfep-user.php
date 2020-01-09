<?php
/**
 * Login Class.
 *
 * @package WP Frontend Profile
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

if ( ! class_exists( 'WPFEP_User' ) ) :
	/**
	 * The User Class
	 *
	 * @since 1.0.0
	 */
	class WPFEP_User {

		/**
		 * User ID
		 *
		 * @var integer
		 */
		public $id;

		/**
		 * User Object
		 *
		 * @var \WP_User return user.
		 */
		public $user;

		/**
		 * The constructor
		 *
		 * @param integer|WP_User $user return user.
		 */
		public function __construct( $user ) {

			if ( is_numeric( $user ) ) {
				$the_user = get_user_by( 'id', $user );
				if ( $the_user ) {
					$this->id   = $the_user->ID;
					$this->user = $the_user;
				}
			} elseif ( is_a( $user, 'WP_User' ) ) {
				$this->id   = $user->ID;
				$this->user = $user;
			}
		}


		/**
		 * Check if user is verified
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function is_verified() {
			if ( ! metadata_exists( 'user', $this->id, '_wpfep_user_active' ) ) {
				return true;
			}
			if ( intval( get_user_meta( $this->id, '_wpfep_user_active', true ) ) == 1 ) {
				return true;
			}
			return false;
		}

		/**
		 * Mark user as verified
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function mark_verified() {
			update_user_meta( $this->id, '_wpfep_user_active', 1 );
		}

		/**
		 * Mark user as unverified
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function mark_unverified() {
			update_user_meta( $this->id, '_wpfep_user_active', 0 );
		}

		/**
		 * Set user activation key
		 *
		 * @since 1.0.0
		 *
		 * @param string $key returns key.
		 *
		 * @return void
		 */
		public function set_activation_key( $key ) {
			update_user_meta( $this->id, '_wpfep_activation_key', $key );
		}

		/**
		 * Get user activation key
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_activation_key() {
			return get_user_meta( $this->id, '_wpfep_activation_key', true );
		}

		/**
		 * Remove user activation key
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function remove_activation_key() {
			delete_user_meta( $this->id, '_wpfep_activation_key' );
		}

		/**
		 * Manually approve users
		 *
		 * @since 1.0.0
		 *
		 *  @param int $user return current user id.
		 *
		 * @return void
		 */
		public function manually_approve( $user ) {
			add_user_meta( $user, 'wpfep_user_status', 'pending' );
			$userdata = get_userdata( $user );
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			$subject = 'Registeration E-mail';
			$message = sprintf( esc_attr( 'Congrats! You are Successfully registered to: %s' ), $blogname ) . "\r\n\r\n";
			$message .= 'Your Account is not Approved by Admin.' . "\r\n\r\n";
			$message .= 'We will send Confirmation when It is Approved.' . "\r\n\r\n";
			wp_mail( $userdata->user_email, $subject, $message );
			/* translators: %s: admin mail */
			$message_admin = sprintf( esc_html__( 'New user registration on your site %s:', 'wpfep' ), get_option( 'blogname' ) ) . "\r\n\r\n";
			/* translators: %s: user login */
		    $message_admin .= sprintf( esc_html__( 'Username: %s', 'wpfep' ), $userdata->user_login ) . "\r\n\r\n";
			/* translators: %s: user email */
		    $message_admin .= sprintf( esc_html__( 'E-mail: %s', 'wpfep' ), $userdata->user_email ) . "\r\n";
			/* translators: %s: user subject */
		    $subject = esc_html__( 'New User Registration', 'wpfep' );
			/* translators: %s: user email */
		    wp_mail( get_option( 'admin_email' ), sprintf( esc_html__( '[%1$s] %2$s', 'wpfep' ), $blogname, $subject ), $message_admin );
		}
	}
endif;
