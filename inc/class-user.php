<?php

/**
 * The User Class
 *
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
     * @var \WP_User
     */
    public $user;

    /**
     * The constructor
     *
     * @param integer|WP_User $user
     */
    function __construct( $user ) {

        if ( is_numeric( $user ) ) {

            $the_user = get_user_by( 'id', $user );

            if ( $the_user ) {
                $this->id   = $the_user->ID;
                $this->user = $the_user;
            }

        } elseif ( is_a( $user, 'WP_User') ) {
            $this->id   = $user->ID;
            $this->user = $user;
        }
    }

    
    /**
     * Check if user is verified
     *
     * @return bool
     */
    public function is_verified() {
        if ( !metadata_exists( 'user', $this->id, '_wpfep_user_active' ) ) {
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
     * @return void
     */
    public function mark_verified() {
        update_user_meta( $this->id, '_wpfep_user_active', 1 );
    }

    /**
     * Mark user as unverified
     *
     *
     * @return void
     */
    public function mark_unverified() {
        update_user_meta( $this->id, '_wpfep_user_active', 0 );
    }

    /**
     * Set user activation key
     *
     * @return void
     */
    public function set_activation_key( $key ) {
        update_user_meta( $this->id, '_wpfep_activation_key', $key );
    }

    /**
     * Get user activation key
     *
     * @return string
     */
    public function get_activation_key() {
        return get_user_meta( $this->id, '_wpfep_activation_key', true );
    }

    /**
     * Remove user activation key
     *
     * @return void
     */
    public function remove_activation_key( ) {
        delete_user_meta( $this->id, '_wpfep_activation_key' );
    }
}
