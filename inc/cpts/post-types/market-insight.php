<?php
/**
 * Plugin Name: CPT – Market Insights
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_extended_post_type(
        'market_insight',
        [
            'menu_icon'       => 'dashicons-chart-pie',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'market-insights', 'with_front' => false],
            'menu_position'   => 22,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Market Insight',
            'plural'   => 'Market Insights',
            'slug'     => 'market-insights',
        ]
    );
});
