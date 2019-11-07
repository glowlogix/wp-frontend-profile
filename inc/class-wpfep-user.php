<?php
/**
 * Login Class.
 *
 * @package WP Frontend Profile
 */

defined( 'ABSPATH' ) || exit;

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
	}
endif;
