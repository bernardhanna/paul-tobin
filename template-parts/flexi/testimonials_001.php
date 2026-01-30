<?php
$section_id = 'testimonials_' . uniqid();

$heading                = get_sub_field('heading');
$heading_tag            = get_sub_field('heading_tag');
$testimonial_source     = get_sub_field('testimonial_source');
$manual_testimonials    = get_sub_field('manual_testimonials');
$number_of_testimonials = get_sub_field('number_of_testimonials') ?: 6;
$background_color       = get_sub_field('background_color'); // hex or rgba from ACF

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
        'post_type'        => 'testimonial',
        'posts_per_page'   => $number_of_testimonials,
        'post_status'      => 'publish',
        'orderby'          => 'date',
        'order'            => 'DESC',
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

// Build bg style (use ACF value, fallback to previous #F9FAFB)
$bg_style = 'background-color: ' . esc_attr($background_color ?: '#F9FAFB') . ';';
?>
<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
    style="<?php echo $bg_style; ?>"
>
    <div class="flex flex-col items-center w-full mx-auto py-8 md:py-20 max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <div class="mx-auto w-full max-w-7xl">
            <?php if (!empty($heading)) : ?>
                <div class="flex flex-col gap-6 items-center mmb-12">
                    <<?php echo tag_escape($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-[2.125rem] font-semibold tracking-normal leading-10 text-center font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo tag_escape($heading_tag); ?>>

                    <div class="flex lg:pb-8 justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($testimonials)) : ?>
                <div class="flex gap-4 items-center md:gap-8 lg:gap-12">

                    <!-- Prev (desktop) -->
                    <button
                        aria-label="Previous testimonial"
                        class="hidden flex-shrink-0 justify-center items-center w-10 h-10 bg-[#0A1119] transition-colors md:flex bg-text-dark hover:bg-[#40BFF5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
                                <div class="flex flex-col h-full">
                                    <article
                                        class="relative flex-1 p-8 bg-white  shadow-sm md:p-10 border border-neutral-200
                                               before:content-[''] before:absolute before:left-1/2 before:-translate-x-1/2
                                               before:w-0 before:h-0 before:border-l-[20px] before:border-r-[20px] before:border-b-[20px]
                                               before:border-l-transparent before:border-r-transparent before:border-b-white
                                               before:-top-[20px] before:z-10
                                               after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2
                                               after:w-0 after:h-0 after:border-l-[22px] after:border-r-[22px] after:border-b-[22px]
                                               after:border-l-transparent after:border-r-transparent after:border-b-neutral-200
                                               after:-top-[22px] after:z-0">

                                        <div class="flex relative z-10 flex-col gap-4">

                                            <?php if (!empty($t['testimonial'])) : ?>
                                                <div class="flex flex-row">
                                                    <p class="mt-2 text-base font-normal tracking-normal leading-6 text-[#0A1119] font-primary">
                                                            <?php echo wp_kses_post($t['testimonial']); ?>
                                                        </p>
                                                    <div class="arelative -top-2 right-0 text-[#D4D4D4] text-[80px] font-bold leading-[92px] tracking-[-0.16px] opacity-40" aria-hidden="true">
                                                       <svg width="35" height="28" viewBox="0 0 35 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0.800195 27.2L6.0802 8L7.8402 15.28C5.54686 15.28 3.65353 14.6133 2.1602 13.28C0.720195 11.8933 0.000195324 10.0267 0.000195324 7.68C0.000195324 5.38666 0.746862 3.54666 2.2402 2.16C3.73353 0.719995 5.57353 -4.76837e-06 7.7602 -4.76837e-06C10.0002 -4.76837e-06 11.8402 0.719995 13.2802 2.16C14.7202 3.54666 15.4402 5.38666 15.4402 7.68C15.4402 8.37333 15.3869 9.06666 15.2802 9.76C15.2269 10.4 15.0402 11.1733 14.7202 12.08C14.4535 12.9867 14.0002 14.1867 13.3602 15.68L8.7202 27.2H0.800195ZM19.6802 27.2L24.9602 8L26.7202 15.28C24.4269 15.28 22.5335 14.6133 21.0402 13.28C19.6002 11.8933 18.8802 10.0267 18.8802 7.68C18.8802 5.38666 19.6269 3.54666 21.1202 2.16C22.6135 0.719995 24.4535 -4.76837e-06 26.6402 -4.76837e-06C28.8802 -4.76837e-06 30.7202 0.719995 32.1602 2.16C33.6002 3.54666 34.3202 5.38666 34.3202 7.68C34.3202 8.37333 34.2669 9.06666 34.1602 9.76C34.1069 10.4 33.9202 11.1733 33.6002 12.08C33.3335 12.9867 32.8802 14.1867 32.2402 15.68L27.6002 27.2H19.6802Z" fill="#D4D4D4"/>
                                                        </svg>
                                                    </div>
                                              </div>
                                            <?php endif; ?>

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
                                        </div>
                                    </article>

                                    <!-- Mobile arrows -->
                                    <div class="flex gap-4 justify-center mt-6 md:hidden">
                                        <button
                                            aria-label="Previous testimonial"
                                            class="flex justify-center items-center w-10 h-10 transition-colors bg-text-dark hover:bg-[#0A1119]"
                                            data-slick-prev="#<?php echo esc_attr($section_id); ?>-slider"
                                            type="button"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M9.83337 3.33335L5.16671 8.00002L9.83337 12.6667" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                                            </svg>
                                        </button>
                                        <button
                                            aria-label="Next testimonial"
                                            class="flex justify-center items-center w-10 h-10 transition-colors bg-text-dark hover:bg-[#0A1119]"
                                            data-slick-next="#<?php echo esc_attr($section_id); ?>-slider"
                                            type="button"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M6.16666 12.6666L10.8333 7.99998L6.16666 3.33331" stroke="#F6FAFF" stroke-width="2" stroke-linecap="round"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Next (desktop) -->
                    <button
                        aria-label="Next testimonial"
                        class="hidden flex-shrink-0 justify-center items-center w-10 h-10 bg-[#0A1119] transition-colors md:flex bg-text-dark  hover:bg-[#40BFF5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
      slidesToShow: 2, // desktop
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

  $('[data-slick-prev="#<?php echo esc_js($section_id); ?>-slider"]').on('click', function(){ $slider.slick('slickPrev'); });
  $('[data-slick-next="#<?php echo esc_js($section_id); ?>-slider"]').on('click', function(){ $slider.slick('slickNext'); });

  $('[data-slick-prev="#<?php echo esc_js($section_id); ?>-slider"], [data-slick-next="#<?php echo esc_js($section_id); ?>-slider"]').on('keydown', function(e){
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).click(); }
  });
});
</script>

<style>
.testimonials-slider .slick-track { display: flex; }
.testimonials-slider .slick-slide { height: auto; }
.testimonials-slider .slick-slide > div { height: 100%; }
.testimonials-slider .slick-list {
  padding-top: 28px;
  overflow: hidden;
}
</style>
