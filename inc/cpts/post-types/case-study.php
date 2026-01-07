<?php
/**
 * Plugin Name: CPT – Case Studies
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_extended_post_type(
        'case_study',
        [
            'menu_icon'       => 'dashicons-awards',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'case-studies', 'with_front' => false],
            'menu_position'   => 23,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Case Study',
            'plural'   => 'Case Studies',
            'slug'     => 'case-studies',
        ]
    );
});
