<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>[x-cloak]{ display:none !important; }</style>

</head>
<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>
    <a
        class="matrix-skip-link"
        href="#primary"
        onclick="var m=document.getElementById('primary');if(m){setTimeout(function(){m.focus({preventScroll:true});},0);}"
    ><?php esc_html_e('Skip to main content', 'matrix-starter'); ?></a>
    <header class="relative">

        <?php get_template_part('template-parts/header/navbar'); ?>
    </header>