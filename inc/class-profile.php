<?php

/**
 * User Profile handler class
 */
class WPFEP_Profile {

    private $login_errors = array();
    private $messages = array();

    private static $_instance;

    function __construct() {
        add_shortcode( 'wpfep-profile', array($this, 'user_profile') );
    }

    /**
     * Singleton object
     *
     * @return self
     */
    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }


    /**
     * Shows the user profile
     *
     * @return string
     */
    function user_profile() {

        ob_start();

        wpfep_load_template( 'profile.php', $args );

        return ob_get_clean();
    }

    /**
     * Add Error message
     *
     * @since 1.0.0
     *
     * @param $message
     */
    public function add_error( $message ) {
        $this->login_errors[] = $message;
    }

    /**
     * Add info message
     *
     * @since 1.0.0
     *
     * @param $message
     */
    public function add_message( $message ) {
        $this->messages[] = $message;
    }
    /**
     * Show erros on the form
     *
     * @return void
     */
    function show_errors() {
        if ( $this->login_errors ) {
            foreach ($this->login_errors as $error) {
                echo '<div class="wpfep-error">';
                _e( $error,'wpptm' );
                echo '</div>';
            }
        }
    }

    /**
     * Show messages on the form
     *
     * @return void
     */
    function show_messages() {
        if ( $this->messages ) {
            foreach ($this->messages as $message) {
                printf( '<div class="wpfep-message">%s</div>', $message );
            }
        }
    }
}
