<?php
/**
 * Plugin Name: CPT – Case Studies
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_extended_post_type(
        'team',
        [
            'menu_icon'       => 'dashicons-groups',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'team', 'with_front' => false],
            'menu_position'   => 23,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'team',
            'plural'   => 'team',
            'slug'     => 'team',
        ]
    );
});

register_taxonomy(
    'team_role',
    array('team'),
    array(
        'labels' => array(
            'name'          => __('Roles', 'matrix-starter'),
            'singular_name' => __('Role', 'matrix-starter'),
        ),
        'public'            => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_rest'      => true,
        'hierarchical'      => true,
        'rewrite'           => array('slug' => 'team-role', 'with_front' => false),
        'show_admin_column' => true,
    )
);
