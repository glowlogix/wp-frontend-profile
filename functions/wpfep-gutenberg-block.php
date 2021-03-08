<?php
defined('ABSPATH') || exit;


// WPFEP Gutenberg block
function wpfep_gutenberg_block()
{
    wp_enqueue_script(
        'wpfep-gutenberg-block',
        plugins_url('assets/js/block.js', __DIR__),
        array('wp-blocks', 'wp-element')
    );
}

add_action('enqueue_block_editor_assets', 'wpfep_gutenberg_block');
