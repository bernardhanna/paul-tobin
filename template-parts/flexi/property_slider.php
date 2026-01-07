<?php
// Get ACF fields
$section_heading        = get_sub_field('section_heading');
$section_heading_tag    = get_sub_field('section_heading_tag');
$background_color       = get_sub_field('background_color');
$selected_properties    = get_sub_field('selected_properties');
$auto_select_properties = get_sub_field('auto_select_properties');
$number_of_properties   = get_sub_field('number_of_properties');
$property_order         = get_sub_field('property_order');

// Padding settings
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Get properties
$properties = [];
if ($auto_select_properties) {
    $args = [
        'post_type'      => 'property',
        'posts_per_page' => $number_of_properties ?: 5,
        'post_status'    => 'publish',
        'orderby'        => $property_order === 'random' ? 'rand' : 'date',
        'order'          => $property_order === 'oldest' ? 'ASC' : 'DESC'
    ];
    $properties = get_posts($args);
} else {
    $properties = $selected_properties ?: [];
}

$slide_count = is_array($properties) ? count($properties) : 0;

// Unique IDs
$slider_id = 'property-slider-' . uniqid();
$prev_id   = $slider_id . '-prev';
$next_id   = $slider_id . '-next';
?>

<section
  id="<?php echo esc_attr($slider_id); ?>"
  class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
  style="background-color: <?php echo esc_attr($background_color); ?>;"
>
  <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">

    <?php if (!empty($section_heading)): ?>
      <header class="gap-6 w-full text-3xl font-semibold tracking-normal leading-none text-center text-primary max-md:max-w-full">
        <div class="flex flex-col gap-6 items-start w-full max-md:max-w-full">
          <<?php echo esc_attr($section_heading_tag ?: 'h2'); ?> class="text-[#0A1119] text-left font-secondary text-[32px] font-semibold leading-[40px] tracking-[-0.16px] max-md:max-w-full">
            <?php echo esc_html($section_heading); ?>
          </<?php echo esc_attr($section_heading_tag ?: 'h2'); ?>>
          <div class="mt-6 h-1 w-[71px] bg-primary" role="presentation" aria-hidden="true"></div>
        </div>
      </header>
    <?php endif; ?>

    <?php if (!empty($properties)): ?>
      <div class="mt-12 w-full property-slider-container max-md:mt-10 max-md:max-w-full">

        <!-- Slider -->
        <div class="property-slider" role="region" aria-label="Property showcase">
          <?php foreach ($properties as $property):
            $property_id     = is_object($property) ? $property->ID : $property;
            $property_post   = is_object($property) ? $property : get_post($property_id);
            $property_image  = get_post_thumbnail_id($property_id);
            $property_title  = get_the_title($property_id);
            $property_excerpt= get_the_excerpt($property_id);
            $property_link   = get_permalink($property_id);
            $bedrooms        = get_field('bedrooms', $property_id) ?: '0';
            $bathrooms       = get_field('bathrooms', $property_id) ?: '0';
            $area            = get_field('area', $property_id) ?: '';
            $property_types  = get_the_terms($property_id, 'property_type');
            $property_type   = $property_types && !is_wp_error($property_types) ? $property_types[0]->name : 'Residential';
            $image_alt       = $property_image ? (get_post_meta($property_image, '_wp_attachment_image_alt', true) ?: $property_title) : '';
          ?>
          <article class="property-slide">
            <div class="flex overflow-hidden relative flex-col p-8 w-full min-h-[723px] max-md:px-5 max-md:max-w-full">

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
                <h3 class="text-2xl font-semibold tracking-normal text-primary">
                  <?php echo esc_html($property_title); ?>
                </h3>

                <?php if ($property_excerpt): ?>
                  <p class="mt-4 tracking-normal text-gray-700">
                    <?php echo esc_html($property_excerpt); ?>
                  </p>
                <?php endif; ?>

                <a
                  href="<?php echo esc_url($property_link); ?>"
                  class="inline-block mt-4 tracking-normal leading-loose underline decoration-auto decoration-solid text-primary underline-offset-auto btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                  aria-label="<?php echo esc_attr('Read success story for ' . $property_title); ?>"
                >
                  Read our success story
                </a>
              </div>

              <footer class="flex relative flex-wrap gap-10 justify-between items-center px-8 py-4 mt-80 w-full bg-primary max-md:px-5 max-md:mt-10 max-md:max-w-full">
                <div class="flex gap-10 items-center self-stretch my-auto text-base font-semibold tracking-normal text-gray-50 whitespace-nowrap">
                  <span class="self-stretch my-auto"><?php echo esc_html($property_type); ?></span>

                  <div class="flex gap-2 justify-center items-center self-stretch my-auto" aria-label="Bedrooms">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M7 14c1.66 0 3-1.34 3-3S8.66 8 7 8s-3 1.34-3 3 1.34 3 3 3zm0-4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm12-3h-8v8H3V5H1v15h2v-3h18v3h2v-9c0-1.1-.9-2-2-2z"/>
                    </svg>
                    <span class="self-stretch my-auto"><?php echo esc_html($bedrooms); ?></span>
                  </div>

                  <div class="flex gap-2 justify-center items-center self-stretch my-auto" aria-label="Bathrooms">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M9 2v1h6V2h2v1h1c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V5c-1.1 0-.9 2 .9 2h1V2h2zm9 16V8H6v10h12z"/>
                    </svg>
                    <span class="self-stretch my-auto"><?php echo esc_html($bathrooms); ?></span>
                  </div>

                  <?php if ($area): ?>
                    <span class="self-stretch my-auto"><?php echo esc_html($area); ?></span>
                  <?php endif; ?>
                </div>
              </footer>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <!-- Single, persistent nav (works for any slide) -->
        <nav class="flex gap-4 justify-end items-center mt-4" aria-label="Property navigation">
          <button id="<?php echo esc_attr($prev_id); ?>"
                  type="button"
                  class="flex justify-center items-center w-10 h-10 bg-gray-50 btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                  aria-label="Previous property">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
          </button>

          <button id="<?php echo esc_attr($next_id); ?>"
                  type="button"
                  class="flex justify-center items-center w-10 h-10 bg-gray-50 btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
                  aria-label="Next property">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
            </svg>
          </button>
        </nav>

      </div>
    <?php endif; ?>
  </div>
</section>

<script>
/* WP-safe init + per-count options */
jQuery(function ($) {
  var sliderId   = '<?php echo esc_js($slider_id); ?>';
  var slideCount = <?php echo (int) $slide_count; ?>;
  var $slider    = $('#' + sliderId + ' .property-slider');

  if ($slider.length && $.fn.slick) {
    var opts = {
      dots: false,
      speed: 500,
      cssEase: 'linear',
      autoplay: false,
      slidesToShow: 1,
      slidesToScroll: 1,
      prevArrow: $('#' + '<?php echo esc_js($prev_id); ?>'),
      nextArrow: $('#' + '<?php echo esc_js($next_id); ?>'),
      accessibility: true,
      focusOnSelect: false,
      pauseOnHover: true,
      pauseOnFocus: true,
      swipe: true,
      touchMove: true,
      responsive: [
        {
          breakpoint: 768,
          settings: { swipe: true, touchMove: true }
        }
      ]
    };

    // Important: slick + fade + infinite behaves badly with < 3 slides.
    if (slideCount >= 3) {
      opts.fade     = true;
      opts.infinite = true;
    } else {
      opts.fade     = false; // use slide animation
      opts.infinite = false; // prevent looping quirks with 1–2 slides
    }

    $slider.slick(opts);

    // SR announcement
    $slider.on('afterChange', function (event, slick, currentSlide) {
      var total = slick.slideCount, num = currentSlide + 1;
      var $sr = $('<div>', { 'aria-live':'polite', 'aria-atomic':'true', 'class':'sr-only' })
        .text('Showing property ' + num + ' of ' + total);
      $('body').append($sr);
      setTimeout(function(){ $sr.remove(); }, 1000);
    });
  }
});
</script>

<style>
/* Keep focus styles; Slick CSS handles show/hide */
.property-slider .slick-slide { outline: none; }
.property-slider .slick-slide:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
