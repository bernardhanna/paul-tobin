<?php
/**
 * Plugin Name: CPT – Guides
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_extended_post_type(
        'guide',
        [
            'menu_icon'       => 'dashicons-book-alt',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'guides', 'with_front' => false],
            'menu_position'   => 21,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Guide',
            'plural'   => 'Guides',
            'slug'     => 'guides',
        ]
    );
});
