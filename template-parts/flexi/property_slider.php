<?php
// =========================================================
// Flexi Block: Property Slider (+ Before & After compare mode)
// =========================================================

// ACF fields
$section_heading        = get_sub_field('section_heading');
$section_heading_tag    = get_sub_field('section_heading_tag') ?: 'h2';
$background_color       = get_sub_field('background_color') ?: '#FFFFFF';
$slider_mode            = get_sub_field('slider_mode') ?: 'properties';
$is_before_after        = ($slider_mode === 'before_after');
$before_after_layout    = get_sub_field('before_after_layout') ?: 'carousel';
if (!in_array($before_after_layout, ['carousel', 'grid_2'], true)) {
  $before_after_layout = 'carousel';
}
$is_ba_grid             = ($is_before_after && $before_after_layout === 'grid_2');

$selected_properties    = get_sub_field('selected_properties');
$auto_select_properties = get_sub_field('auto_select_properties');
$number_of_properties   = (int) get_sub_field('number_of_properties');
$property_order         = get_sub_field('property_order'); // 'latest', 'oldest', 'random'

// Padding settings
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size    = get_sub_field('screen_size');
    $padding_top    = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');
    if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
      $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
    }
    if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
      $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
  }
}

// Collect slides
$properties = [];
$before_after_pairs = [];

if ($is_before_after) {
  if (have_rows('before_after_pairs')) {
    while (have_rows('before_after_pairs')) {
      the_row();
      $before_id = (int) get_sub_field('before_image');
      $after_id  = (int) get_sub_field('after_image');
      if (!$before_id || !$after_id) {
        continue;
      }
      // Default on for older rows that pre-date this field (null/'' => show).
      $show_text_card_raw = get_sub_field('show_text_card');
      $show_text_card = ($show_text_card_raw === null || $show_text_card_raw === '')
        ? true
        : (bool) $show_text_card_raw;

      $handle_start = (string) get_sub_field('handle_start_position');
      if (!in_array($handle_start, ['one_third', 'center', 'two_thirds'], true)) {
        $handle_start = 'center';
      }
      $handle_start_pct = match ($handle_start) {
        'one_third'  => 33.0,
        'two_thirds' => 67.0,
        default      => 50.0,
      };

      $before_after_pairs[] = [
        'before_id'         => $before_id,
        'after_id'          => $after_id,
        'show_text_card'    => $show_text_card,
        'title'             => trim((string) get_sub_field('pair_title')),
        'caption'           => trim((string) get_sub_field('pair_caption')),
        'before_label'      => trim((string) get_sub_field('before_label')) ?: 'Before',
        'after_label'       => trim((string) get_sub_field('after_label')) ?: 'After',
        'handle_start'      => $handle_start,
        'handle_start_pct'  => $handle_start_pct,
      ];
    }
  }
  $slide_count = count($before_after_pairs);
} else {
  if ($auto_select_properties) {
    $args = [
      'post_type'      => 'property',
      'posts_per_page' => $number_of_properties ?: 5,
      'post_status'    => 'publish',
    ];
    if ($property_order === 'random') {
      $args['orderby'] = 'rand';
    } elseif ($property_order === 'oldest') {
      $args['orderby'] = 'date';
      $args['order']   = 'ASC';
    } else {
      $args['orderby'] = 'date';
      $args['order']   = 'DESC';
    }
    $properties = get_posts($args);
  } else {
    $properties = is_array($selected_properties) ? $selected_properties : [];
  }
  $slide_count = is_array($properties) ? count($properties) : 0;
}

if (!function_exists('matrix_property_slider_clean_value')) {
  /**
   * Convert meta/WYSIWYG-ish values to plain text.
   */
  function matrix_property_slider_clean_value($value): string {
    if (is_array($value)) {
      $value = implode(' ', array_map('strval', $value));
    }
    $value = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim((string) $value);
  }
}

if (!function_exists('matrix_property_slider_property_data_value')) {
  /**
   * Read a normalized value from the first property_data flexible-content block.
   * Supports direct keys (e.g. "size") and extra row labels (e.g. "Bedrooms", "Bathrooms").
   */
  function matrix_property_slider_property_data_value(int $property_id, string $key): string {
    $blocks = get_field('flexible_content_blocks', $property_id);
    if (!is_array($blocks) || empty($blocks)) {
      return '';
    }

    $normalized_key = strtolower(trim($key));
    foreach ($blocks as $block) {
      if (!is_array($block)) {
        continue;
      }
      if (($block['acf_fc_layout'] ?? '') !== 'property_data') {
        continue;
      }

      // Direct property_data fields (e.g. size).
      if (isset($block[$normalized_key]) && $block[$normalized_key] !== '') {
        return matrix_property_slider_clean_value($block[$normalized_key]);
      }

      // Extra rows, where labels like "Bedrooms"/"Bathrooms" are commonly stored.
      $rows = $block['extra_rows'] ?? array();
      if (!is_array($rows)) {
        continue;
      }
      foreach ($rows as $row) {
        if (!is_array($row)) {
          continue;
        }
        $label = strtolower(trim((string) ($row['label'] ?? '')));
        if ($label === $normalized_key) {
          return matrix_property_slider_clean_value($row['value'] ?? '');
        }
      }
    }

    return '';
  }
}

// Unique IDs
$section_id = 'property-slider-' . uniqid();
$slider_id  = $section_id;
$has_slides = $is_before_after ? !empty($before_after_pairs) : !empty($properties);
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative bg-white flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
  style="background-color: <?php echo esc_attr($background_color); ?>;"
  data-slider-mode="<?php echo esc_attr($is_before_after ? 'before_after' : 'properties'); ?>"
  data-ba-layout="<?php echo esc_attr($is_before_after ? $before_after_layout : ''); ?>"
>
  <div class="flex flex-col items-center pt-8  md:py-6 md:pt-[5rem] md:pb-[5rem] mx-auto w-full max-w-container max-xl:px-5 max-md:pb-8">

    <?php if (!empty($section_heading)): ?>
      <header class="gap-6 w-full text-[2.125rem] font-semibold tracking-normal leading-none text-left md:text-center text-primary max-md:max-w-full">
        <div class="flex flex-col gap-6 items-start w-full max-md:max-w-full">
          <<?php echo esc_attr($section_heading_tag); ?> class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left md:text-center font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8 max-md:max-w-full">
            <?php echo esc_html($section_heading); ?>
          </<?php echo esc_attr($section_heading_tag); ?>>
          <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
            <div class="bg-orange-500 flex-1 h-[5px]"></div>
            <div class="bg-sky-500 flex-1 h-[5px]"></div>
            <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
            <div class="bg-lime-600 flex-1 h-[5px]"></div>
          </div>
        </div>
      </header>
    <?php endif; ?>

    <?php if ($has_slides): ?>
      <div class="relative mt-12 w-full max-md:mt-5 max-md:max-w-full">
        <div
          class="property-slider<?php echo $is_ba_grid ? ' ba-grid ba-grid--2' : ''; ?>"
          role="region"
          aria-roledescription="<?php echo $is_ba_grid ? 'group' : 'carousel'; ?>"
          aria-label="<?php echo esc_attr($is_before_after ? 'Before and after showcase' : 'Property showcase'); ?>"
        >

          <?php if ($is_before_after): ?>
            <?php foreach ($before_after_pairs as $index => $pair):
              $before_alt = get_post_meta($pair['before_id'], '_wp_attachment_image_alt', true) ?: ($pair['title'] ? $pair['title'] . ' — before' : 'Before');
              $after_alt  = get_post_meta($pair['after_id'], '_wp_attachment_image_alt', true) ?: ($pair['title'] ? $pair['title'] . ' — after' : 'After');
              $compare_id = $section_id . '-ba-' . $index;
              $start_pct  = isset($pair['handle_start_pct']) ? (float) $pair['handle_start_pct'] : 50.0;
              $start_pct  = max(0, min(100, $start_pct));
              $start_pct_css = rtrim(rtrim(number_format($start_pct, 3, '.', ''), '0'), '.') . '%';
              $start_pct_aria = (int) round($start_pct);
              $show_ba_text_card = !empty($pair['show_text_card'])
                && ($pair['title'] !== '' || $pair['caption'] !== '');
              $show_ba_nav = (!$is_ba_grid && $slide_count > 1);
            ?>
              <article class="property-slide ba-slide<?php echo $is_ba_grid ? ' ba-slide--grid' : ''; ?>">
                <div class="<?php echo $is_ba_grid
                  ? 'ba-slide__inner relative flex flex-col w-full max-md:max-w-full'
                  : 'flex overflow-hidden relative flex-col p-0 md:p-8 w-full md:min-h-[723px] max-md:max-w-full justify-between'; ?>">

                  <div
                    class="<?php echo $is_ba_grid
                      ? 'ba-compare relative w-full'
                      : 'ba-compare relative inset-0 w-full h-full max-md:order-0 md:absolute md:inset-8 md:w-auto md:h-auto'; ?>"
                    data-ba-compare
                    data-ba-start="<?php echo esc_attr((string) $start_pct); ?>"
                    id="<?php echo esc_attr($compare_id); ?>"
                    style="--ba-pos: <?php echo esc_attr($start_pct_css); ?>;"
                  >
                    <div class="<?php echo $is_ba_grid
                      ? 'ba-compare__media relative w-full overflow-hidden bg-[#e0e0e0] aspect-[4/3]'
                      : 'ba-compare__media relative w-full overflow-hidden bg-[#e0e0e0] aspect-[4/3] md:aspect-auto md:h-full md:min-h-[40rem]'; ?>">
                      <div class="ba-compare__after absolute inset-0">
                        <?php echo wp_get_attachment_image($pair['after_id'], 'full', false, [
                          'alt'           => esc_attr($after_alt),
                          'class'         => 'object-cover w-full h-full',
                          'loading'       => $index === 0 ? 'eager' : 'lazy',
                          'draggable'     => 'false',
                        ]); ?>
                        <span class="ba-compare__label ba-compare__label--after"><?php echo esc_html($pair['after_label']); ?></span>
                      </div>

                      <div class="ba-compare__before absolute inset-0" aria-hidden="true">
                        <?php echo wp_get_attachment_image($pair['before_id'], 'full', false, [
                          'alt'       => '',
                          'class'     => 'object-cover w-full h-full',
                          'loading'   => $index === 0 ? 'eager' : 'lazy',
                          'draggable' => 'false',
                        ]); ?>
                        <span class="ba-compare__label ba-compare__label--before"><?php echo esc_html($pair['before_label']); ?></span>
                      </div>

                      <div class="ba-compare__handle" data-ba-handle>
                        <div class="ba-compare__line" aria-hidden="true"></div>
                        <button
                          type="button"
                          class="ba-compare__knob"
                          role="slider"
                          aria-valuemin="0"
                          aria-valuemax="100"
                          aria-valuenow="<?php echo esc_attr((string) $start_pct_aria); ?>"
                          aria-label="<?php echo esc_attr(sprintf('Compare before and after%s', $pair['title'] !== '' ? ': ' . $pair['title'] : '')); ?>"
                          data-ba-knob
                        >
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 8L5 12L9 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 8L19 12L15 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </button>
                      </div>

                      <?php if ($show_ba_text_card && $is_ba_grid): ?>
                        <div class="ba-text-card ba-text-card--grid absolute left-0 bottom-0 z-10 p-5 md:p-6 max-w-[min(100%,22rem)] text-[0.9375rem] leading-6 bg-[#EDEDED]">
                          <?php if ($pair['title'] !== ''): ?>
                            <h4 class="text-[#0A1119] text-[1.125rem] md:text-[1.25rem] font-semibold leading-snug tracking-[-0.16px] font-secondary">
                              <?php echo esc_html($pair['title']); ?>
                            </h4>
                          <?php endif; ?>
                          <?php if ($pair['caption'] !== ''): ?>
                            <p class="<?php echo $pair['title'] !== '' ? 'mt-3 ' : ''; ?>text-[#434B53] font-primary text-[0.875rem] md:text-[0.9375rem] font-normal leading-6 tracking-normal">
                              <?php echo esc_html($pair['caption']); ?>
                            </p>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if ($show_ba_text_card && !$is_ba_grid): ?>
                    <div class="ba-text-card max-md:order-2 relative z-10 p-8 max-w-full text-[0.9375rem] leading-6 bg-[#EDEDED] w-full md:w-[417px] max-md:px-5<?php echo $show_ba_nav ? ' ba-text-card--with-nav' : ''; ?>">
                      <?php if ($pair['title'] !== ''): ?>
                        <h4 class="text-[#0A1119] text-[1.375rem] font-semibold leading-[1.75rem] tracking-[-0.16px] font-secondary">
                          <?php echo esc_html($pair['title']); ?>
                        </h4>
                      <?php endif; ?>
                      <?php if ($pair['caption'] !== ''): ?>
                        <p class="<?php echo $pair['title'] !== '' ? 'mt-4 ' : ''; ?>text-[#434B53] font-primary text-[0.9375rem] font-normal leading-6 tracking-normal">
                          <?php echo esc_html($pair['caption']); ?>
                        </p>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($show_ba_nav): ?>
                    <div class="flex relative z-20 flex-row flex-wrap gap-y-3 justify-end items-center px-5 py-4 mt-auto w-full property-slider__meta-bar bg-primary max-md:order-1 max-md:mt-0 max-md:max-w-full md:px-8 md:py-4">
                      <nav class="flex gap-2 items-center property-slider__desktop-nav shrink-0 max-md:hidden" aria-label="Before and after navigation">
                        <button
                          type="button"
                          class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119] hover:bg-blue mr-2"
                          aria-label="Previous project"
                          data-desktop-prev="<?php echo esc_attr($slider_id); ?>"
                        >
                          <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </button>
                        <button
                          type="button"
                          class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition hover:bg-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119]"
                          aria-label="Next project"
                          data-desktop-next="<?php echo esc_attr($slider_id); ?>"
                        >
                          <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                        </button>
                      </nav>
                      <div class="flex gap-4 md:ml-auto md:hidden max-md:w-full max-md:justify-end">
                        <button
                          type="button"
                          class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 hover:bg-blue"
                          aria-label="Previous project"
                          data-mobile-prev="<?php echo esc_attr($slider_id); ?>">
                          <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect width="40" height="40" fill="#F9FAFB"/>
                            <path d="M21.8333 15.3333L17.1666 20L21.8333 24.6667" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
                          </svg>
                        </button>
                        <button
                          type="button"
                          class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 hover:bg-blue"
                          aria-label="Next project"
                          data-mobile-next="<?php echo esc_attr($slider_id); ?>">
                          <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect width="40" height="40" fill="#F9FAFB"/>
                            <path d="M18.1667 24.6667L22.8334 20L18.1667 15.3333" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>

          <?php else: ?>
            <?php foreach ($properties as $property):
              $property_id     = is_object($property) ? $property->ID : (int) $property;
              $property_image  = get_post_thumbnail_id($property_id);
              $property_title  = get_the_title($property_id);
              $property_excerpt= trim((string) get_the_excerpt($property_id));
              $property_link   = get_permalink($property_id);

              $bedrooms_raw    = matrix_property_slider_property_data_value($property_id, 'bedrooms');
              if ($bedrooms_raw === '' || $bedrooms_raw === null) {
                $bedrooms_raw = get_post_meta($property_id, 'bedrooms', true);
              }
              if ($bedrooms_raw === '' || $bedrooms_raw === null) {
                $bedrooms_raw = get_field('bedrooms', $property_id);
              }
              $bathrooms_raw   = matrix_property_slider_property_data_value($property_id, 'bathrooms');
              if ($bathrooms_raw === '' || $bathrooms_raw === null) {
                $bathrooms_raw = get_post_meta($property_id, 'bathrooms', true);
              }
              if ($bathrooms_raw === '' || $bathrooms_raw === null) {
                $bathrooms_raw = get_field('bathrooms', $property_id);
              }
              $area_raw        = matrix_property_slider_property_data_value($property_id, 'size');
              if ($area_raw === '' || $area_raw === null) {
                $area_raw = get_post_meta($property_id, 'flexible_content_blocks_0_size', true);
              }
              if ($area_raw === '' || $area_raw === null) {
                $area_raw = get_post_meta($property_id, 'area', true);
              }
              if ($area_raw === '' || $area_raw === null) {
                $area_raw = get_field('area', $property_id);
              }

              $bedrooms        = matrix_property_slider_clean_value($bedrooms_raw);
              $bathrooms       = matrix_property_slider_clean_value($bathrooms_raw);
              $area            = matrix_property_slider_clean_value($area_raw);
              $area            = preg_replace('/^\s*area\s*:\s*/iu', '', $area);

              $bedrooms        = $bedrooms !== '' ? $bedrooms : '0';
              $bathrooms       = $bathrooms !== '' ? $bathrooms : '0';
              $property_types  = get_the_terms($property_id, 'property_type');
              $property_type   = ($property_types && !is_wp_error($property_types)) ? $property_types[0]->name : 'Residential';

              $image_alt       = $property_image ? (get_post_meta($property_image, '_wp_attachment_image_alt', true) ?: $property_title) : $property_title;

              $excerpt_plain = preg_replace('/\s+/u', ' ', wp_strip_all_tags($property_excerpt));
              if ($excerpt_plain && preg_match('/^[€£$]?\s?\d[\d,\.\s]*[kKmM]?\s*$/u', $excerpt_plain)) {
                $property_excerpt = '';
              }
            ?>
              <article class="property-slide">
                <div class="flex overflow-hidden relative flex-col p-0 md:p-8 w-full md:min-h-[723px]  max-md:max-w-full justify-between">

                  <?php if ($property_image): ?>
                    <div class="relative inset-0 w-full h-full max-md:order-0 md:absolute">
                      <?php echo wp_get_attachment_image($property_image, 'full', false, [
                        'alt'     => esc_attr($image_alt),
                        'class'   => 'object-cover w-full h-full',
                        'loading' => 'lazy'
                      ]); ?>
                    </div>
                  <?php endif; ?>

                  <div class="max-md:order-2 relative p-8 max-w-full text-[0.9375rem] leading-6 bg-[#EDEDED] w-full md:w-[417px] max-md:px-5">
                    <h4 class="text-[#0A1119] text-[1.375rem] font-semibold leading-[1.75rem] tracking-[-0.16px] font-secondary">
                      <?php echo esc_html($property_title); ?>
                    </h4>

                    <?php if ($property_excerpt): ?>
                      <p class="mt-4 text-[#434B53] font-primary text-[0.9375rem] font-normal leading-6 tracking-normal">
                        <?php echo esc_html($property_excerpt); ?>
                      </p>
                    <?php endif; ?>

                    <a
                      href="<?php echo esc_url($property_link); ?>"
                      class="inline-block mt-4 font-primary text-[0.9375rem] font-normal leading-6 tracking-normal underline decoration-auto decoration-solid text-primary underline-offset-auto btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:no-underline"
                      aria-label="<?php echo esc_attr('Read success story for ' . $property_title); ?>"
                    >
                      Read our success story
                    </a>
                  </div>

                  <div class="flex relative z-20 flex-row flex-wrap gap-y-3 justify-between items-center px-5 py-4 mt-80 w-full property-slider__meta-bar bg-primary max-md:order-1 max-md:mt-0 max-md:max-w-full max-md:items-start md:flex-nowrap md:items-center md:gap-0 md:px-8 md:py-4">
                    <div class="flex flex-row flex-wrap flex-1 gap-4 items-center min-w-0 text-base font-semibold tracking-normal text-gray-50 max-md:w-1/2 max-md:items-start max-md:gap-4 md:flex-nowrap md:items-center md:gap-8 lg:gap-10">
                      <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap max-md:text-left"><?php echo esc_html($property_type); ?></span>

                      <div class="flex gap-2 items-center max-md:justify-start" aria-label="Bedrooms">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <path d="M1 20.6V11.8C1 11.2165 1.23178 10.657 1.64436 10.2444C2.05695 9.83179 2.61652 9.60001 3.2 9.60001H20.8C21.3835 9.60001 21.9431 9.83179 22.3556 10.2444C22.7682 10.657 23 11.2165 23 11.8V20.6" stroke="#F9FAFB" stroke-width="1.25" stroke-linecap="round"/>
                      <path d="M3.19995 9.6V5.2C3.19995 4.61652 3.43174 4.05695 3.84432 3.64436C4.2569 3.23178 4.81647 3 5.39995 3H18.6C19.1834 3 19.743 3.23178 20.1556 3.64436C20.5682 4.05695 20.8 4.61652 20.8 5.2V9.6" stroke="#F9FAFB" stroke-width="1.25" stroke-linecap="round"/>
                      <path d="M12 3V9.6" stroke="#F6FAFF" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M1 18.4H23" stroke="#F6FAFF" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                        <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap"><?php echo esc_html($bedrooms); ?></span>
                      </div>

                      <div class="flex gap-2 items-center max-md:justify-start" aria-label="Bathrooms">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M8.77772 5.33333L5.99995 2.55556C5.71501 2.2341 5.31696 2.03508 4.88883 2C3.98106 2 3.22217 2.75889 3.22217 3.66667V17.5556C3.22217 18.1449 3.45629 18.7102 3.87304 19.1269C4.28979 19.5437 4.85502 19.7778 5.44439 19.7778H18.7777C19.3671 19.7778 19.9323 19.5437 20.3491 19.1269C20.7658 18.7102 20.9999 18.1449 20.9999 17.5556V12" stroke="#F9FAFB" stroke-width="1.25" stroke-linecap="round"/>
                        <path d="M9.88885 4.22223L7.66663 6.44445" stroke="#F9FAFB" stroke-width="1.25" stroke-linecap="round"/>
                        <path d="M1 12H23.2222" stroke="#F6FAFF" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.55554 19.7778V22" stroke="#F6FAFF" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.6666 19.7778V22" stroke="#F6FAFF" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap"><?php echo esc_html($bathrooms); ?></span>
                      </div>

                      <?php if ($area): ?>
                        <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap max-md:text-left"><?php echo esc_html($area); ?></span>
                      <?php endif; ?>
                    </div>

                    <?php if ($slide_count > 1): ?>
                    <nav class="flex gap-2 items-center property-slider__desktop-nav shrink-0 max-md:hidden" aria-label="Property navigation">
                      <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition  focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119] hover:bg-blue mr-2"
                        aria-label="Previous property"
                        data-desktop-prev="<?php echo esc_attr($slider_id); ?>"
                      >
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition hover:bg-blue focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119]"
                        aria-label="Next property"
                        data-desktop-next="<?php echo esc_attr($slider_id); ?>"
                      >
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                          <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                    </nav>
                    <?php endif; ?>

                    <div class="flex gap-4 md:ml-auto md:hidden max-md:w-1/2 max-md:justify-end">
                      <button
                        type="button"
                        class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 hover:bg-blue"
                        aria-label="Previous property"
                        data-mobile-prev="<?php echo esc_attr($slider_id); ?>">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                          <rect width="40" height="40" fill="#F9FAFB"/>
                          <path d="M21.8333 15.3333L17.1666 20L21.8333 24.6667" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                      </button>

                      <button
                        type="button"
                        class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 hover:bg-blue"
                        aria-label="Next property"
                        data-mobile-next="<?php echo esc_attr($slider_id); ?>">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                          <rect width="40" height="40" fill="#F9FAFB"/>
                          <path d="M18.1667 24.6667L22.8334 20L18.1667 15.3333" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  var isBeforeAfter = <?php echo $is_before_after ? 'true' : 'false'; ?>;
  var isBaGrid = <?php echo !empty($is_ba_grid) ? 'true' : 'false'; ?>;

  function initBeforeAfterCompare(root) {
    if (!root) return;
    var compares = root.querySelectorAll('[data-ba-compare]');
    compares.forEach(function (compare) {
      if (compare.dataset.baReady === '1') return;
      compare.dataset.baReady = '1';

      var media = compare.querySelector('.ba-compare__media');
      var before = compare.querySelector('.ba-compare__before');
      var handle = compare.querySelector('[data-ba-handle]');
      var knob = compare.querySelector('[data-ba-knob]');
      if (!media || !before || !handle || !knob) return;

      var dragging = false;
      var startPct = parseFloat(compare.getAttribute('data-ba-start') || '50');
      if (isNaN(startPct)) startPct = 50;

      var setPos = function (pct) {
        pct = Math.max(0, Math.min(100, pct));
        compare.style.setProperty('--ba-pos', pct + '%');
        knob.setAttribute('aria-valuenow', String(Math.round(pct)));
      };

      setPos(startPct);

      var posFromClientX = function (clientX) {
        var rect = media.getBoundingClientRect();
        if (!rect.width) return 50;
        return ((clientX - rect.left) / rect.width) * 100;
      };

      var onPointerDown = function (e) {
        dragging = true;
        compare.classList.add('is-dragging');
        try { knob.setPointerCapture(e.pointerId); } catch (err) {}
        setPos(posFromClientX(e.clientX));
        e.preventDefault();
      };
      var onPointerMove = function (e) {
        if (!dragging) return;
        setPos(posFromClientX(e.clientX));
      };
      var onPointerUp = function (e) {
        if (!dragging) return;
        dragging = false;
        compare.classList.remove('is-dragging');
        try { knob.releasePointerCapture(e.pointerId); } catch (err) {}
      };

      knob.addEventListener('pointerdown', onPointerDown);
      knob.addEventListener('pointermove', onPointerMove);
      knob.addEventListener('pointerup', onPointerUp);
      knob.addEventListener('pointercancel', onPointerUp);

      // Allow dragging from the line/handle area too.
      handle.addEventListener('pointerdown', function (e) {
        if (e.target === knob || knob.contains(e.target)) return;
        onPointerDown(e);
      });
      handle.addEventListener('pointermove', onPointerMove);
      handle.addEventListener('pointerup', onPointerUp);

      media.addEventListener('pointerdown', function (e) {
        if (e.target.closest('[data-ba-handle]')) return;
        onPointerDown(e);
        // Keep listening on media while dragging.
      });
      media.addEventListener('pointermove', onPointerMove);
      media.addEventListener('pointerup', onPointerUp);

      knob.addEventListener('keydown', function (e) {
        var now = parseFloat(knob.getAttribute('aria-valuenow') || '50');
        var step = e.shiftKey ? 10 : 2;
        if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
          e.preventDefault();
          setPos(now - step);
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
          e.preventDefault();
          setPos(now + step);
        } else if (e.key === 'Home') {
          e.preventDefault();
          setPos(0);
        } else if (e.key === 'End') {
          e.preventDefault();
          setPos(100);
        }
      });
    });
  }

  function initPropertySliderSlick() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.slick) return;

    jQuery(function ($) {
      var $scope     = $('#<?php echo esc_js($section_id); ?>');
      var slideCount = <?php echo (int) $slide_count; ?>;
      var $slider    = $scope.find('.property-slider');

      initBeforeAfterCompare($scope.get(0));

      // 2-column grid shows all pairs — do not initialise the carousel.
      if (isBaGrid) return;

      if (!$slider.length || $slider.hasClass('slick-initialized')) return;
      if (slideCount < 2) return;

      var opts = {
        dots: false,
        arrows: false,
        speed: 450,
        cssEase: 'ease-out',
        adaptiveHeight: true,
        autoplay: !isBeforeAfter,
        autoplaySpeed: 3000,
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: false,
        variableWidth: false,
        accessibility: true,
        focusOnSelect: false,
        pauseOnHover: true,
        pauseOnFocus: false,
        // Disable swipe in before/after so the compare drag is not fighting Slick.
        swipe: !isBeforeAfter,
        touchMove: !isBeforeAfter,
        infinite: true,
        fade: true,
        waitForAnimate: false
      };

      $slider.slick(opts);
      $slider.slick('setPosition');
      if (!isBeforeAfter) {
        $slider.slick('slickPlay');
        setTimeout(function () {
          if ($slider.hasClass('slick-initialized')) {
            $slider.slick('slickNext');
          }
        }, 700);
      }

      $scope.on('click', '[data-desktop-prev="<?php echo esc_js($slider_id); ?>"], [data-mobile-prev="<?php echo esc_js($slider_id); ?>"]', function (e) {
        e.preventDefault();
        $slider.slick('slickPrev');
      });
      $scope.on('click', '[data-desktop-next="<?php echo esc_js($slider_id); ?>"], [data-mobile-next="<?php echo esc_js($slider_id); ?>"]', function (e) {
        e.preventDefault();
        $slider.slick('slickNext');
      });

      $scope.on('keydown', '[data-desktop-prev="<?php echo esc_js($slider_id); ?>"], [data-desktop-next="<?php echo esc_js($slider_id); ?>"], [data-mobile-prev="<?php echo esc_js($slider_id); ?>"], [data-mobile-next="<?php echo esc_js($slider_id); ?>"]', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          $(this).trigger('click');
        }
      });

      $slider.on('afterChange', function (event, slick, currentSlide) {
        var total = slick.slideCount, num = currentSlide + 1;
        var label = isBeforeAfter ? 'Showing project ' : 'Showing property ';
        var $sr = $('<div>', { 'aria-live':'polite', 'aria-atomic':'true', 'class':'sr-only' })
          .text(label + num + ' of ' + total);
        $('body').append($sr);
        setTimeout(function(){ $sr.remove(); }, 1000);
        // Re-init compare widgets cloned by slick if needed.
        initBeforeAfterCompare($scope.get(0));
      });
    });
  }

  function scheduleInit() {
    initPropertySliderSlick();
    setTimeout(initPropertySliderSlick, 50);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scheduleInit);
  } else {
    scheduleInit();
  }
})();
</script>

<style>
/* Scoped by section ID */
#<?php echo esc_attr($section_id); ?> .property-slider .slick-slide { outline: none; }
#<?php echo esc_attr($section_id); ?> .property-slider .slick-slide:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
#<?php echo esc_attr($section_id); ?> .opacity-50 { opacity: 0.5; }
#<?php echo esc_attr($section_id); ?> .pointer-events-none { pointer-events: none; }
#<?php echo esc_attr($section_id); ?> .property-slider .slick-list {
  position: relative;
  overflow: hidden !important;
}
#<?php echo esc_attr($section_id); ?> .property-slider .slick-track {
  gap: 0 !important;
}
#<?php echo esc_attr($section_id); ?> .property-slider__desktop-nav {
  display: none;
}
@media (min-width: 768px) {
  #<?php echo esc_attr($section_id); ?> .property-slider__desktop-nav {
    display: flex !important;
    flex-direction: row;
    align-items: center;
    gap: 0.125rem;
  }
  #<?php echo esc_attr($section_id); ?> .property-slider__desktop-nav button {
    background-color: #ffffff !important;
    color: #0A1119 !important;
    border: 1px solid rgba(10, 17, 25, 0.12) !important;
    outline: none;
  }
  #<?php echo esc_attr($section_id); ?> .property-slider__desktop-nav button:hover {
    background-color: #40BFF5 !important;
    color: #0A1119 !important;
  }
}

/* Before / After 2-column grid */
#<?php echo esc_attr($section_id); ?> .ba-grid--2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  #<?php echo esc_attr($section_id); ?> .ba-grid--2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem;
  }
}
#<?php echo esc_attr($section_id); ?> .ba-grid--2 .ba-slide--grid {
  width: 100%;
  min-width: 0;
}
#<?php echo esc_attr($section_id); ?> .ba-grid--2 .ba-text-card--grid {
  max-width: min(100%, 22rem);
}

/* Before / After compare */
#<?php echo esc_attr($section_id); ?> .ba-compare__media {
  touch-action: none;
  user-select: none;
  cursor: ew-resize;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__before {
  clip-path: inset(0 calc(100% - var(--ba-pos, 50%)) 0 0);
  z-index: 2;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__after {
  z-index: 1;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__before img,
#<?php echo esc_attr($section_id); ?> .ba-compare__after img {
  pointer-events: none;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__handle {
  position: absolute;
  top: 0;
  bottom: 0;
  left: var(--ba-pos, 50%);
  z-index: 5;
  width: 44px;
  margin-left: -22px;
  display: flex;
  align-items: center;
  justify-content: center;
  touch-action: none;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__line {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 50%;
  width: 3px;
  margin-left: -1.5px;
  background: #ffffff;
  box-shadow: 0 0 0 1px rgba(10, 17, 25, 0.15);
  pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__knob {
  position: relative;
  z-index: 1;
  width: 44px;
  height: 44px;
  border-radius: 9999px;
  border: 3px solid #ffffff;
  background: #0098d8;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(10, 17, 25, 0.28);
  cursor: ew-resize;
  touch-action: none;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__knob:focus {
  outline: 2px solid #40BFF5;
  outline-offset: 2px;
}
#<?php echo esc_attr($section_id); ?> .ba-compare.is-dragging .ba-compare__knob {
  background: #0a1119;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__label {
  position: absolute;
  top: 1rem;
  z-index: 3;
  padding: 0.35rem 0.7rem;
  background: rgba(10, 17, 25, 0.72);
  color: #ffffff;
  font-family: inherit;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__label--before {
  left: 1rem;
}
#<?php echo esc_attr($section_id); ?> .ba-compare__label--after {
  right: 1rem;
}

/* Optional title/caption card — bottom-left on the compare slide (desktop). */
@media (min-width: 768px) {
  #<?php echo esc_attr($section_id); ?> .ba-slide .ba-text-card {
    position: absolute;
    left: 2rem;
    bottom: 2rem;
    z-index: 10;
    margin: 0;
  }
  #<?php echo esc_attr($section_id); ?> .ba-slide .ba-text-card--with-nav {
    bottom: 5.5rem; /* clear the bottom nav bar */
  }
}
</style>
