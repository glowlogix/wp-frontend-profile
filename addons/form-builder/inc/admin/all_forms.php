<?php
// Abort if this file is accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/*
 * LOADING THE BASE CLASS
 */
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * LOAD THE CHILD CLASS
 */
require dirname(__FILE__) . '/includes/class-forms-list-table.php';
