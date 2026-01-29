<?php
get_header();
?>
  
<main class="overflow-hidden mx-auto w-full">
    <?php
    if (function_exists('load_hero_templates')) {
        load_hero_templates();
    }
    ?>


  <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content()) != '') : ?>
                <div class="max-w-[1095px] max-xl:px-5  mx-auto">
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

  </main>
<?php
get_footer();
?>