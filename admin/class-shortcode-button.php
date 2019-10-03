<?php

/**
 * wpfep tinyMce Shortcode Button class
 *
 * @since 1.0.0
 */
class WPFEP_Shortcodes_Button {

    /**
     * Constructor for shortcode class
     */
    public function __construct() {

        add_filter( 'mce_external_plugins',  array( $this, 'enqueue_plugin_scripts' ) );
        add_filter( 'mce_buttons',  array( $this, 'register_buttons_editor' ) );

        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'admin_enqueue_scripts', array( $this, 'localize_shortcodes_script' ));
    }
    // enqueue scripts for shortcode of tiny mice 
    function enqueue_scripts() {
        global $pagenow;
        if($pagenow == 'post.php'){
            wp_enqueue_script( 'wpfep_shortcode_handle', plugins_url( '/assets/js/wpfep-tmc-button.js', dirname(__FILE__) ),  array ('jquery') );
        }
    }

    // Localize the script with new data
    function localize_shortcodes_script() {
        
        $shortcodes = array(
            'wpfep-register'  => array(
                'title'   => __( 'Register', 'wpptm' ),
                'content' => '[wpfep-register]'
            ),
            'wpfep-edit'     => array(
                'title'   => __( 'Edit', 'wpptm' ),
                'content' => '[wpfep]'
            ),
            'wpfep-login'    => array(
                'title'   => __( 'Login', 'wpptm' ),
                'content' => '[wpfep-login]'
            ),
            'wpfep-profile'    => array(
                'title'   => __( 'Profile', 'wpptm' ),
                'content' => '[wpfep-profile]'
            ),
        ) ;
        $assets_url = WPFEP_PLUGIN_URL;
        wp_localize_script( 'wpfep_shortcode_handle', 'wpfep_shortcode', $shortcodes );
        wp_localize_script( 'wpfep_shortcode_handle', 'wpfep_assets_url', $assets_url );
    }

    /**
     * * Singleton object
     *
     * @staticvar boolean $instance
     *
     * @return \self
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new WPFEP_Shortcodes_Button();
        }

        return $instance;
    }

    /**
     * Add button on Post Editor
     *
     * @since 1.0.0
     *
     * @param array $plugin_array
     *
     * @return array
     */
    function enqueue_plugin_scripts( $plugin_array ) {
        //enqueue TinyMCE plugin script with its ID.
        $plugin_array["wpfep_button"] =  plugins_url( '/assets/js/wpfep-tmc-button.js', dirname(__FILE__) );

        return $plugin_array;
    }

    /**
     * Register tinyMce button
     *
     * @since 1.0.0
     *
     * @param array $buttons
     *
     * @return array
     */
    function register_buttons_editor( $buttons ) {
        //register buttons with their id.
        array_push( $buttons, "wpfep_button" );

        return $buttons;
    }

}

WPFEP_Shortcodes_Button::init();
