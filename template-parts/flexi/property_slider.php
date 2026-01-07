<?php
// =========================================================
// Flexi Block: Property Slider (full updated)
// - Single set of arrows (outside slides) to avoid Slick cloning issues
// - All slider CSS is scoped with the section ID
// =========================================================

// ACF fields
$section_heading        = get_sub_field('section_heading');
$section_heading_tag    = get_sub_field('section_heading_tag') ?: 'h2';
$background_color       = get_sub_field('background_color') ?: '#FFFFFF';

$selected_properties    = get_sub_field('selected_properties');
$auto_select_properties = get_sub_field('auto_select_properties');
$number_of_properties   = (int) get_sub_field('number_of_properties');
$property_order         = get_sub_field('property_order'); // 'latest' (default), 'oldest', 'random'

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

// Unique IDs
$section_id = 'property-slider-' . uniqid();
$slider_id  = $section_id; // same for clarity
$prev_id    = $slider_id . '-prev';
$next_id    = $slider_id . '-next';
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative bg-white flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
>
  <div class="flex flex-col items-center pt-[5rem] pb-[5rem] mx-auto w-full max-w-container max-lg:px-5">

    <?php if (!empty($section_heading)): ?>
      <header class="gap-6 w-full text-3xl font-semibold tracking-normal leading-none text-center text-primary max-md:max-w-full">
        <div class="flex flex-col gap-6 items-start w-full max-md:max-w-full">
          <<?php echo esc_attr($section_heading_tag); ?> class="text-[#0A1119] text-left font-secondary text-[32px] font-semibold leading-[40px] tracking-[-0.16px] max-md:max-w-full">
            <?php echo esc_html($section_heading); ?>
          </<?php echo esc_attr($section_heading_tag); ?>>
          <div class="flex gap-0.5 justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
            <div class="bg-orange-500 flex-1 h-[5px]"></div>
            <div class="bg-sky-500 flex-1 h-[5px]"></div>
            <div class="bg-slate-300 flex-1 h-[5px]"></div>
            <div class="bg-lime-600 flex-1 h-[5px]"></div>
          </div>
        </div>
      </header>
    <?php endif; ?>

    <?php if (!empty($properties)): ?>
      <div class="relative mt-12 w-full max-md:mt-10 max-md:max-w-full">
        <!-- Slider -->
        <div class="property-slider" role="region" aria-roledescription="carousel" aria-label="Property showcase">
          <?php foreach ($properties as $property):
            $property_id     = is_object($property) ? $property->ID : (int) $property;
            $property_post   = is_object($property) ? $property : get_post($property_id);
            $property_image  = get_post_thumbnail_id($property_id);
            $property_title  = get_the_title($property_id);
            $property_excerpt= get_the_excerpt($property_id);
            $property_link   = get_permalink($property_id);

            $bedrooms        = get_field('bedrooms', $property_id) ?: '0';
            $bathrooms       = get_field('bathrooms', $property_id) ?: '0';
            $area            = get_field('area', $property_id) ?: '';
            $property_types  = get_the_terms($property_id, 'property_type');
            $property_type   = ($property_types && !is_wp_error($property_types)) ? $property_types[0]->name : 'Residential';

            $image_alt       = $property_image ? (get_post_meta($property_image, '_wp_attachment_image_alt', true) ?: $property_title) : $property_title;
          ?>
            <article class="property-slide">
              <div class="flex overflow-hidden relative flex-col p-8 w-full min-h-[723px] max-md:px-5 max-md:max-w-full justify-between">

                <?php if ($property_image): ?>
                  <div class="absolute inset-0 w-full h-full">
                    <?php echo wp_get_attachment_image($property_image, 'full', false, [
                      'alt'     => esc_attr($image_alt),
                      'class'   => 'object-cover w-full h-full',
                      'loading' => 'lazy'
                    ]); ?>
                  </div>
                <?php endif; ?>

                <div class="relative p-8 max-w-full text-base leading-7 bg-[#F9FAFB] w-[417px] max-md:px-5">
                  <h4 class="text-[#0A1119] text-2xl font-semibold leading-[26px] tracking-[-0.16px] font-secondary">
                    <?php echo esc_html($property_title); ?>
                  </h4>

                  <?php if ($property_excerpt): ?>
                    <p class="mt-4 text-[#434B53] font-primary text-base font-normal leading-[26px] tracking-normal">
                      <?php echo esc_html($property_excerpt); ?>
                    </p>
                  <?php endif; ?>

                  <a
                    href="<?php echo esc_url($property_link); ?>"
                    class="inline-block mt-4 font-primary text-base font-normal leading-[26px] tracking-normal underline decoration-auto decoration-solid text-primary underline-offset-auto btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:no-underline"
                    aria-label="<?php echo esc_attr('Read success story for ' . $property_title); ?>"
                  >
                    Read our success story
                  </a>
                </div>

                <footer class="flex relative flex-wrap gap-10 justify-between items-center px-8 py-4 mt-80 w-full bg-primary max-md:px-5 max-md:mt-10 max-md:max-w-full">
                  <div class="flex gap-10 items-center self-stretch my-auto text-base font-semibold tracking-normal text-gray-50 whitespace-nowrap">
                    <span class="self-stretch my-auto text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px]"><?php echo esc_html($property_type); ?></span>

                    <div class="flex gap-2 justify-center items-center self-stretch my-auto" aria-label="Bedrooms">
                      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 14c1.66 0 3-1.34 3-3S8.66 8 7 8s-3 1.34-3 3 1.34 3 3 3zm0-4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm12-3h-8v8H3V5H1v15h2v-3h18v3h2v-9c0-1.1-.9-2-2-2z"/>
                      </svg>
                      <span class="self-stretch my-auto text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px]"><?php echo esc_html($bedrooms); ?></span>
                    </div>

                    <div class="flex gap-2 justify-center items-center self-stretch my-auto" aria-label="Bathrooms">
                      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 2v1h6V2h2v1h1c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V5c-1.1 0-.9 2 .9 2h1V2h2zm9 16V8H6v10h12z"/>
                      </svg>
                      <span class="self-stretch my-auto text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px]"><?php echo esc_html($bathrooms); ?></span>
                    </div>

                    <?php if ($area): ?>
                      <span class="self-stretch my-auto text-[#F9FAFB] font-primary text-base font-semibold leading-6 tracking-[0.08px]"><?php echo esc_html($area); ?></span>
                    <?php endif; ?>
                  </div>
                </footer>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <?php if ($slide_count > 1): ?>
          <!-- Single, persistent nav (outside slides so it never gets cloned) -->
          <nav class="flex absolute right-[3rem] bottom-[2.4rem] z-40 gap-4 justify-end items-center"
               aria-label="Property navigation">
            <button id="<?php echo esc_attr($prev_id); ?>"
                    type="button"
                    class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    aria-label="Previous property">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" fill="#F9FAFB"/>
                    <path d="M21.8333 15.3333L17.1666 20L21.8333 24.6667" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
                    </svg>

            </button>

            <button id="<?php echo esc_attr($next_id); ?>"
                    type="button"
                    class="flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                    aria-label="Next property">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="40" height="40" fill="#F9FAFB"/>
                <path d="M18.1667 24.6667L22.8334 20L18.1667 15.3333" stroke="#0A1119" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </nav>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
jQuery(function ($) {
  var $scope     = $('#<?php echo esc_js($section_id); ?>');
  var slideCount = <?php echo (int) $slide_count; ?>;
  var $slider    = $scope.find('.property-slider');
  var $prev      = $scope.find('#<?php echo esc_js($prev_id); ?>');
  var $next      = $scope.find('#<?php echo esc_js($next_id); ?>');

  if (!$slider.length || !$.fn.slick) return;

  var opts = {
    dots: false,
    speed: 500,
    cssEase: 'linear',
    autoplay: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    prevArrow: $prev,
    nextArrow: $next,
    accessibility: true,
    focusOnSelect: false,
    pauseOnHover: true,
    pauseOnFocus: true,
    swipe: true,
    touchMove: true
  };

  if (slideCount >= 3) {
    opts.fade     = true;
    opts.infinite = true;
  } else {
    opts.fade     = false;              // safer for 1–2 slides
    opts.infinite = (slideCount > 1);   // allow back/fwd with 2 slides
  }

  $slider.slick(opts);

  // Hide/disable arrows when only 1 slide
  if (slideCount < 2) {
    $prev.prop('disabled', true).addClass('opacity-50 pointer-events-none');
    $next.prop('disabled', true).addClass('opacity-50 pointer-events-none');
  }

  // Screen reader announcement
  $slider.on('afterChange', function (event, slick, currentSlide) {
    var total = slick.slideCount, num = currentSlide + 1;
    var $sr = $('<div>', { 'aria-live':'polite', 'aria-atomic':'true', 'class':'sr-only' })
      .text('Showing property ' + num + ' of ' + total);
    $('body').append($sr);
    setTimeout(function(){ $sr.remove(); }, 1000);
  });
});
</script>

<style>
/* All slider CSS scoped by the section ID */
#<?php echo esc_attr($section_id); ?> .property-slider .slick-slide { outline: none; }
#<?php echo esc_attr($section_id); ?> .property-slider .slick-slide:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
#<?php echo esc_attr($section_id); ?> .opacity-50 { opacity: 0.5; }
#<?php echo esc_attr($section_id); ?> .pointer-events-none { pointer-events: none; }

/* Optional: ensure the nav stays above slide content */
#<?php echo esc_attr($section_id); ?> nav[aria-label="Property navigation"] { z-index: 50; }
</style>
