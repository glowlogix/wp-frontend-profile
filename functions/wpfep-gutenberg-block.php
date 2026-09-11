<?php
/**
 * @package wp-front-end-profile
 * WPFEP Gutenberg block
 */
defined('ABSPATH') || exit;

function wpfep_gutenberg_block()
{
    wp_enqueue_script(
        'wpfep-gutenberg-block',
        plugins_url('assets/js/block.js', __DIR__),
        array( 'wp-block-editor', 'wp-blocks', 'wp-element' ),
        WPFEP_VERSION,
        true
    );
}

add_action('enqueue_block_editor_assets', 'wpfep_gutenberg_block');
