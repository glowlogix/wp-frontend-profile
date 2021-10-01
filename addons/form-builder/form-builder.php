<?php
/**
 * Author:      Glowlogix
 * Author URI:  https://www.glowlogix.com
 * License:     GPL v2 or later.
 */

// Prevent direct file access
defined("ABSPATH") or exit();

if (!defined("FBWPF_VERSION")) {
    define("FBWPF_VERSION", "1.0.0");
}

if (!defined("FBWPF_PATH")) {
    define("FBWPF_PATH", plugin_dir_path(__FILE__));
}
if (!defined("FBWPF_PLUGIN_URL")) {
    define("FBWPF_PLUGIN_URL", plugin_dir_url(__FILE__));
}
//WPFEP_PATH

add_action("admin_footer", "fbwpf_popup_modals");
function fbwpf_popup_modals()
{
    include WPFEP_PATH . "/views/popup-modals.php";
}

/** @ignore */
function _FBWPF_load_plugin()
{
    if (
        !in_array(
            "wp-frontend-profile-pro/wp-frontend-profile.php",
            apply_filters("active_plugins", get_option("active_plugins"))
        )
    ) {
        add_action("admin_notices", "FBWPF_missing_notice");
        return false;
    } else {
        require_once FBWPF_PATH . "init.php";
    }
}
add_action("plugins_loaded", "_FBWPF_load_plugin", 8);

function FBWPF_missing_notice()
{
    echo '<div class="error wfp-message"><p>' .
        sprintf(
            __(
                'Sorry, <strong>%s</strong> requires WP Frontend Profile to be installed and activated first. Please install <a href="%s">WP Frontend Profile</a> first.',
                "slwfp"
            ),
            "Form Builder For Wp Frontend Profile",
            admin_url(
                "plugin-install.php?tab=search&type=term&s=WP+Frontend+Profile"
            )
        ) .
        "</p></div>";
}
