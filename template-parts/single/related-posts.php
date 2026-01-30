<?php
/**
 * Related Posts by Category (non-ACF)
 * Design matches property cards: bg image fill + gradient overlay + inner slab.
 *
 * Usage:
 * get_template_part('template-parts/blog/related-posts', null, [
 *   'heading' => 'Related posts',
 *   'limit'   => 6,
 *   'orderby' => 'date',
 *   'order'   => 'DESC',
 * ]);
 */

if (!defined('ABSPATH')) { exit; }

// ========== Args / defaults ==========
$args = wp_parse_args($args ?? [], [
  'heading' => 'Related posts',
  'limit'   => 3,
  'orderby' => 'date',
  'order'   => 'DESC',
]);

$heading = is_string($args['heading']) ? $args['heading'] : 'Related posts';
$limit   = (int) $args['limit'];
if ($limit < 1) { $limit = 6; }
$orderby = in_array($args['orderby'], ['date','modified','rand','title'], true) ? $args['orderby'] : 'date';
$order   = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

// ========== Identify categories from current post ==========
$current_id = get_the_ID();
if (!$current_id) { return; }

$cats = get_the_terms($current_id, 'category');
$cat_ids = [];
if (!empty($cats) && !is_wp_error($cats)) {
  foreach ($cats as $c) { $cat_ids[] = (int) $c->term_id; }
}

// ========== Build query ==========
$q_args = [
  'post_type'           => 'post',
  'posts_per_page'      => $limit,
  'post_status'         => 'publish',
  'orderby'             => $orderby,
  'order'               => $order,
  'post__not_in'        => [$current_id],
  'ignore_sticky_posts' => true,
];

// If categories exist, use them; else we’ll fall back to latest posts
if (!empty($cat_ids)) {
  $q_args['category__in'] = $cat_ids;
}

$q = new WP_Query($q_args);

// Fallback if nothing found and we had categories
if (!$q->have_posts() && !empty($cat_ids)) {
  $q = new WP_Query(array_merge($q_args, [
    'category__in' => [], // drop category constraint
  ]));
}

if (!$q->have_posts()) {
  // Nothing to show; bail silently
  return;
}

// ========== Markup ==========
$section_id = 'related-posts-' . wp_generate_uuid4();
?>
<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative">
  <div class="flex flex-col items-center py-10 mx-auto w-full lg:py-20 max-w-container max-lg:px-5">
    <div class="box-border flex flex-col gap-8 items-start py-0 w-full">

      <!-- Heading -->
      <?php if (!empty($heading)): ?>
        <div class="flex flex-col gap-2 items-start w-full">
          <h2 class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8">
            <?php echo esc_html($heading); ?>
          </h2>
          <div class="flex justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
            <div class="bg-orange-500 flex-1 h-[5px]"></div>
            <div class="bg-sky-500 flex-1 h-[5px]"></div>
            <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
            <div class="bg-lime-600 flex-1 h-[5px]"></div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Grid: same pattern as property cards -->
      <div class="grid grid-cols-1 gap-12 w-full sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 max-md:gap-8 max-sm:gap-6" role="region" aria-label="<?php echo esc_attr($heading ?: 'Related posts'); ?>">
        <?php
        $idx = 0;
        while ($q->have_posts()): $q->the_post();
          $pid       = get_the_ID();
          $title     = get_the_title();
          $permalink = get_permalink();
          $date_txt  = get_the_date('j F Y');

          // simple reading time
          $content    = get_post_field('post_content', $pid);
          $word_count = str_word_count(wp_strip_all_tags($content));
          $minutes    = max(1, (int) ceil($word_count / 200));
          $read_time  = $minutes . ' min' . ($minutes > 1 ? 's' : '');

          // categories data for potential client-side filtering (optional)
          $p_terms = get_the_terms($pid, 'category') ?: [];
          $cats_attr = implode(' ', wp_list_pluck($p_terms, 'slug'));

          // Featured image
          $thumb_id = get_post_thumbnail_id($pid);
          $img_alt  = $thumb_id ? (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $title) : $title;
          $img_html = $thumb_id
            ? wp_get_attachment_image($thumb_id, 'large', false, [
                'alt'     => esc_attr($img_alt),
                'class'   => 'w-full h-full object-cover',
                'loading' => 'lazy',
              ])
            : '';

          // Span pattern: every 3rd card spans 2 cols on sm/md; resets at lg
          $span_classes = ($idx % 3 === 2)
            ? 'sm:col-span-2 md:col-span-2 lg:col-span-1'
            : 'sm:col-span-1 md:col-span-1 lg:col-span-1';
          ?>
          <a href="<?php echo esc_url($permalink); ?>"
             class="group flex flex-col items-start h-[318px] max-md:h-[280px] max-sm:h-[250px] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 <?php echo esc_attr($span_classes); ?>"
             data-categories="<?php echo esc_attr($cats_attr); ?>"
             aria-label="<?php echo esc_attr('Read full article: ' . $title); ?>">
            <div class="flex overflow-hidden flex-col justify-center items-center w-full flex-[1_0_0] relative">
              <?php if ($img_html): ?>
                <div class="absolute inset-0 w-full h-full">
                  <?php echo $img_html; ?>
                </div>
              <?php endif; ?>

              <!-- Gradient overlay on hover/focus -->
              <div
                class="absolute inset-0 opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 group-focus:opacity-100"
                style="background: linear-gradient(0deg, rgba(0, 152, 216, 0.25) 0%, rgba(0, 152, 216, 0.25) 100%);"
                aria-hidden="true"
              ></div>

              <div class="box-border flex flex-col justify-end items-start p-8 w-full flex-[1_0_0] max-sm:p-6 relative z-10">
                <div class="flex flex-col items-start px-8 py-4 bg-gray-200 max-md:px-6 max-md:py-3 max-sm:px-5 max-sm:py-3">
                  <span class="font-secondary font-semibold text-[2.125rem] leading-[2.5rem] tracking-[-0.01rem] text-[#0A1119]">
                    <div class="transition-colors duration-200">
                      <h3 class="text-[inherit] leading-[inherit] font-[inherit]">
                        <?php echo esc_html($title); ?>
                      </h3>
                    </div>
                  </span>
                  <p class="font-normal text-[1rem] leading-[1.625rem] text-[#434B53]">
                    <?php echo esc_html($date_txt); ?> • <?php echo esc_html($read_time); ?>
                  </p>
                </div>
              </div>
            </div>
          </a>
          <?php
          $idx++;
        endwhile;
        wp_reset_postdata();
        ?>
      </div>

    </div>
  </div>
</section>
