<?php
// =========================================================
// Flexi Block: Property Slider (full updated)
// =========================================================

// ACF fields
$section_heading        = get_sub_field('section_heading');
$section_heading_tag    = get_sub_field('section_heading_tag') ?: 'h2';
$background_color       = get_sub_field('background_color') ?: '#FFFFFF';

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

// Collect properties
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

// Unique IDs
$section_id = 'property-slider-' . uniqid();
$slider_id  = $section_id;
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative bg-white flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
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

    <?php if (!empty($properties)): ?>
      <div class="relative mt-12 w-full max-md:mt-5 max-md:max-w-full">
        <!-- Slider -->
        <div class="property-slider" role="region" aria-roledescription="carousel" aria-label="Property showcase">
          <?php foreach ($properties as $property):
            $property_id     = is_object($property) ? $property->ID : (int) $property;
            $property_post   = is_object($property) ? $property : get_post($property_id);
            $property_image  = get_post_thumbnail_id($property_id);
            $property_title  = get_the_title($property_id);
            $property_excerpt= trim((string) get_the_excerpt($property_id));
            $property_link   = get_permalink($property_id);

            // Prefer synced post meta (source of truth used by Daft sync), then fallback to ACF field values.
            $bedrooms_raw    = get_post_meta($property_id, 'bedrooms', true);
            if ($bedrooms_raw === '' || $bedrooms_raw === null) {
              $bedrooms_raw = get_field('bedrooms', $property_id);
            }
            $bathrooms_raw   = get_post_meta($property_id, 'bathrooms', true);
            if ($bathrooms_raw === '' || $bathrooms_raw === null) {
              $bathrooms_raw = get_field('bathrooms', $property_id);
            }
            $area_raw        = get_post_meta($property_id, 'area', true);
            if ($area_raw === '' || $area_raw === null) {
              $area_raw = get_field('area', $property_id);
            }

            $bedrooms        = matrix_property_slider_clean_value($bedrooms_raw);
            $bathrooms       = matrix_property_slider_clean_value($bathrooms_raw);
            $area            = matrix_property_slider_clean_value($area_raw);
            // Normalize area labels like "Area: 65 m2" -> "65 m2".
            $area            = preg_replace('/^\s*area\s*:\s*/iu', '', $area);

            $bedrooms        = $bedrooms !== '' ? $bedrooms : '0';
            $bathrooms       = $bathrooms !== '' ? $bathrooms : '0';
            $property_types  = get_the_terms($property_id, 'property_type');
            $property_type   = ($property_types && !is_wp_error($property_types)) ? $property_types[0]->name : 'Residential';

            $image_alt       = $property_image ? (get_post_meta($property_image, '_wp_attachment_image_alt', true) ?: $property_title) : $property_title;

            // Hide excerpt when it only looks like a price/value (e.g. "€1,926,000").
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

                <div class="property-slider__meta-bar relative z-20 mt-80 flex w-full flex-row flex-wrap items-center justify-between gap-y-3 bg-primary px-5 py-4 max-md:order-1 max-md:mt-0 max-md:max-w-full max-md:items-start md:flex-nowrap md:items-center md:gap-0 md:px-8 md:py-4">
                  <div class="flex min-w-0 flex-1 flex-row flex-wrap items-center gap-4 text-base font-semibold tracking-normal text-gray-50 max-md:w-1/2 max-md:flex-col max-md:items-start max-md:gap-4 md:flex-nowrap md:items-center md:gap-8 lg:gap-10">
                    <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap max-md:text-left"><?php echo esc_html($property_type); ?></span>

                    <div class="flex items-center gap-2 max-md:justify-start" aria-label="Bedrooms">
                      <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 14c1.66 0 3-1.34 3-3S8.66 8 7 8s-3 1.34-3 3 1.34 3 3 3zm0-4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm12-3h-8v8H3V5H1v15h2v-3h18v3h2v-9c0-1.1-.9-2-2-2z"/>
                      </svg>
                      <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap"><?php echo esc_html($bedrooms); ?></span>
                    </div>

                    <div class="flex items-center gap-2 max-md:justify-start" aria-label="Bathrooms">
                      <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 2v1h6V2h2v1h1c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V5c-1.1 0-.9 2 .9 2h1V2h2zm9 16V8H6v10h12z"/>
                      </svg>
                      <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap"><?php echo esc_html($bathrooms); ?></span>
                    </div>

                    <?php if ($area): ?>
                      <span class="text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px] whitespace-nowrap max-md:text-left"><?php echo esc_html($area); ?></span>
                    <?php endif; ?>
                  </div>

                  <?php if ($slide_count > 1): ?>
                  <nav class="property-slider__desktop-nav flex shrink-0 items-center gap-0.5 max-md:hidden" aria-label="Property navigation">
                    <button
                      type="button"
                      class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition hover:bg-[#F9FAFB] focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119]"
                      aria-label="Previous property"
                      data-desktop-prev="<?php echo esc_attr($slider_id); ?>"
                    >
                      <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                    <button
                      type="button"
                      class="flex h-10 w-10 shrink-0 items-center justify-center bg-white text-[#0A1119] shadow-sm ring-1 ring-white/30 transition hover:bg-[#F9FAFB] focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A1119]"
                      aria-label="Next property"
                      data-desktop-next="<?php echo esc_attr($slider_id); ?>"
                    >
                      <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </nav>
                  <?php endif; ?>

                  <!-- Mobile slide arrows -->
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
                  <!-- /mobile arrows -->
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function () {
  function initPropertySliderSlick() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.slick) return;

    jQuery(function ($) {
      var $scope     = $('#<?php echo esc_js($section_id); ?>');
      var slideCount = <?php echo (int) $slide_count; ?>;
      var $slider    = $scope.find('.property-slider');

      if (!$slider.length || $slider.hasClass('slick-initialized')) return;
      if (slideCount < 2) return;

    var opts = {
      dots: false,
      arrows: false,
      speed: 450,
      cssEase: 'ease-out',
      autoplay: true,
      autoplaySpeed: 3000,
      slidesToShow: 1,
      slidesToScroll: 1,
      centerMode: false,
      variableWidth: false,
      accessibility: true,
      focusOnSelect: false,
      pauseOnHover: true,
      pauseOnFocus: false,
      swipe: true,
      touchMove: true,
      infinite: true,
      fade: true,
      waitForAnimate: false
    };

    $slider.slick(opts);

    $slider.slick('setPosition');
    $slider.slick('slickPlay');
    // Kick off first movement quickly, then continue with autoplaySpeed cadence.
    setTimeout(function () {
      if ($slider.hasClass('slick-initialized')) {
        $slider.slick('slickNext');
      }
    }, 700);

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
      var $sr = $('<div>', { 'aria-live':'polite', 'aria-atomic':'true', 'class':'sr-only' })
        .text('Showing property ' + num + ' of ' + total);
      $('body').append($sr);
      setTimeout(function(){ $sr.remove(); }, 1000);
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
/* Desktop nav: avoid display:none !important from .hidden / theme nav rules beating Tailwind md:flex */
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
  }
  #<?php echo esc_attr($section_id); ?> .property-slider__desktop-nav button:hover {
    background-color: #F9FAFB !important;
    color: #0A1119 !important;
  }
}
</style>
