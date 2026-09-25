<?php
/**
 * Front-end user role management.
 *
 * @package wp-front-end-profile
 */

defined( 'ABSPATH' ) || exit;

class WPFEP_User_Role_Manager {
	const PER_PAGE = 20;

	public function __construct() {
		add_shortcode( 'wpfep_manage_roles', array( $this, 'render' ) );
		add_action( 'init', array( $this, 'create_user' ) );
		add_action( 'wp_ajax_wpfep_update_role', array( $this, 'update_role' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Load the manager assets on the front end.
	 *
	 * The shortcode can be rendered by a page builder or widget, where it is not
	 * present in $post->post_content. Loading these small, namespaced assets on
	 * every front-end page keeps the manager styled in all supported placements.
	 */
	public function enqueue_assets() {
		$style_version  = WPFEP_VERSION . '-' . filemtime( WPFEP_PATH . 'assets/css/role-manager.css' );
		$script_version = WPFEP_VERSION . '-' . filemtime( WPFEP_PATH . 'assets/js/role-manager.js' );

		wp_enqueue_style( 'wpfep-role-manager', WPFEP_PLUGIN_URL . 'assets/css/role-manager.css', array(), $style_version );
		wp_enqueue_script( 'wpfep-role-manager', WPFEP_PLUGIN_URL . 'assets/js/role-manager.js', array(), $script_version, true );
		wp_localize_script( 'wpfep-role-manager', 'wpfepRoleManager', $this->get_script_data() );
	}

	
	/** @return array<string, string> */
	private function get_script_data() {
		return array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'wpfep_role_change' ),
			'addUserText'      => __( 'Add User', 'wpfep' ),
			'hideAddUserText'  => __( 'Hide Add User', 'wpfep' ),
			'genericErrorText' => __( 'The role could not be updated. Please try again.', 'wpfep' ),
		);
	}

	/** @return array */
	private function get_frontend_roles() {
		// get_editable_roles() is only available in wp-admin/includes/user.php.
		$roles = apply_filters( 'editable_roles', wp_roles()->roles );
		unset( $roles['administrator'] );
		return $roles;
	}

	/** @return string */
	private function get_redirect_url() {
		$referer = wp_get_referer();
		return $referer ? $referer : home_url( '/' );
	}

	/** @return string */
	public function render() {
		if ( ! current_user_can( 'list_users' ) ) {
			return '<p>' . esc_html__( 'Access denied.', 'wpfep' ) . '</p>';
		}

		$roles       = $this->get_frontend_roles();
		$search      = isset( $_GET['wpfep_user_search'] ) ? sanitize_text_field( wp_unslash( $_GET['wpfep_user_search'] ) ) : '';
		$page_number = isset( $_GET['wpfep_role_page'] ) ? max( 1, absint( $_GET['wpfep_role_page'] ) ) : 1;
		$query_args  = array(
			'number' => self::PER_PAGE, 'offset' => ( $page_number - 1 ) * self::PER_PAGE,
			'orderby' => 'display_name', 'order' => 'ASC', 'count_total' => true,
		);
		if ( '' !== $search ) {
			$query_args['search'] = '*' . $search . '*';
			$query_args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}
		$user_query  = new WP_User_Query( $query_args );
		$users       = $user_query->get_results();
		$total_users = (int) $user_query->get_total();
		$total_pages = max( 1, (int) ceil( $total_users / self::PER_PAGE ) );
		if ( $page_number > $total_pages ) {
			$page_number = $total_pages;
		}

		ob_start();
		?>
		<div class="wpfep-role-manager">
			<h2><?php esc_html_e( 'User Role Management', 'wpfep' ); ?></h2>
			<?php if ( isset( $_GET['wpfep_user_created'] ) && '1' === $_GET['wpfep_user_created'] ) : ?>
				<p class="wpfep-success wpfep-role-notice" role="status"><?php esc_html_e( 'User created successfully.', 'wpfep' ); ?></p>
			<?php endif; ?>
			<?php if ( isset( $_GET['wpfep_user_error'] ) ) : ?>
				<p class="wpfep-error wpfep-role-notice" role="alert"><?php echo esc_html( wp_unslash( $_GET['wpfep_user_error'] ) ); ?></p>
			<?php endif; ?>

			<?php if ( current_user_can( 'create_users' ) ) : ?>
				<button type="button" class="wpfep-add-user-toggle" aria-expanded="false" aria-controls="wpfep-add-user-panel"><?php esc_html_e( 'Add User', 'wpfep' ); ?></button>
				<div id="wpfep-add-user-panel" class="wpfep-add-user-panel" hidden>
					<form method="post" class="wpfep-add-user-form">
						<h3><?php esc_html_e( 'Add New User', 'wpfep' ); ?></h3>
						<p><label for="wpfep_new_user_login"><?php esc_html_e( 'Username', 'wpfep' ); ?></label><input type="text" name="wpfep_new_user_login" id="wpfep_new_user_login" required></p>
						<p><label for="wpfep_new_user_email"><?php esc_html_e( 'Email', 'wpfep' ); ?></label><input type="email" name="wpfep_new_user_email" id="wpfep_new_user_email" required></p>
						<p><label for="wpfep_new_user_password"><?php esc_html_e( 'Password', 'wpfep' ); ?></label><input type="password" name="wpfep_new_user_password" id="wpfep_new_user_password" required></p>
						<p><label for="wpfep_new_user_role"><?php esc_html_e( 'Role', 'wpfep' ); ?></label><select name="wpfep_new_user_role" id="wpfep_new_user_role" required><?php foreach ( $roles as $role_slug => $role ) : ?><option value="<?php echo esc_attr( $role_slug ); ?>"><?php echo esc_html( translate_user_role( $role['name'] ) ); ?></option><?php endforeach; ?></select></p>
						<?php wp_nonce_field( 'wpfep_create_frontend_user', 'wpfep_create_user_nonce' ); ?>
						<input type="hidden" name="wpfep_create_frontend_user" value="1"><button type="submit"><?php esc_html_e( 'Add User', 'wpfep' ); ?></button>
					</form>
				</div>
			<?php endif; ?>

			<form method="get" class="wpfep-user-search-form">
				<label class="screen-reader-text" for="wpfep-user-search"><?php esc_html_e( 'Search users', 'wpfep' ); ?></label>
				<input type="search" id="wpfep-user-search" name="wpfep_user_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by name, username, or email…', 'wpfep' ); ?>"><button type="submit"><?php esc_html_e( 'Search', 'wpfep' ); ?></button>
			</form>

			<table class="wpfep-table"><thead><tr><th><?php esc_html_e( 'User', 'wpfep' ); ?></th><th><?php esc_html_e( 'Email', 'wpfep' ); ?></th><th><?php esc_html_e( 'Role', 'wpfep' ); ?></th><th><?php esc_html_e( 'Change Role', 'wpfep' ); ?></th></tr></thead><tbody>
				<?php if ( empty( $users ) ) : ?><tr><td colspan="4"><?php esc_html_e( 'No users found.', 'wpfep' ); ?></td></tr><?php endif; ?>
				<?php foreach ( $users as $user ) : ?>
					<?php $role_slug = isset( $user->roles[0] ) ? $user->roles[0] : ''; ?>
					<tr><td><?php echo esc_html( $user->display_name ); ?></td><td><?php echo esc_html( $user->user_email ); ?></td><td><span class="wpfep-role-badge wpfep-role-<?php echo esc_attr( sanitize_html_class( $role_slug ) ); ?>"><?php echo esc_html( isset( $roles[ $role_slug ] ) ? translate_user_role( $roles[ $role_slug ]['name'] ) : $role_slug ); ?></span></td><td>
						<?php if ( current_user_can( 'promote_users' ) && $user->ID !== get_current_user_id() && ! in_array( 'administrator', (array) $user->roles, true ) ) : ?>
							<select class="wpfep-role-select" data-user-id="<?php echo esc_attr( $user->ID ); ?>" data-current-role="<?php echo esc_attr( $role_slug ); ?>"><?php foreach ( $roles as $available_slug => $available_role ) : ?><option value="<?php echo esc_attr( $available_slug ); ?>" <?php selected( $role_slug, $available_slug ); ?>><?php echo esc_html( translate_user_role( $available_role['name'] ) ); ?></option><?php endforeach; ?></select>
						<?php else : ?><span class="wpfep-role-locked"><?php esc_html_e( 'Locked', 'wpfep' ); ?></span><?php endif; ?>
					</td></tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php if ( $total_pages > 1 ) : ?><nav class="wpfep-pagination" aria-label="<?php esc_attr_e( 'User pagination', 'wpfep' ); ?>"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'wpfep_role_page', '%#%' ), 'format' => '', 'current' => $page_number, 'total' => $total_pages, 'add_args' => array( 'wpfep_user_search' => $search ) ) ) ); ?></nav><?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/** Create a submitted front-end user. */
	public function create_user() {
		if ( ! isset( $_POST['wpfep_create_frontend_user'] ) ) {
			return;
		}
		$valid_nonce = isset( $_POST['wpfep_create_user_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpfep_create_user_nonce'] ) ), 'wpfep_create_frontend_user' );
		if ( ! current_user_can( 'create_users' ) || ! current_user_can( 'promote_users' ) || ! $valid_nonce ) {
			wp_die( esc_html__( 'You are not allowed to create users.', 'wpfep' ), 403 );
		}
		$username = isset( $_POST['wpfep_new_user_login'] ) ? sanitize_user( wp_unslash( $_POST['wpfep_new_user_login'] ) ) : '';
		$email    = isset( $_POST['wpfep_new_user_email'] ) ? sanitize_email( wp_unslash( $_POST['wpfep_new_user_email'] ) ) : '';
		$password = isset( $_POST['wpfep_new_user_password'] ) ? wp_unslash( $_POST['wpfep_new_user_password'] ) : '';
		$role     = isset( $_POST['wpfep_new_user_role'] ) ? sanitize_key( wp_unslash( $_POST['wpfep_new_user_role'] ) ) : '';
		$roles    = $this->get_frontend_roles();
		if ( '' === $username || '' === $email || '' === $password || ! is_email( $email ) ) {
			$this->redirect_with_error( __( 'Please provide a valid username, email, and password.', 'wpfep' ) );
		}
		if ( ! isset( $roles[ $role ] ) ) {
			$this->redirect_with_error( __( 'Please select a valid role.', 'wpfep' ) );
		}
		$user_id = wp_insert_user( array( 'user_login' => $username, 'user_email' => $email, 'user_pass' => $password, 'role' => $role ) );
		if ( is_wp_error( $user_id ) ) {
			$this->redirect_with_error( $user_id->get_error_message() );
		}
		wp_safe_redirect( add_query_arg( 'wpfep_user_created', '1', remove_query_arg( 'wpfep_user_error', $this->get_redirect_url() ) ) );
		exit;
	}

	/** @param string $message Error message. */
	private function redirect_with_error( $message ) {
		wp_safe_redirect( add_query_arg( 'wpfep_user_error', rawurlencode( $message ), $this->get_redirect_url() ) );
		exit;
	}

	/** Update a user's primary role via AJAX. */
	public function update_role() {
		check_ajax_referer( 'wpfep_role_change', 'nonce' );
		if ( ! current_user_can( 'promote_users' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to change user roles.', 'wpfep' ) ), 403 );
		}
		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$role    = isset( $_POST['role'] ) ? sanitize_key( wp_unslash( $_POST['role'] ) ) : '';
		$roles   = $this->get_frontend_roles();
		$user    = get_userdata( $user_id );
		if ( ! $user || $user->ID === get_current_user_id() || in_array( 'administrator', (array) $user->roles, true ) || ! isset( $roles[ $role ] ) ) {
			wp_send_json_error( array( 'message' => __( 'This role change is not allowed.', 'wpfep' ) ), 403 );
		}
		$user->set_role( $role );
		wp_send_json_success( array( 'role' => $role, 'roleName' => translate_user_role( $roles[ $role ]['name'] ) ) );
	}
}
