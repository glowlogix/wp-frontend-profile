<?php
/**
 * Add some content to the help tab
 *
 * @package     wpptm/Admin
 * @version     2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'WPFEP_Admin_Help', false ) ) {
	return new WPFEP_Admin_Help();
}
/**
 * WPFEP_Admin_Help Class.
 */
class WPFEP_Admin_Help {
	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
	}
	/**
	 * Add help tabs.
	 */
	public function add_tabs() {
		$screen = get_current_screen();
		if (  $screen->id == 'frontend-profile_page_wpfep-settings' || $screen->id == 'frontend-profile_page_wpfep-tools' || $screen->id == 'frontend-profile_page_wpfep-status') {
			$screen->add_help_tab(
				array(
					'id'      => 'wpfep_support_tab',
					'title'   => __( 'Help &amp; Support', 'wpptm' ),
					'content' =>
						'<h2>' . __( 'Help &amp; Support', 'wpptm' ) . '</h2>' .
						'<p>' . sprintf(
							/* translators: %s: Documentation URL */
							__( 'Should you need help understanding, using, or extending WP Frontend Profile, <a href="%s">please read our documentation</a>.', 'wpptm' ),
							'https://github.com/glowlogix/wp-frontend-profile/wiki'
						) . '</p>' .
						'<p>' . sprintf(
							/* translators: %s: Forum URL */
							__( 'For further assistance with WP Frontend Profile core you can use the <a href="%1$s">community forum</a>. ', 'wpptm' ),
							'https://wordpress.org/support/plugin/wp-front-end-profile/'
						) . '</p>' .
						'<p> <a href="https://wordpress.org/support/plugin/wp-front-end-profile/" class="button">' . __( 'Community forum', 'wpptm' ) . '</a> </p>',
				)
			);
			$screen->add_help_tab(
				array(
					'id'      => 'wpfep_bugs_tab',
					'title'   => __( 'Found a bug?', 'wpptm' ),
					'content' =>
						'<h2>' . __( 'Found a bug?', 'wpptm' ) . '</h2>' .
						/* translators: 1: GitHub issues URL 2: GitHub contribution guide URL 3: System status report URL */
						'<p>' . sprintf( __( 'If you find a bug within WP Frontend Profile core you can create a ticket via <a href="%1$s">Github issues</a>.', 'wpptm' ), 'https://github.com/glowlogix/wp-frontend-profile/issues?q=is%3Aopen' ) . '</p>' .
						'<p><a href="https://github.com/glowlogix/wp-frontend-profile/issues/new?assignees=&labels=bug&template=1-bug-report.md&title=" class="button button-primary">' . __( 'Report a bug', 'wpptm' ) . '</a> </p>',
				)
			);
		}
	}
}
return new WPFEP_Admin_Help();