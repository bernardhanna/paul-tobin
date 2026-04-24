<?php
get_header();
?>
<main id="primary" class="overflow-hidden w-full min-h-screen site-main" tabindex="-1">
    <?php
    $is_blog_post = (get_post_type() === 'post');
    $current_post_id = get_queried_object_id();

    // Blog posts use the featured image as the top hero (no video/home hero block).
    if ($is_blog_post && $current_post_id && has_post_thumbnail($current_post_id)) :
        $featured_image_id  = get_post_thumbnail_id($current_post_id);
        $featured_image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true) ?: get_the_title($current_post_id);
    ?>
        <section class="flex overflow-hidden relative w-full">
            <?php echo wp_get_attachment_image($featured_image_id, 'full', false, [
                'alt' => esc_attr($featured_image_alt),
                'class' => 'w-full max-md:h-auto md:h-[500px] md:h-[665px] object-contain md:object-cover',
                'loading' => 'lazy',
                'fetchpriority' => 'low',
                'decoding' => 'async',
            ]); ?>
        </section>
    <?php
    elseif (function_exists('load_hero_templates')) :
        load_hero_templates();
    endif;
    ?>

    <?php
    $enable_breadcrumbs = get_field('enable_breadcrumbs', 'option');

    if ($enable_breadcrumbs !== false) :
        get_template_part('template-parts/header/breadcrumbs');
    endif;
    ?>

    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (get_post_type() !== 'property' && trim(get_the_content()) != '') : ?>
                <div class="mx-auto w-full max-w-[60rem] max-xl:px-5">
                    <?php
                    get_template_part('template-parts/content/content', 'page');
                    ?>
                </div>
    <?php endif;
        endwhile;
    else :
        echo '<p>No content found</p>';
    endif;
    ?>

    <?php load_flexible_content_templates(); ?>

    <?php
    // Blog posts: keep related posts, remove author/social bar section.
    if ($is_blog_post) :
    ?>
        <?php get_template_part('template-parts/single/related-posts'); ?>
    <?php endif; ?>
</main>

<?php
get_footer();
?>