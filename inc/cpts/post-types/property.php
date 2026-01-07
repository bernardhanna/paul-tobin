<?php
/**
 * Plugin Name: CPT – Property
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* CPT: property */
    register_extended_post_type(
        'property',
        [
            'menu_icon'       => 'dashicons-admin-home',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'properties', 'with_front' => false],
            'menu_position'   => 20,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Property',
            'plural'   => 'Properties',
            'slug'     => 'properties',
        ]
    );

    /* Taxonomy: property_status (non-hierarchical) */
    register_extended_taxonomy(
        'property_status',
        'property',
        [
            'hierarchical'    => false,
            'show_ui'         => true,
            'show_in_rest'    => true,
            'show_admin_column'=> true,
            'rewrite'         => ['slug' => 'property-status', 'with_front' => false],
        ],
        [
            'singular' => 'Property Status',
            'plural'   => 'Property Status',
            'slug'     => 'property-status',
        ]
    );

    /* Taxonomy: property_type (hierarchical) */
    register_extended_taxonomy(
        'property_type',
        'property',
        [
            'hierarchical'     => true,
            'show_ui'          => true,
            'show_in_rest'     => true,
            'show_admin_column'=> true,
            'rewrite'          => ['slug' => 'property-type', 'with_front' => false],
        ],
        [
            'singular' => 'Property Type',
            'plural'   => 'Property Types',
            'slug'     => 'property-type',
        ]
    );
});

/* Seed default terms (runs in admin only) */
add_action('admin_init', function () {
    $ensure = function ($name, $tax) {
        if (!taxonomy_exists($tax)) return;
        if (!term_exists($name, $tax)) wp_insert_term($name, $tax);
    };

    foreach (['For Sale','For Rent','Sold','Let Agreed','Rented'] as $t) {
        $ensure($t, 'property_status');
    }
    foreach (['Apartment','House','Townhouse','Villa','Studio','Duplex','Land','Commercial'] as $t) {
        $ensure($t, 'property_type');
    }
});
