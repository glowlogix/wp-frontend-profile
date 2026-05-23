<?php
defined('ABSPATH') || exit;

class WPFEP_User_Role_Manager {

    public function __construct() {
        add_shortcode('wpfep_manage_roles', [$this, 'render']);
        add_action('init', [$this, 'create_user']);
        add_action('wp_ajax_wpfep_update_role', [$this, 'update_role']);
    }

    private function get_frontend_roles() {
        $roles = wp_roles()->roles;
        unset($roles['administrator']);

        return $roles;
    }

    /**
     * FRONTEND UI
     */
    public function render() {

        if (!current_user_can('manage_options')) {
            return '<p>Access Denied</p>';
        }

        ob_start();

        $users = get_users();
        $roles = $this->get_frontend_roles();
        ?>

        <div class="wpfep-role-manager">

            <h2>User Role Management</h2>

            <?php if (isset($_GET['wpfep_user_created']) && $_GET['wpfep_user_created'] === '1'): ?>
                <p class="wpfep-success wpfep-role-notice">User created successfully.</p>
            <?php endif; ?>

            <?php if (isset($_GET['wpfep_user_error'])): ?>
                <p class="wpfep-error wpfep-role-notice"><?php echo esc_html(wp_unslash($_GET['wpfep_user_error'])); ?></p>
            <?php endif; ?>

            <?php if (current_user_can('create_users')): ?>
                <button type="button" id="wpfep-show-add-user" class="wpfep-add-user-toggle">Add User</button>

                <div id="wpfep-add-user-panel" class="wpfep-add-user-panel" hidden>
                    <form method="post" class="wpfep-add-user-form">
                        <h3>Add New User</h3>

                        <p>
                            <label for="wpfep_new_user_login">Username</label>
                            <input type="text" name="wpfep_new_user_login" id="wpfep_new_user_login" required>
                        </p>

                        <p>
                            <label for="wpfep_new_user_email">Email</label>
                            <input type="email" name="wpfep_new_user_email" id="wpfep_new_user_email" required>
                        </p>

                        <p>
                            <label for="wpfep_new_user_password">Password</label>
                            <input type="password" name="wpfep_new_user_password" id="wpfep_new_user_password" required>
                        </p>

                        <p>
                            <label for="wpfep_new_user_role">Role</label>
                            <select name="wpfep_new_user_role" id="wpfep_new_user_role" required>
                                <?php foreach ($roles as $key => $r): ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($r['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>

                        <?php wp_nonce_field('wpfep_create_frontend_user', 'wpfep_create_user_nonce'); ?>
                        <input type="hidden" name="wpfep_create_frontend_user" value="1">
                        <button type="submit">Add User</button>
                    </form>
                </div>
            <?php endif; ?>

            <input type="text" id="user-search" placeholder="Search user...">

            <!-- NONCE -->
            <?php wp_nonce_field('wpfep_role_change', 'wpfep_nonce'); ?>

            <table class="wpfep-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Change Role</th>
                    </tr>
                </thead>

                <tbody id="user-table">

                    <?php foreach ($users as $user): ?>

                        <?php $role = $user->roles[0] ?? 'subscriber'; ?>

                        <tr data-name="<?php echo esc_attr(strtolower($user->display_name)); ?>">

                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>

                            <td>
                                <span class="role-badge role-<?php echo esc_attr($role); ?>">
                                    <?php echo ucfirst($role); ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($user->ID != get_current_user_id() && $user->ID != 1 && !in_array('administrator', (array) $user->roles, true)): ?>

                                    <select class="role-select" data-user="<?php echo esc_attr($user->ID); ?>">

                                        <?php foreach ($roles as $key => $r): ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($role, $key); ?>>
                                                <?php echo esc_html($r['name']); ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>

                                <?php else: ?>
                                    <strong>Locked</strong>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>

        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function () {

            document.querySelectorAll(".wpfep-role-notice").forEach(notice => {
                setTimeout(() => {
                    notice.style.display = "none";
                }, 4000);
            });

            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete("wpfep_user_created");
                url.searchParams.delete("wpfep_user_error");
                window.history.replaceState({}, document.title, url.toString());
            }

            const addUserButton = document.getElementById("wpfep-show-add-user");
            const addUserPanel = document.getElementById("wpfep-add-user-panel");

            if (addUserButton && addUserPanel) {
                addUserButton.addEventListener("click", function () {
                    addUserPanel.hidden = !addUserPanel.hidden;
                    addUserButton.innerText = addUserPanel.hidden ? "Add User" : "Hide Add User";
                });
            }

            // SEARCH
            const searchInput = document.getElementById("user-search");

            if (searchInput) {
                searchInput.addEventListener("keyup", function () {
                    let value = this.value.toLowerCase();

                    document.querySelectorAll("#user-table tr").forEach(row => {
                        row.style.display = row.dataset.name.includes(value) ? "" : "none";
                    });
                });
            }

            // ROLE CHANGE
            document.querySelectorAll(".role-select").forEach(select => {

                select.addEventListener("change", function () {

                    let user_id = this.dataset.user;
                    let role = this.value;

                    let nonce = document.querySelector('input[name="wpfep_nonce"]').value;

                    this.disabled = true;

                    fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                        },
                        body: new URLSearchParams({
                            action: "wpfep_update_role",
                            user_id: user_id,
                            role: role,
                            nonce: nonce
                        })
                    })
                    .then(res => res.text())
                    .then(res => {

                        this.disabled = false;

                        if (res.trim() === "success") {

                            let row = this.closest("tr");
                            let badge = row.querySelector(".role-badge");

                            badge.className = "role-badge role-" + role;
                            badge.innerText = role.charAt(0).toUpperCase() + role.slice(1);

                        } else {
                            alert("Error: " + res);
                        }

                    })
                    .catch(err => {
                        this.disabled = false;
                        console.error(err);
                        alert("AJAX failed");
                    });

                });

            });

        });
        </script>

        <style>
        .wpfep-add-user-toggle {
            cursor: pointer;
            margin-bottom: 12px;
        }

        .wpfep-add-user-form {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
        }

        .wpfep-add-user-form p {
            margin-bottom: 12px;
        }

        .wpfep-add-user-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .wpfep-add-user-form input,
        .wpfep-add-user-form select {
            max-width: 320px;
            width: 100%;
        }

        .wpfep-table {
            width: 100%;
            border-collapse: collapse;
        }

        .wpfep-table th,
        .wpfep-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .role-badge {
            background: #6c757d;
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
            display: inline-block;
        }

        .role-administrator { background: #e74c3c; }
        .role-editor { background: #3498db; }
        .role-author { background: #2ecc71; }
        .role-contributor { background: #f39c12; }
        .role-subscriber { background: #7f8c8d; }

        input#user-search {
            padding: 8px;
            margin-bottom: 10px;
            width: 250px;
        }
        </style>

        <?php
        return ob_get_clean();
    }

    /**
     * CREATE USER HANDLER
     */
    public function create_user() {

        if (!isset($_POST['wpfep_create_frontend_user'])) {
            return;
        }

        if (!current_user_can('manage_options') || !current_user_can('create_users')) {
            wp_die('No permission');
        }

        if (!isset($_POST['wpfep_create_user_nonce']) || !wp_verify_nonce($_POST['wpfep_create_user_nonce'], 'wpfep_create_frontend_user')) {
            wp_die('Invalid nonce');
        }

        $username = isset($_POST['wpfep_new_user_login']) ? sanitize_user(wp_unslash($_POST['wpfep_new_user_login'])) : '';
        $email = isset($_POST['wpfep_new_user_email']) ? sanitize_email(wp_unslash($_POST['wpfep_new_user_email'])) : '';
        $password = isset($_POST['wpfep_new_user_password']) ? wp_unslash($_POST['wpfep_new_user_password']) : '';
        $role = isset($_POST['wpfep_new_user_role']) ? sanitize_text_field(wp_unslash($_POST['wpfep_new_user_role'])) : 'subscriber';
        $roles = $this->get_frontend_roles();

        if ($role === 'administrator' || !isset($roles[$role])) {
            $role = 'subscriber';
        }

        if (empty($username) || empty($email) || empty($password)) {
            wp_safe_redirect(add_query_arg('wpfep_user_error', rawurlencode('Please fill all required fields.'), wp_get_referer()));
            exit;
        }

        if (username_exists($username)) {
            wp_safe_redirect(add_query_arg('wpfep_user_error', rawurlencode('Username already exists.'), wp_get_referer()));
            exit;
        }

        if (email_exists($email)) {
            wp_safe_redirect(add_query_arg('wpfep_user_error', rawurlencode('Email already exists.'), wp_get_referer()));
            exit;
        }

        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $password,
            'role'       => $role,
        ]);

        if (is_wp_error($user_id)) {
            wp_safe_redirect(add_query_arg('wpfep_user_error', rawurlencode($user_id->get_error_message()), wp_get_referer()));
            exit;
        }

        wp_safe_redirect(add_query_arg('wpfep_user_created', '1', remove_query_arg('wpfep_user_error', wp_get_referer())));
        exit;
    }

    /**
     * AJAX HANDLER
     */
    public function update_role() {

        if (!current_user_can('manage_options')) {
            wp_die('No permission');
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpfep_role_change')) {
            wp_die('Invalid nonce');
        }

        $user_id = intval($_POST['user_id']);
        $role = sanitize_text_field($_POST['role']);
        $roles = $this->get_frontend_roles();

        if ($role === 'administrator' || !isset($roles[$role])) {
            wp_die('Not allowed');
        }

        if ($user_id == get_current_user_id() || $user_id == 1) {
            wp_die('Not allowed');
        }

        $user = new WP_User($user_id);
        if (in_array('administrator', (array) $user->roles, true)) {
            wp_die('Not allowed');
        }

        $user->set_role($role);

        wp_die('success');
    }
}
