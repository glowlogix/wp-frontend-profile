<?php
/**
 * User Role Editor.
 */
class WPFEP_Roles_Editor
{
    public function __construct()
    {
        // To Create Roles Editor CPT
        add_action('init', [ $this, 'create_roles_editor_cpt' ]);

        // To Create a Roles Editor CPT post for every existing role
        add_action('current_screen', [ $this, 'create_post_for_role' ]);

        // For Edit CPT page
        add_filter('manage_wpfep-roles-editor_posts_columns', [ $this, 'add_extra_column_for_roles_editor_cpt' ]);
        add_action('manage_wpfep-roles-editor_posts_custom_column', [ $this, 'custom_column_content_for_roles_editor_cpt' ], 10, 2);

        // To Add and remove meta boxes
        add_action('add_meta_boxes', [ $this, 'register_meta_boxes' ], 1);

        // To Edit Publish meta box
        add_action('post_submitbox_misc_actions', [ $this, 'edit_publish_meta_box' ]);

        // To Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [ $this, 'scripts_admin' ]);

        // For Add role slug to the created post
        add_action('save_post', [ $this, 'add_post_meta' ], 10, 2);

        add_filter('wp_insert_post_data', [ $this, 'modify_post_title' ], '99', 1);

        // add_action( 'wp_ajax_delete_capability_permanently', array( $this, 'delete_capability_permanently' ) );
        add_action('wp_ajax_update_role_capabilities', [ $this, 'update_role_capabilities' ]);
        add_action('wp_ajax_get_role_capabilities', [ $this, 'get_role_capabilities' ]);

        add_filter('months_dropdown_results', [ $this, 'remove_filter_by_month_dropdown' ], 10, 2);
        add_filter('post_row_actions', [ $this, 'modify_list_row_actions' ], 10, 2);

        add_filter('bulk_actions-edit-wpfep-roles-editor', '__return_empty_array');
        add_filter('views_edit-wpfep-roles-editor', [ $this, 'edit_cpt_quick_links' ]);

        add_filter('enter_title_here', [ $this, 'change_title_text' ]);
        add_filter('post_updated_messages', [ $this, 'change_post_updated_messages' ]);
        add_action('before_delete_post', [ $this, 'delete_role_permanently' ], 10);

        // To Add multiple roles checkbox to back-end Add / Edit User (as admin)
        add_action('load-user-new.php', [ $this, 'actions_on_user_new' ]);
    }

    public function scripts_admin()
    {
        global $post_type;
        global $current_screen;
        global $post;
        global $wp_scripts;
        global $wp_styles;

        if ('wpfep-roles-editor' == $post_type) {
            $wp_default_scripts = $this->wp_default_scripts();
            $scripts_exceptions = [ 'wpfep-sitewide', 'acf-field-group', 'acf-pro-field-group', 'acf-input', 'acf-pro-input' ];
            foreach ($wp_scripts->registered as $key => $value) {
                if (! in_array($key, $wp_default_scripts) && ! in_array($key, $scripts_exceptions)) {
                    wp_deregister_script($key);
                }
            }

            $wp_default_styles = $this->wp_default_styles();
            $styles_exceptions = [ 'wpfep-serial-notice-css', 'acf-global', 'wpfep-back-end-style' ];
            foreach ($wp_styles->registered as $key => $value) {
                if (! in_array($key, $wp_default_styles) && ! in_array($key, $styles_exceptions)) {
                    wp_deregister_style($key);
                }
            }

            wp_enqueue_script('wpfep_select2_js', WPFEP_PLUGIN_URL . 'assets/js/select2/select2.min.js');
            wp_enqueue_style('wpfep_select2_css', WPFEP_PLUGIN_URL . '/assets/css/role.css');

            wp_enqueue_script('wpfep_roles_editor_js', plugin_dir_url(__FILE__) . '../assets/js/roles-editor.js');
            wp_enqueue_style('wpfep_roles_editor_css', plugin_dir_url(__FILE__) . '../assets/css/roles-editor.css');

            if ('wpfep-roles-editor' == $current_screen->id) {
                $role_slug    = $this->sanitize_role(get_post_meta($post->ID, 'wpfep_role_slug', true));
                $current_role = get_role($role_slug);
                $current_user = wp_get_current_user();

                if (isset($current_role) && is_array($current_role->capabilities)) {
                    $role_capabilities = $current_role->capabilities;

                    // Logic conditionTrue if current user got this role
                    if (isset($role_slug) && in_array($role_slug, $current_user->roles)) {
                        $current_user_role = true;
                    } else {
                        $current_user_role = false;
                    }

                    // For Getting current role users count
                    $current_role_users_count = $this->count_role_users($current_role->name);
                } else {
                    $role_capabilities        = null;
                    $current_role_users_count = null;
                    $current_user_role        = false;
                }
            } else {
                $role_capabilities        = null;
                $current_role_users_count = null;
                $current_user_role        = false;
            }

            // For Remove old WordPress levels system
            // To Use filter and return FALSE if you need the old levels capability system
            $remove_old_levels = apply_filters('wpfep_remove_old_levels', true);
            if (true === $remove_old_levels) {
                $role_capabilities = $this->remove_old_labels($role_capabilities);
            }

            $admin_capabilities = [
                'manage_options',
                'activate_plugins',
                'delete_plugins',
                'install_plugins',
                'manage_network_options',
                'manage_network',
                'manage_network_plugins',
                'upload_plugins',
            ];

            $group_capabilities  = $this->group_capabilities();
            $hidden_capabilities = null;

            $remove_hidden_capabilities = apply_filters('wpfep_re_remove_hidden_caps', true);
            if (true === $remove_hidden_capabilities) {
                $group_capabilities['general']['capabilities']                  = array_diff($group_capabilities['general']['capabilities'], $this->get_hidden_capabilities());
                $group_capabilities['appearance']['capabilities']               = array_diff($group_capabilities['appearance']['capabilities'], $this->get_hidden_capabilities());
                $group_capabilities['plugins']['capabilities']                  = array_diff($group_capabilities['plugins']['capabilities'], $this->get_hidden_capabilities());
                $group_capabilities['post_types']['attachment']['capabilities'] = array_diff($group_capabilities['post_types']['attachment']['capabilities'], $this->get_hidden_capabilities());

                if (null !== $role_capabilities) {
                    $role_capabilities = array_diff_key($role_capabilities, $this->get_hidden_capabilities());
                }

                $hidden_capabilities = $this->get_hidden_capabilities();
                if (empty($hidden_capabilities)) {
                    $hidden_capabilities = null;
                }
            }

            $all_capabilities = $this->get_all_capabilities();

            $custom_capabilities = get_option('wpfep_roles_editor_capabilities', 'not_set');
            if ('not_set' != $custom_capabilities && ! empty($custom_capabilities['custom']['capabilities'])) {
                foreach ($custom_capabilities['custom']['capabilities'] as $custom_capability_key => $custom_capability) {
                    if (! in_array($custom_capability, $all_capabilities)) {
                        $all_capabilities[ $custom_capability ] = $custom_capability;
                    }
                }
            }

            $vars_array = [
                'ajaxUrl'                       => admin_url('admin-ajax.php'),
                'current_screen_action'         => $current_screen->action,
                'capabilities'                  => $group_capabilities,
                'current_role_capabilities'     => $role_capabilities,
                'current_role_users_count'      => $current_role_users_count,
                'all_capabilities'              => $all_capabilities,
                'current_user_role'             => $current_user_role,
                'admin_capabilities'            => $admin_capabilities,
                'hidden_capabilities'           => $hidden_capabilities,
                'default_role_text'             => esc_html__('Default Role', 'wpfep'),
                'your_role_text'                => esc_html__('Your Role', 'wpfep'),
                'role_name_required_error_text' => esc_html__('Role name is required.', 'wpfep'),
                'no_capabilities_found_text'    => esc_html__('No capabilities found.', 'wpfep'),
                'select2_placeholder_text'      => esc_html__('Select capabilities', 'wpfep'),
                'delete_permanently_text'       => esc_html__('Delete Permanently', 'wpfep'),
                'capability_perm_delete_text'   => esc_html__("This will permanently delete the capability from your site and from every user role.\n\nIt can't be undone!", 'wpfep'),
                'new_cap_update_title_text'     => esc_html__('This capability is not saved until you click Update!', 'wpfep'),
                'new_cap_publish_title_text'    => esc_html__('This capability is not saved until you click Publish!', 'wpfep'),
                'delete_text'                   => esc_html__('Delete', 'wpfep'),
                'cancel_text'                   => esc_html__('Cancel', 'wpfep'),
                'add_new_capability_text'       => esc_html__('Add New Capability', 'wpfep'),
                'capability_text'               => esc_html__('Capability', 'wpfep'),
                'cap_no_delete_text'            => esc_html__('You can\'t delete this capability from your role.', 'wpfep'),
            ];

            wp_localize_script('wpfep_roles_editor_js', 'wpfep_roles_editor_data', $vars_array);
        }
    }

    public function count_role_users($current_role_name)
    {
        // To Get current role users count
        $user_count = count_users();

        if (isset($user_count['avail_roles'][ $current_role_name ])) {
            $current_role_users_count = $user_count['avail_roles'][ $current_role_name ];
        } else {
            $current_role_users_count = null;
        }

        return $current_role_users_count;
    }

    public function get_role_capabilities()
    {
        if (! current_user_can('manage_options')) {
            die();
        }

        check_ajax_referer('wpfep-re-ajax-nonce', 'security');

        $role = get_role(sanitize_text_field($_POST['role']));

        if (isset($role) && ! empty($role->capabilities)) {
            $role_capabilities = $role->capabilities;

            // For Remove old WordPress levels system
            // To Use filter and return FALSE if you need the old levels capability system
            $remove_old_levels = apply_filters('wpfep_remove_old_levels', true);
            if (true === $remove_old_levels) {
                $role_capabilities = $this->remove_old_labels($role_capabilities);
            }

            die(json_encode($role_capabilities));
        }

        die('no_caps');
    }

    public function edit_cpt_quick_links($views)
    {
        $edited_views        = [];
        $edited_views['all'] = $views['all'];

        return $edited_views;
    }

    public function create_roles_editor_cpt()
    {
        if (is_admin() && current_user_can('manage_options')) {
            $labels = [
                'name'               => esc_html__('Roles Editor', 'wpfep'),
                'singular_name'      => esc_html__('Roles Editor', 'wpfep'),
                'add_new'            => esc_html__('Add New Role', 'wpfep'),
                'add_new_item'       => esc_html__('Add New Role', 'wpfep'),
                'edit_item'          => esc_html__('Edit Role', 'wpfep'),
                'new_item'           => esc_html__('New Role', 'wpfep'),
                'all_items'          => esc_html__('Roles Editor', 'wpfep'),
                'view_item'          => esc_html__('View Role', 'wpfep'),
                'search_items'       => esc_html__('Search the Roles Editor', 'wpfep'),
                'not_found'          => esc_html__('No roles found', 'wpfep'),
                'not_found_in_trash' => esc_html__('No roles found in trash', 'wpfep'),
                'name_admin_bar'     => esc_html__('Role', 'wpfep'),
                'parent_item_colon'  => '',
                'menu_name'          => esc_html__('Roles Editor', 'wpfep'),
            ];

            $args = [
                'labels'             => $labels,
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'query_var'          => true,
                'show_in_menu'       => 'users.php',
                'has_archive'        => false,
                'hierarchical'       => false,
                'capability_type'    => 'post',
                'supports'           => [ 'title' ],
            ];

            register_post_type('wpfep-roles-editor', $args);
        }
    }

    public function change_title_text($title)
    {
        $screen = get_current_screen();

        if ('wpfep-roles-editor' == $screen->post_type) {
            $title = esc_html__('Enter role name here', 'wpfep');
        }

        return $title;
    }

    public function change_post_updated_messages($messages)
    {
        global $post;

        $messages['wpfep-roles-editor'] = [
            0  => '',
            1  => esc_html__('Role updated.', 'wpfep'),
            2  => esc_html__('Custom field updated.', 'wpfep'),
            3  => esc_html__('Custom field deleted.', 'wpfep'),
            4  => esc_html__('Role updated.', 'wpfep'),
            5  => isset($_GET['revision']) ? sprintf(esc_html__('Role restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => esc_html__('Role created.', 'wpfep'),
            7  => esc_html__('Role saved.', 'wpfep'),
            8  => esc_html__('Role submitted.', 'wpfep'),
            9  => sprintf(esc_html__('Role scheduled for: <strong>%1$s</strong>', 'wpfep'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date))),
            10 => esc_html__('Role draft updated.', 'wpfep'),
        ];

        return $messages;
    }

    public function create_post_for_role()
    {
        $screen = get_current_screen();

        if ('edit-wpfep-roles-editor' == $screen->id) {
            global $wpdb;
            global $wp_roles;

            $added_posts = [];

            $args  = [
                'numberposts' => -1,
                'post_type'   => 'wpfep-roles-editor',
            ];
            $posts = get_posts($args);

            foreach ($posts as $key => $value) {
                $post_id        = intval($value->ID);
                $role_slug_meta = $this->sanitize_role(get_post_meta($post_id, 'wpfep_role_slug', true));
                if (! empty($role_slug_meta)) {
                    if (! array_key_exists($role_slug_meta, $wp_roles->role_names)) {
                        $post_meta = get_post_meta($post_id);
                        foreach ($post_meta as $post_meta_key => $post_meta_value) {
                            delete_post_meta($post_id, $post_meta_key);
                        }

                        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->posts WHERE post_type = %s AND ID = %d", 'wpfep-roles-editor', $post_id));
                    } else {
                        $added_posts[] = $role_slug_meta;
                    }
                }
            }

            foreach ($wp_roles->role_names as $role_slug => $role_display_name) {
                if (! in_array($role_slug, $added_posts)) {
                    $id = wp_insert_post(
                        [
                            'post_title'   => $role_display_name,
                            'post_type'    => 'wpfep-roles-editor',
                            'post_content' => '',
                            'post_status'  => 'publish',
                        ]
                    );

                    update_post_meta($id, 'wpfep_role_slug', $role_slug);
                }
            }
        }
    }

    public function add_extra_column_for_roles_editor_cpt($columns)
    {
        $columns = [
             'title'        => esc_html__('Role Name', 'wpfep'),
             'role'         => esc_html__('Role Slug', 'wpfep'),
             'capabilities' => esc_html__('Capabilities', 'wpfep'),
             'users'        => esc_html__('Users', 'wpfep'),
         ];

        return apply_filters('wpfep_manage_roles_columns', $columns);
    }

    public function custom_column_content_for_roles_editor_cpt($column_name, $post_id)
    {
        $role_slug = $this->sanitize_role(get_post_meta($post_id, 'wpfep_role_slug', true));

        if (isset($role_slug)) {
            $role = get_role($role_slug);

            if ('role' == $column_name) {
                echo '<input readonly spellcheck="false" type="text" class="wpfep-role-slug-input input" value="' . $role_slug . '" />';
            }

            if ('capabilities' == $column_name && isset($role)) {
                // To Remove old WordPress levels system
                // For Use filter and return FALSE if you need the old levels capability system
                $remove_old_levels = apply_filters('wpfep_remove_old_levels', true);
                if (true === $remove_old_levels) {
                    $role_capabilities = $this->remove_old_labels($role->capabilities);
                } else {
                    $role_capabilities = $role->capabilities;
                }

                echo count($role_capabilities);
            }

            if ('users' == $column_name && isset($role)) {
                $role_users_count = $this->count_role_users($role->name);

                if (! isset($role_users_count)) {
                    $role_users_count = 0;
                }

                echo $role_users_count;
            }
        }
    }

    public function register_meta_boxes()
    {
        remove_meta_box('slugdiv', 'wpfep-roles-editor', 'normal');
        add_meta_box('wpfep_edit_role_capabilities', esc_html__('Edit Role Capabilities', 'wpfep'), [ $this, 'edit_role_capabilities_meta_box_callback' ], 'wpfep-roles-editor', 'normal', 'high');
    }

    public function edit_role_capabilities_meta_box_callback() {        ?>

					<div id="wpfep-role-edit-table">
						<div class="wpfep-re-spinner-container"></div>
						<div id="wpfep-role-edit-caps-clear"></div>
					</div>
					<div id="wpfep-role-edit-divs-clear"></div>
				</div>

				<input type="hidden" id="wpfep-role-slug-hidden" name="wpfep-role-slug-hidden" value="">
				<input type="hidden" name="wpfep-re-ajax-nonce" id="wpfep-re-ajax-nonce" value="<?php echo wp_create_nonce('wpfep-re-ajax-nonce'); ?>" />
			</div>

			<?php
    }

    public function edit_publish_meta_box($post)
    {
        global $current_screen;

        $post_type = 'wpfep-roles-editor';

        if ($post->post_type == $post_type) {
            $role_slug = $this->sanitize_role(get_post_meta($post->ID, 'wpfep_role_slug', true)); ?>
				<style type="text/css">
					.misc-pub-section.misc-pub-post-status,
					.misc-pub-section.misc-pub-visibility,
					.misc-pub-section.curtime.misc-pub-curtime,
					#minor-publishing-actions,
					#major-publishing-actions #delete-action {
						display: none;
					}
				</style>



				<div class="misc-pub-section misc-pub-section-edit-slug">
					<span>
						<label for="wpfep-role-slug"><?php esc_html_e('Role Slug', 'wpfep'); ?>: </label>
						<input type="text" id="wpfep-role-slug" value="<?php echo 'add' == $current_screen->action ? '' : $role_slug; ?>" <?php echo 'add' == $current_screen->action ? '' : 'disabled'; ?>>
					</span>
				</div>
			  <?php
        }
    }

    public function remove_old_labels($capabilities)
    {
        $old_levels = [ 'level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'level_7', 'level_8', 'level_9', 'level_10' ];

        foreach ($old_levels as $key => $value) {
            unset($capabilities[ $value ]);
        }

        return $capabilities;
    }

    public function modify_post_title($data)
    {
        if ('wpfep-roles-editor' != $data['post_type'] || 'auto-draft' == $data['post_status']) {
            return $data;
        }

        if (! current_user_can('manage_options')) {
            return $data;
        }

        if (isset($data['post_title'])) {
            $data['post_title'] = wp_strip_all_tags($data['post_title']);
        }

        return $data;
    }

    public function add_post_meta($post_id, $post)
    {
        $post_type = get_post_type($post_id);

        if ('wpfep-roles-editor' != $post_type || 'auto-draft' == $post->post_status) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['wpfep-role-slug-hidden'])) {
            $role_slug = trim($_POST['wpfep-role-slug-hidden']);
            $role_slug = $this->sanitize_role($role_slug);

            update_post_meta($post_id, 'wpfep_role_slug', $role_slug);
        }
    }

    public function update_role_capabilities()
    {
        if (! current_user_can('manage_options')) {
            die();
        }

        check_ajax_referer('wpfep-re-ajax-nonce', 'security');

        $role_slug = $this->sanitize_role($_POST['role']);

        $role = get_role($role_slug);

        if ($role) {
            if (isset($_POST['new_capabilities'])) {
                foreach ($_POST['new_capabilities'] as $key => $value) {
                    $role->add_cap(sanitize_text_field($key));
                }
            }

            if (isset($_POST['capabilities_to_delete'])) {
                foreach ($_POST['capabilities_to_delete'] as $key => $value) {
                    $role->remove_cap(sanitize_text_field($key));
                }
            }
        } else {
            $capabilities = [];

            if (isset($_POST['all_capabilities'])) {
                foreach ($_POST['all_capabilities'] as $key => $value) {
                    $capabilities[ sanitize_text_field($key) ] = true;
                }
            }

            $role_display_name = sanitize_text_field($_POST['role_display_name']);

            add_role($role_slug, $role_display_name, $capabilities);
        }

        die('role_capabilities_updated');
    }

    public function group_capabilities()
    {
        $capabilities = get_option('wpfep_roles_editor_capabilities', 'not_set');

        if ('not_set' == $capabilities) {
            // For remove non-custom capabilities from this array later on
            //
            update_option('wpfep_roles_editor_capabilities', $capabilities);
        } else {
            $custom_capabilities = $this->get_all_capabilities();
            $custom_capabilities = $this->remove_old_labels($custom_capabilities);

            foreach ($capabilities['post_types'] as $key => $value) {
                $custom_capabilities = array_diff($custom_capabilities, $value['capabilities']);
            }

            foreach ($capabilities as $key => $value) {
                if ('post_types' != $key && 'custom' != $key) {
                    $custom_capabilities = array_diff($custom_capabilities, $value['capabilities']);
                }
            }

            $custom_capabilities = array_values($custom_capabilities);
            $custom_capabilities = array_unique($custom_capabilities);
            $custom_capabilities = array_diff($custom_capabilities, $capabilities['custom']['capabilities']);

            if (! empty($custom_capabilities)) {
                $capabilities['custom']['capabilities'] = array_merge($capabilities['custom']['capabilities'], $custom_capabilities);

                update_option('wpfep_roles_editor_capabilities', $capabilities);
            }
        }

        return $capabilities;
    }

    public function post_type_group_capabilities($post_type = 'post')
    {
        $capabilities = (array) get_post_type_object($post_type)->cap;

        unset($capabilities['edit_post']);
        unset($capabilities['read_post']);
        unset($capabilities['delete_post']);

        $capabilities = array_values($capabilities);

        if (! in_array($post_type, [ 'post', 'page' ])) {
            // Get the post and page capabilities
            $post_caps = array_values((array) get_post_type_object('post')->cap);
            $page_caps = array_values((array) get_post_type_object('page')->cap);

            // Remove post/page capabilities from the current post type capabilities
            $capabilities = array_diff($capabilities, $post_caps, $page_caps);
        }

        if ('attachment' === $post_type) {
            $capabilities[] = 'unfiltered_upload';
        }

        return array_unique($capabilities);
    }

    public function get_all_capabilities()
    {
        global $wp_roles;

        $capabilities = [];

        foreach ($wp_roles->role_objects as $key => $role) {
            if (is_array($role->capabilities)) {
                foreach ($role->capabilities as $capability => $granted) {
                    $capabilities[ $capability ] = $capability;
                }
            }
        }

        return array_unique($capabilities);
    }

    public function remove_filter_by_month_dropdown($months, $post_type = null)
    {
        if (isset($post_type) && 'wpfep-roles-editor' == $post_type) {
            return __return_empty_array();
        } else {
            return $months;
        }
    }

    public function modify_list_row_actions($actions, $post)
    {
        global $wp_roles;

        if ('wpfep-roles-editor' == $post->post_type) {
            $current_user = wp_get_current_user();
            $default_role = get_option('default_role');
            $role_slug    = get_post_meta($post->ID, 'wpfep_role_slug', true);

            $url     = admin_url('post.php?post=' . $post->ID);
            $actions = [];

            if (in_array($role_slug, $current_user->roles) && (! is_multisite() || (is_multisite() && ! is_super_admin())) && (! empty($wp_roles->roles[ $role_slug ]['capabilities']) && array_key_exists('manage_options', $wp_roles->roles[ $role_slug ]['capabilities']))) {
                $actions = array_merge(
                    $actions,
                    [
                        'delete_notify your_role' => '<span title="' . esc_html__('You can\'t delete your role.', 'wpfep') . '">' . esc_html__('Delete', 'wpfep') . '</span>',
                    ]
                );
            } elseif ($role_slug == $default_role) {
                $actions = array_merge(
                    $actions,
                    [
                        'default_role'  => sprintf(
                            '<a href="%s">%s</a>',
                            esc_url(admin_url('options-general.php#default_role')),
                            esc_html__('Change Default', 'wpfep')
                        ),
                        'delete_notify' => '<span title="' . esc_html__('You can\'t delete the default role. Change it first.', 'wpfep') . '">' . esc_html__('Delete', 'wpfep') . '</span>',
                    ]
                );
            } else {
                $delete_link = wp_nonce_url(add_query_arg([ 'action' => 'delete' ], $url), 'delete-post_' . $post->ID);

                $actions = array_merge(
                    $actions,
                    [
                        'delete' => sprintf(
                            '<a href="%1$s" onclick="return confirm( \'%2$s\' );">%3$s</a>',
                            esc_url($delete_link),
                            esc_html__('Are you sure?\nThis will permanently delete the role and cannot be undone!\nUsers assigned only on this role will be moved to the default role.', 'profile_builder'),
                            esc_html__('Delete', 'wpfep')
                        ),
                    ]
                );
            }
        }

        return $actions;
    }

    public function sanitize_role($role)
    {
        $role = strtolower($role);
        $role = wp_strip_all_tags($role);
        $role = preg_replace('/[^a-z0-9_\-\s]/', '', $role);
        $role = str_replace(' ', '_', $role);

        return $role;
    }

    public function delete_role_permanently($post_id)
    {
        global $post_type;
        if ('wpfep-roles-editor' != $post_type) {
            return;
        }

        check_admin_referer('delete-post_' . $post_id);

        $role_slug = get_post_meta($post_id, 'wpfep_role_slug', true);
        $role_slug = $this->sanitize_role($role_slug);

        $default_role = get_option('default_role');

        if ($role_slug == $default_role) {
            return;
        }

        $users = get_users([ 'role' => $role_slug ]);

        if (is_array($users)) {
            foreach ($users as $user) {
                if ($user->has_cap($role_slug) && count($user->roles) <= 1) {
                    $user->set_role($default_role);
                } elseif ($user->has_cap($role_slug)) {
                    $user->remove_role($role_slug);
                }
            }
        }

        remove_role($role_slug);
    }

    public function wp_default_scripts()
    {
        $wp_default_scripts = [
            'jquery',
            'jquery-core',
            'jquery-migrate',
            'jquery-ui-core',
            'jquery-ui-accordion',
            'jquery-ui-autocomplete',
            'jquery-ui-button',
            'jquery-ui-datepicker',
            'jquery-ui-dialog',
            'jquery-ui-draggable',
            'jquery-ui-droppable',
            'jquery-ui-menu',
            'jquery-ui-mouse',
            'jquery-ui-position',
            'jquery-ui-progressbar',
            'jquery-ui-resizable',
            'jquery-ui-selectable',
            'jquery-ui-slider',
            'jquery-ui-sortable',
            'jquery-ui-spinner',
            'jquery-ui-tabs',
            'jquery-ui-tooltip',
            'jquery-ui-widget',
            'underscore',
            'utils',
            'common',
            'wp-a11y',
            'sack',
            'quicktags',
            'colorpicker',
            'editor',
            'wp-fullscreen-stub',
            'wp-ajax-response',
            'wp-pointer',
            'heartbeat',
            'wp-auth-check',
            'wp-lists',
            'prototype',
            'scriptaculous-root',
            'scriptaculous-builder',
            'scriptaculous-dragdrop',
            'scriptaculous-effects',
            'scriptaculous-slider',
            'scriptaculous-sound',
            'scriptaculous-controls',
            'scriptaculous',
            'cropper',
            'jquery-ui-selectmenu',
            'jquery-form',
            'jquery-color',
            'schedule',
            'jquery-query',
            'jquery-serialize-object',
            'jquery-hotkeys',
            'jquery-table-hotkeys',
            'jquery-touch-punch',
            'suggest',
            'imagesloaded',
            'masonry',
            'jquery-masonry',
            'thickbox',
            'jcrop',
            'swfupload',
            'swfupload-swfobject',
            'swfupload-queue',
            'swfupload-speed',
            'swfupload-all',
            'swfupload-handlers',
            'comment-reply',
            'json2',
            'underscore',
            'backbone',
            'wp-util',
            'wp-backbone',
            'revisions',
            'mediaelement',
            'wp-mediaelement',
            'froogaloop',
            'wp-playlist',
            'zxcvbn-async',
            'password-strength-meter',
            'language-chooser',
            'user-suggest',
            'admin-bar',
            'wplink',
            'wpdialogs',
            'word-count',
            'media-upload',
            'hoverIntent',
            'customize-base',
            'customize-loader',
            'customize-preview',
            'customize-models',
            'customize-views',
            'customize-controls',
            'customize-selective-refresh',
            'customize-widgets',
            'customize-preview-widgets',
            'customize-preview-nav-menus',
            'wp-custom-header',
            'accordion',
            'shortcode',
            'media-models',
            'wp-embed',
            'mce-view',
            'wp-api',
            'admin-tags',
            'admin-comments',
            'xfn',
            'postbox',
            'tags-box',
            'tags-suggest',
            'post',
            'press-this',
            'editor-expand',
            'link',
            'comment',
            'admin-gallery',
            'admin-widgets',
            'theme',
            'inline-edit-post',
            'inline-edit-tax',
            'plugin-install',
            'updates',
            'dashboard',
            'list-revisions',
            'media-grid',
            'media',
            'image-edit',
            'nav-menu',
            'custom-header',
            'custom-background',
            'media-gallery',
            'customize-nav-menus',
        ];

        return $wp_default_scripts;
    }

    public function wp_default_styles()
    {
        $wp_default_styles = [
            'admin-bar',
            'colors',
            'ie',
            'wp-auth-check',
            'wp-jquery-ui-dialog',
            'wpfep-serial-notice-css',
            'common',
            'forms',
            'admin-menu',
            'dashboard',
            'list-tables',
            'edit',
            'revisions',
            'media',
            'themes',
            'about',
            'nav-menus',
            'widgets',
            'site-icon',
            'l10n',
            'wp-admin',
            'login',
            'install',
            'customize-controls',
            'customize-widgets',
            'customize-nav-menus',
            'press-this',
            'buttons',
            'dashicons',
            'editor-buttons',
            'media-views',
            'wp-pointer',
            'customize-preview',
            'wp-embed-template-ie',
            'colors-fresh',
            'open-sans',
        ];

        return $wp_default_styles;
    }

    public function get_hidden_capabilities()
    {
        $capabilities = [];

        if (is_multisite() || ! defined('ALLOW_UNFILTERED_UPLOADS') || ! ALLOW_UNFILTERED_UPLOADS) {
            $capabilities['unfiltered_upload'] = 'unfiltered_upload';
        }

        if (is_multisite() || (defined('DISALLOW_UNFILTERED_HTML') && DISALLOW_UNFILTERED_HTML)) {
            $capabilities['unfiltered_html'] = 'unfiltered_html';
        }

        if (is_multisite() || (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT)) {
            $capabilities['edit_files']   = 'edit_files';
            $capabilities['edit_plugins'] = 'edit_plugins';
            $capabilities['edit_themes']  = 'edit_themes';
        }

        if (is_multisite() || (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS)) {
            $capabilities['edit_files']      = 'edit_files';
            $capabilities['edit_plugins']    = 'edit_plugins';
            $capabilities['edit_themes']     = 'edit_themes';
            $capabilities['update_plugins']  = 'update_plugins';
            $capabilities['delete_plugins']  = 'delete_plugins';
            $capabilities['install_plugins'] = 'install_plugins';
            $capabilities['upload_plugins']  = 'upload_plugins';
            $capabilities['update_themes']   = 'update_themes';
            $capabilities['delete_themes']   = 'delete_themes';
            $capabilities['install_themes']  = 'install_themes';
            $capabilities['upload_themes']   = 'upload_themes';
            $capabilities['update_core']     = 'update_core';
        }

        return array_unique($capabilities);
    }

    // To Add actions on Add User back-end page
    public function actions_on_user_new()
    {
        $this->scripts_and_styles_actions('user_new');

        add_action('user_new_form', [ $this, 'roles_field_user_new' ]);

        add_action('user_register', [ $this, 'roles_update_user_new' ]);
    }

    // For Roles Edit checkboxes for Add User back-end page
    public function roles_field_user_new()
    {
        if (! current_user_can('promote_users')) {
            return;
        }

        $user_roles = apply_filters('wpfep_default_user_roles', [ get_option('default_role') ]);

        if (isset($_POST['createuser']) && ! empty($_POST['wpfep_re_user_roles'])) {
            $user_roles = array_map([ $this, 'sanitize_role' ], $_POST['wpfep_re_user_roles']);
        }

        wp_nonce_field('new_user_roles', 'wpfep_re_new_user_roles_nonce');

        $this->roles_field_display($user_roles);
    }

    public function roles_update_user_new($user_id)
    {
        if (! current_user_can('promote_users')) {
            return;
        }

        if (! isset($_POST['wpfep_re_new_user_roles_nonce']) || ! wp_verify_nonce($_POST['wpfep_re_new_user_roles_nonce'], 'new_user_roles')) {
            return;
        }

        $user = new \WP_User($user_id);

        $this->roles_update_user_new_and_edit($user);
    }

    public function roles_update_user_new_and_edit($user)
    {
        if (! empty($_POST['wpfep_re_user_roles'])) {
            $old_roles = (array) $user->roles;

            $new_roles = array_map([ $this, 'sanitize_role' ], $_POST['wpfep_re_user_roles']);

            foreach ($new_roles as $new_role) {
                if (! in_array($new_role, (array) $user->roles)) {
                    $user->add_role($new_role);
                }
            }

            foreach ($old_roles as $old_role) {
                if (! in_array($old_role, $new_roles)) {
                    $user->remove_role($old_role);
                }
            }
        } else {
            foreach ((array) $user->roles as $old_role) {
                $user->remove_role($old_role);
            }
        }
    }

    public function scripts_and_styles_actions($location)
    {
        // For Enqueue jQuery on both Add User and Edit User back-end pages
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_jquery' ]);

        // This is Actions for Add User back-end page
        if ('user_new' == $location) {
            add_action('admin_footer', [ $this, 'print_scripts_user_new' ], 25);
        }
    }

    // For Enqueue jQuery where needed (use action)
    public function enqueue_jquery()
    {
        wp_enqueue_script('jquery');
    }

    // To Print scripts on Add User back-end page
    public function print_scripts_user_new()
    {
        ?>
			<script>
				jQuery( document ).ready( function() {
					// Remove WordPress default Role Select
					var roles_dropdown = jQuery( 'select#role' );
					roles_dropdown.closest( 'tr' ).remove();
				} );
			</script>

		<?php
    }

    // Print scripts on Edit User back-end page
    public function print_scripts_user_edit()
    {
        ?>
			<script>
				jQuery( document ).ready(
					// Remove WordPress default Role Select
					function() {
						jQuery( '.user-role-wrap' ).remove();
					}
				);
			</script>

		<?php
    }

    // Print scripts on Edit User back-end page
    public function print_styles_user_edit()
    {
        ?>
			<style type="text/css">
				/* Hide WordPress default Role Select */
				.user-role-wrap {
					display: none !important;
				}
			</style>

		<?php
    }
}
       $add_role = wpfep_get_option('role_editor', 'wpfep_general');
if ('on' == $add_role) {
    $wpfep_role_editor_instance = new WPFEP_Roles_Editor();
}
