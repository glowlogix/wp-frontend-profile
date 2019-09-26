<?php
/*
Plugin Name: WP Frontend Profile
Description: This plugin allows users to easily edit their profile information on the front end rather than having to go into the dashboard to make changes to password, email address and other user meta data.
Version:     1.0.0
Author:      Glowlogix
Author URI:  https://glowlogix.com
Text Domain: wpptm
License:     GPL v2 or later
*/

/**
 * Main class for WP Frontend Profile
 *
 * @package WP Frontend Profile
 */

final class WP_Frontend_Profile {
	/**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

     /**
     * The singleton instance
     *
     * @var WP_Frontend_Profile
     */
    private static $_instance;

    /**
     * Fire up the plugin
     */
    public function __construct() {
    	$this->includes();
        $this->init_hooks();
        do_action( 'wfep_loaded' );
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {
    	add_action( 'plugins_loaded', array( $this, 'instantiate' ) );
    	add_action( 'init', array( $this, 'load_textdomain' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
    }
     /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
    	require_once dirname( __FILE__ ) . '/functions/scripts.php';
		require_once dirname( __FILE__ ) . '/functions/default-fields.php';
		require_once dirname( __FILE__ ) . '/functions/tabs.php';
		require_once dirname( __FILE__ ) . '/functions/wpfep-functions.php';
		require_once dirname( __FILE__ ) . '/functions/save-fields.php';
		require_once dirname( __FILE__ ) . '/functions/shortcode.php';
		require_once dirname( __FILE__ ) . '/functions/feedback.php';
		require_once  dirname( __FILE__ ) . '/inc/class-user.php';

		if (is_admin()) {
			require_once dirname( __FILE__ ) . '/admin/installer.php';
			require_once dirname( __FILE__ ) . '/admin/class-admin-settings.php';
			require_once dirname( __FILE__ ) . '/admin/class-shortcode-button.php';
			require_once dirname( __FILE__ ) . '/admin/class-help-tab.php';
		} else {

            require_once dirname( __FILE__ ) . '/inc/class-registration.php';
            require_once dirname( __FILE__ ) . '/inc/class-login.php';
            require_once dirname( __FILE__ ) . '/inc/class-profile.php';
            require_once dirname( __FILE__ ) . '/inc/class-captcha.php';
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    function instantiate() {
  
    	if ( is_admin() ) {
      		$this->container['settings']           = WPFEP_Admin_Settings::init();
      		$this->container['admin_installer']    = new WPFEP_Admin_Installer();
      		
      	}
      	else {
      		
      		$this->container['registration']    = WPFEP_Registration::init();
      		$this->container['login']    		= WPFEP_Login::init();
      		$this->container['profile']    		= WPFEP_Profile::init();
      		$this->container['captcha']    		= WPFEP_Captcha_Recaptcha::initialize();
      	}
    }

     /**
     * Load the translation file for current language.
     */
    function load_textdomain() {
        load_plugin_textdomain( 'wpptm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Singleton Instance
     *
     * @return \self
     */
    public static function init() {

        if ( ! self::$_instance ) {
            self::$_instance = new WP_Frontend_Profile();
        }

        return self::$_instance;
    }
    /**
     * @since  1.0.0
     */
    function plugin_action_links( $links ) {

        $mylinks = array(
		 '<a href="' . admin_url( 'admin.php?page=wpfep-settings' ) . '">Settings</a>',
		 );
		return array_merge( $links, $mylinks );
	}
}
/**
 * Returns the singleton instance
 *
 * @return \WP_Frontend_Profile
 */
function wpfep() {
    return WP_Frontend_Profile::init();
}

// kickoff
wpfep();
/* When plugin is activated */
register_activation_hook(__FILE__,'Install_wpfep_time');

function Install_wpfep_time() {
	if (get_option("wpfep_Install_Time") == "") {
		update_option("wpfep_Install_Time", time());
	}
}
add_action('admin_notices', 'Wpfep_Error_Notices');

//REVIEW ASK
function Wpfep_Hide_Review_Ask(){   
    $Ask_Review_Date = sanitize_text_field($_POST['Ask_Review_Date']);

    if (get_option('wpfep_Ask_Review_Date') < time()+3600*24*$Ask_Review_Date) {
    	update_option('wpfep_Ask_Review_Date', time()+3600*24*$Ask_Review_Date);
    }

    die();
}
add_action('wp_ajax_wpfep_hide_review_ask','Wpfep_Hide_Review_Ask');

//feeback mail
function Wpfep_Send_Feedback() {   
	$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";  
    $Feedback = sanitize_text_field($_POST['Feedback']);
    $Feedback .= '<br /><br />Email Address: ';
    $Feedback .= sanitize_text_field($_POST['EmailAddress']);

    wp_mail('info@glowlogix.com', 'WP Frontend Profile Plugin Feedback', $Feedback, $headers);

    die();
}
add_action('wp_ajax_wpfep_send_feedback','Wpfep_Send_Feedback');

/**
 * function wp_frontend_profile_output()
 *
 * provides the front end output for the front end profile editing
 */
function wpfep_show_profile() {
	
	/* first things first - if no are not logged in move on! */
	if( ! is_user_logged_in() ){
		echo "<div class='wpfep-login-alert'>";
		printf( __( "This page is restricted. Please %s to view this page.", 'wpptm' ), wp_loginout( '', false ) );
		echo "</div>";
		return;
	}
	
	/* if you're an admin - too risky to allow front end editing */
	if( current_user_can( 'manage_options' ) )
		return;

	?>
	
	<div class="wpfep-wrapper">
		
		<?php
			
			/* get the tabs that have been added - see below */
			$wpfep_tabs = apply_filters(
				'wpfep_tabs',
				array()	
			);
			
			/**
			 * @hook wpfep_before_tabs
			 * fires before the tabs list items are outputted
			 * @param (array) $tabs is all the tabs that have been added
			 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
			 */
			do_action( 'wpfep_before_tabs', $wpfep_tabs, get_current_user_id() );	
			
		?>
		
		<ul class="wpfep-tabs" id="wpfep-tabs">
			
			<?php
				
				/**
				* set an array of tab titles and ids
				* the id set here should match the id given to the content wrapper
				* which has the class tab-content included in the callback function
				* @hooked wpfep_add_profile_tab - 10
				* @hooked wpfep_add_password_tab - 20
				*/
				$wpfep_tabs = apply_filters(
					'wpfep_tabs',
					array()
				);
				
				/* check we have items to show */
				if( ! empty( $wpfep_tabs ) ) {

					/* loop through each item */
					foreach( $wpfep_tabs as $wpfep_tab ) {
						
						/* output the tab name as a tab */
						wpfep_tab_list_item( $wpfep_tab );

					}

				}
				
			?>	
			
		</ul><!-- // wpfep-tabs -->
		
		<?php
			global $wp;			
			/* loop through each item */
			foreach( $wpfep_tabs as $wpfep_tab ) {
				
				/* build the content class */
				$content_class = '';
				
				/* if we have a class provided */
				if( $wpfep_tab[ 'content_class' ] != '' ) {
					
					/* add the content class to our variable */
					$content_class .= ' ' . $wpfep_tab[ 'content_class' ];
					
				}
				
				/**
				 * @hook wpfep_before_tab_content
				 * fires before the contents of the tab are outputted
				 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
				 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
				 */
				do_action( 'wpfep_before_tab_content', $wpfep_tab[ 'id' ], get_current_user_id() );

				?>
				
				<div class="tab-content<?php echo esc_attr( $content_class ); ?>" id="<?php echo esc_attr( $wpfep_tab[ 'id' ] ); ?>">
					
					<form method="post" action="<?php echo home_url( $wp->request.'/#'.esc_attr( $wpfep_tab[ 'id' ] ) ); ?>" class="wpfep-form-<?php echo esc_attr( $wpfep_tab[ 'id' ] ); ?>">
						
						<?php
							
							/* check if callback function exists */
							if(  isset($wpfep_tab[ 'callback' ]) && function_exists( $wpfep_tab[ 'callback' ] ) ) {
								
								/* use custom callback function */
								$wpfep_tab[ 'callback' ]( $wpfep_tab );
							
							/* custom callback does not exist */
							} else {
								
								/* use default callback function */
								wpfep_default_tab_content( $wpfep_tab );
								
							}
						
						?>
						
						<?php
							
							wp_nonce_field(
								'wpfep_nonce_action',
								'wpfep_nonce_name'
							);
						
						?>
					
					</form>
					
				</div>
				
				<?php
						
				/**
				 * @hook wpfep_after_tab_content
				 * fires after the contents of the tab are outputted
				 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
				 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
				 */
				do_action( 'wpfep_after_tab_content', $wpfep_tab[ 'id' ], get_current_user_id() );		
				
			} // end tabs loop

		?>
	
	</div><!-- // wpfep-wrapper -->
		
	<?php
	
}