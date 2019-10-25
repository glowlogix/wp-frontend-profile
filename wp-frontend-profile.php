<?php
/**
Plugin Name: WP Frontend Profile
Plugin URI: https://wordpress.org/plugins/wp-front-end-profile/
Description: This plugin allows users to easily edit their profile information on the frontend rather than having to go into the dashboard to make changes to password, email address and other user meta data.
Version:     1.0.0
Author:      Glowlogix
Author URI:  https://www.glowlogix.com
Text Domain: wpfep
License:     GPL v2 or later
 *
 * @package WP Frontend Profile
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for WP Frontend Profile
 *
 * @package WP Frontend Profile
 */
if ( ! defined( 'WPFEP_VERSION' ) ) {
	define( 'WPFEP_VERSION', '1.0.0' );
}
if ( ! defined( 'WPFEP_PATH' ) ) {
	define( 'WPFEP_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPFEP_PLUGIN_URL' ) ) {
	define( 'WPFEP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'WP_Frontend_Profile' ) ) :
	/**
	 * WP_Frontend_Profile main class
	 *
	 * @author Glowlogix
	 */
	class WP_Frontend_Profile {
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
			add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );
			add_action( 'admin_notices', 'Wpfep_Error_Notices' );

			/* When plugin is activated */
			register_activation_hook( __FILE__, array( &$this, 'wpfep_install_time' ) );
		}
		/**
		 * Include the required files
		 *
		 * @return void
		 */
		public function includes() {
			require_once WPFEP_PATH . '/functions/scripts.php';
			require_once WPFEP_PATH . '/functions/default-fields.php';
			require_once WPFEP_PATH . '/functions/tabs.php';
			require_once WPFEP_PATH . '/functions/wpfep-functions.php';
			require_once WPFEP_PATH . '/functions/save-fields.php';
			require_once WPFEP_PATH . '/functions/shortcode.php';
			require_once WPFEP_PATH . '/functions/feedback.php';
			require_once WPFEP_PATH . '/inc/class-wpfep-user.php';

			if ( is_admin() ) {
				require_once WPFEP_PATH . '/admin/class-wpfep-admin-installer.php';
				require_once WPFEP_PATH . '/admin/class-wpfep-admin-settings.php';
				require_once WPFEP_PATH . '/admin/class-wpfep-shortcodes-button.php';
				require_once WPFEP_PATH . '/admin/class-wpfep-admin-help.php';
				require_once WPFEP_PATH . '/admin/class-wpfep-system-status.php';
			} else {
				require_once WPFEP_PATH . '/inc/class-wpfep-registration.php';
				require_once WPFEP_PATH . '/inc/class-wpfep-login.php';
				require_once WPFEP_PATH . '/inc/class-wpfep-profile.php';
				require_once WPFEP_PATH . '/inc/class-wpfep-captcha-recaptcha.php';
			}
		}

		/**
		 * Instantiate the classes
		 *
		 * @return void
		 */
		public function instantiate() {
			if ( is_admin() ) {
				$this->container['settings']        = WPFEP_Admin_Settings::init();
				$this->container['admin_installer'] = new WPFEP_Admin_Installer();
				$this->container['System_Status']   = new Wpfep_System_Status();
			} else {
				$this->container['registration'] = WPFEP_Registration::init();
				$this->container['login']        = WPFEP_Login::init();
				$this->container['profile']      = WPFEP_Profile::init();
				$this->container['captcha']      = WPFEP_Captcha_Recaptcha::initialize();
			}
		}

		/**
		 * Load the translation file for current language.
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'wpfep', false, WPFEP_PATH . '/languages/' );
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
		 * Plugin action links.
		 *
		 * @param array $links setting page links.
		 *
		 * @since  1.0.0
		 */
		public function plugin_action_links( $links ) {

			$mylinks = array(
				'<a href="' . admin_url( 'admin.php?page=wpfep-settings' ) . '">Settings</a>',
			);
			return array_merge( $links, $mylinks );
		}

		/**
		 * Show/hide admin bar to the permitted user level
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show Whether to allow the admin bar to show.
		 *
		 * @return bool Whether the admin bar should be showing.
		 */
		public function show_admin_bar( $show ) {

			if ( ! is_user_logged_in() ) {
				return false;
			}

			$roles        = wpfep_get_option( 'show_admin_bar_to_roles', 'wpfep_general', array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) );
			$roles        = $roles ? $roles : array();
			$current_user = wp_get_current_user();

			if ( isset( $current_user->roles[0] ) ) {
				if ( ! in_array( $current_user->roles[0], $roles ) ) {
					return false;
				}
			}
			return $show;
		}

		/**
		 * Add links to plugin's description in plugins table
		 *
		 * @since 1.0.0
		 *
		 * @param array  $links  Initial list of links.
		 * @param string $file   Basename of current plugin.
		 *
		 * @return array
		 */
		public function plugin_meta_links( $links, $file ) {
			if ( plugin_basename( __FILE__ ) !== $file ) {
				return $links;
			}
			$support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/" title="' . __( 'Get help', 'wpptm' ) . '">' . __( 'Support', 'wpptm' ) . '</a>';
			$rate_link    = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-front-end-profile/reviews/#new-post" title="' . __( 'Rate the plugin', 'wpptm' ) . '">' . __( 'Rate the plugin ★★★★★', 'wpptm' ) . '</a>';

			$links[] = $support_link;
			$links[] = $rate_link;
			return $links;
		}

		/**
		 * Update plugin install time if not set
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function wpfep_install_time() {
			if ( false === get_option( 'wpfep_install_time' ) ) {
				update_option( 'wpfep_install_time', time() );
			}
		}
	}
	WP_Frontend_Profile::init();
endif;

/**
 * Function wp_frontend_profile_output()
 *
 * Provides the frontend output for the frontend profile editing
 */
function wpfep_show_profile() {

	/* First things first - if no are not logged in move on! */
	if ( ! is_user_logged_in() ) {
		echo "<div class='wpfep-login-alert'>";
		/* translators: %s: Login link */
		printf( esc_html__( 'This page is restricted. Please %s to view this page.', 'wpfep' ), wp_loginout( '', false ) );
		echo '</div>';
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( 'administrator', (array) $user->roles ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			ob_start();
		}
		echo "<div class='wpfep_editing_disabled'>";
		printf( esc_html__( 'Frontend editing is disabled for administrators because of security risks.', 'wpfep' ) );
		echo '</div>';
		return ob_get_clean();
	}
	/* if you're an admin - too risky to allow frontend editing */
	?>
	
	<div class="wpfep-wrapper">
		<?php
			/* get the tabs that have been added - see below */
			$wpfep_tabs = apply_filters(
				'wpfep_tabs',
				array()
			);

			/**
			 * Hook before tab content.
			 *
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
				 * Set an array of tab titles and ids
				 * the id set here should match the id given to the content wrapper
				 * which has the class tab-content included in the callback function
				 *
				 * @hooked wpfep_add_profile_tab - 10
				 * @hooked wpfep_add_password_tab - 20
				 */
				$wpfep_tabs = apply_filters(
					'wpfep_tabs',
					array()
				);
				/* check we have items to show */
			if ( ! empty( $wpfep_tabs ) ) {
				/* loop through each item */
				foreach ( $wpfep_tabs as $wpfep_tab ) {
					/* output the tab name as a tab */
					wpfep_tab_list_item( $wpfep_tab );
				}
			}
			?>
		</ul><!-- // wpfep-tabs -->
		
		<?php
			global $wp;
			/* loop through each item */
		foreach ( $wpfep_tabs as $wpfep_tab ) {

			/* build the content class */
			$content_class = '';

			/* if we have a class provided */
			if ( '' != $wpfep_tab['content_class'] ) {
				/* add the content class to our variable */
				$content_class .= ' ' . $wpfep_tab['content_class'];
			}

			/**
			 * Hook before tab content.
			 *
			 * @hook wpfep_before_tab_content
			 * fires before the contents of the tab are outputted
			 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
			 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
			 */
			do_action( 'wpfep_before_tab_content', $wpfep_tab['id'], get_current_user_id() );
			?>
				
				<div class="tab-content<?php echo esc_attr( $content_class ); ?>" id="<?php echo esc_attr( $wpfep_tab['id'] ); ?>">
					<form method="post" action="<?php echo esc_attr( get_edit_profile_page() ) . '#' . esc_attr( $wpfep_tab['id'] ); ?>" class="wpfep-form-<?php echo esc_attr( $wpfep_tab['id'] ); ?>">
					<?php
						/* check if callback function exists */
					if ( isset( $wpfep_tab['callback'] ) && function_exists( $wpfep_tab['callback'] ) ) {
						/* use custom callback function */
						$wpfep_tab['callback']( $wpfep_tab );
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
				 * Get current user id.
				 *
				 * @hook wpfep_after_tab_content
				 * fires after the contents of the tab are outputted
				 * @param (string) $tab_id the id of the tab being displayed. This can be used to target a particular tab.
				 * @param (int) $current_user_id the user if of the current user to add things targetted to a specific user only.
				 */
				do_action( 'wpfep_after_tab_content', $wpfep_tab['id'], get_current_user_id() );
		} // end tabs loop
		?>
	</div><!-- // wpfep-wrapper -->
	<?php
}

/**
 * Get edit profile page url
 *
 * @return boolean|string
 */
function get_edit_profile_page() {
	$page_id = wpfep_get_option( 'profile_edit_page', 'wpfep_pages', false );

	if ( ! $page_id ) {
		return false;
	}

	$url = get_permalink( $page_id );

	return apply_filters( 'wpfep_profile_edit_url', $url, $page_id );
}
