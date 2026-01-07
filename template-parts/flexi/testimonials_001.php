<?php
$section_id = 'testimonials_' . uniqid();

$heading              = get_sub_field('heading');
$heading_tag          = get_sub_field('heading_tag');
$testimonial_source   = get_sub_field('testimonial_source');
$manual_testimonials  = get_sub_field('manual_testimonials');
$number_of_testimonials = get_sub_field('number_of_testimonials') ?: 6;
$background_color     = get_sub_field('background_color');

// Build padding classes from repeater (attach to inner wrapper per spec)
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

// Get testimonials based on source
$testimonials = [];
if ($testimonial_source === 'manual' && $manual_testimonials) {
    $testimonials = $manual_testimonials;
} else {
    $posts = get_posts([
        'post_type'      => 'testimonial',
        'posts_per_page' => $number_of_testimonials,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'suppress_filters' => false,
    ]);
    foreach ($posts as $post) {
        $testimonials[] = [
            'name'        => $post->post_title,
            'title'       => get_post_meta($post->ID, 'job_title', true) ?: get_the_excerpt($post),
            'testimonial' => apply_filters('the_content', $post->post_content),
        ];
    }
}

// Safe defaults
$allowed_heading_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}
?>
<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative bg-[#F9FAFB]"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center w-full mx-auto py-20  max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <div class="mx-auto w-full max-w-7xl">
            <?php if (!empty($heading)) : ?>
                <div class="flex flex-col gap-6 items-center mb-12">
                    <<?php echo tag_escape($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-[#0A1119] text-left font-secondary text-[32px] font-semibold leading-[40px] tracking-[-0.16px]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo tag_escape($heading_tag); ?>>
 <!-- Decorative Color Bars -->
                <div
                    class="flex justify-between items-start w-[71px] max-md:flex-col max-md:gap-8 max-sm:gap-6"
                    role="presentation"
                    aria-hidden="true"
                >
                    <div class="mr-0.5 bg-orange-500 flex-1 h-[5px]"></div>
                    <div class="mr-0.5 bg-sky-500 flex-1 h-[5px] max-md:flex-col max-md:gap-8 max-sm:gap-6"></div>
                    <div class="bg-lime-600 flex-1 h-[5px]"></div>
                </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($testimonials)) : ?>
                <div class="flex gap-4 items-center md:gap-8 lg:gap-12">

                    <!-- Prev (desktop) -->
                    <button
                        aria-label="Previous testimonial"
                        class="hidden flex-shrink-0 justify-center items-center w-10 h-10 bg-gray-800 transition-colors md:flex bg-text-dark hover:bg-gray-800"
                        data-slick-prev="#<?php echo esc_attr($section_id); ?>-slider"
                        type="button"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9.83337 3.33335L5.16671 8.00002L9.83337 12.6667" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </button>

                    <!-- Slider -->
                    <div class="overflow-hidden flex-1">
                        <div
                            id="<?php echo esc_attr($section_id); ?>-slider"
                            class="testimonials-slider"
                            role="region"
                            aria-label="Customer testimonials"
                            aria-live="polite"
                        >
                            <?php foreach ($testimonials as $t) : ?>
                             <article
  class="relative flex-1 p-8 bg-white rounded-lg shadow-sm md:p-10 border border-neutral-200
         before:content-[''] before:absolute before:left-1/2 before:-translate-x-1/2
         before:w-0 before:h-0 before:border-l-[20px] before:border-r-[20px] before:border-b-[20px]
         before:border-l-transparent before:border-r-transparent before:border-b-white
         before:-top-[20px] before:z-10
         after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2
         after:w-0 after:h-0 after:border-l-[22px] after:border-r-[22px] after:border-b-[22px]
         after:border-l-transparent after:border-r-transparent after:border-b-neutral-200
         after:-top-[22px] after:z-0">
                                    <!-- Big decorative quote -->
                                    <div class="absolute -top-2 right-8 text-[#D4D4D4]  text-[80px] font-bold leading-[92px] tracking-[-0.16px] opacity-40 text-bg-medium" aria-hidden="true"><i class="fa-solid fa-quote-right"></i></div>

                                    <div class="flex relative z-10 flex-col gap-4">
                                        <?php if (!empty($t['name'])) : ?>
                                            <span class="text-[#0A1119] font-secondary text-6xl font-normal leading-[56px] tracking-[-0.16px]">
                                                <?php echo esc_html($t['name']); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($t['title'])) : ?>
                                            <p class="text-[#0A1119] font-primary text-base font-semibold leading-6 tracking-[0.08px]">
                                                <?php echo esc_html($t['title']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($t['testimonial'])) : ?>
                                            <p class="mt-2 text-base font-normal tracking-normal leading-6 text-black font-primary">
                                                <?php echo wp_kses_post($t['testimonial']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Next (desktop) -->
                    <button
                        aria-label="Next testimonial"
                        class="hidden flex-shrink-0 justify-center items-center w-10 h-10 bg-gray-800 transition-colors md:flex bg-text-dark hover:bg-gray-800"
                        data-slick-next="#<?php echo esc_attr($section_id); ?>-slider"
                        type="button"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6.16666 12.6666L10.8333 7.99998L6.16666 3.33331" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile arrows -->
                <div class="flex gap-4 justify-center mt-8 md:hidden">
                    <button
                        aria-label="Previous testimonial"
                        class="flex justify-center items-center w-10 h-10 transition-colors bg-text-dark hover:bg-gray-800"
                        data-slick-prev="#<?php echo esc_attr($section_id); ?>-slider"
                        type="button"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9.83337 3.33335L5.16671 8.00002L9.83337 12.6667" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </button>
                    <button
                        aria-label="Next testimonial"
                        class="flex justify-center items-center w-10 h-10 transition-colors bg-text-dark hover:bg-gray-800"
                        data-slick-next="#<?php echo esc_attr($section_id); ?>-slider"
                        type="button"
                    >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6.16666 12.6666L10.8333 7.99998L6.16666 3.33331" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
jQuery(function($){
  var $slider = $('#<?php echo esc_js($section_id); ?>-slider');
  if ($slider.length && !$slider.hasClass('slick-initialized')) {
    $slider.slick({
      slidesToShow: 2,              // default (above 1085px)
      slidesToScroll: 1,
      arrows: false,
      dots: false,
      infinite: true,
      autoplay: false,
      responsive: [
        { breakpoint: 1085, settings: { slidesToShow: 1, slidesToScroll: 1 } }
      ],
      accessibility: true,
      pauseOnHover: true,
      pauseOnFocus: true
    });
  }

  // Arrow bindings
  $('[data-slick-prev="#<?php echo esc_js($section_id); ?>-slider"]').on('click', function(){ $slider.slick('slickPrev'); });
  $('[data-slick-next="#<?php echo esc_js($section_id); ?>-slider"]').on('click', function(){ $slider.slick('slickNext'); });

  // Keyboard activation for arrows
  $('[data-slick-prev="#<?php echo esc_js($section_id); ?>-slider"], [data-slick-next="#<?php echo esc_js($section_id); ?>-slider"]').on('keydown', function(e){
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).click(); }
  });
});
</script>


<style>
/* keep cards equal height within slick rows */
.testimonials-slider .slick-track { display: flex; }
.testimonials-slider .slick-slide { height: auto; }
.testimonials-slider .slick-slide > div { height: 100%; }
/* Make room for the notch without showing extra slides */
.testimonials-slider .slick-list {
  padding-top: 28px; /* ~ notch height incl. border */
  overflow: hidden;  /* keep other slides hidden */
}
</style>
