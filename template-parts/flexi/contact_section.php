<?php
/**
 * Frontend: Get In Touch / Calendly Contact Section (simple)
 * Uses fields defined in your $contact_section builder (left_* / right_*, video_*).
 */

// Unique section ID
$section_id = 'contact-section-' . wp_rand(1000, 9999);

// Fetch fields (all via get_sub_field)
$left_heading       = get_sub_field('left_heading');
$left_heading_tag   = get_sub_field('left_heading_tag') ?: 'h2';
$left_description   = get_sub_field('left_description');
$left_button        = get_sub_field('left_button');
$calendly_shortcode = get_sub_field('calendly_shortcode');

$right_heading      = get_sub_field('right_heading');
$right_heading_tag  = get_sub_field('right_heading_tag') ?: 'h3';
$right_description  = get_sub_field('right_description');
$right_button       = get_sub_field('right_button');

$video_type  = get_sub_field('video_type');      // 'youtube' | 'local'
$youtube_url = get_sub_field('youtube_url');     // url
$local_video = get_sub_field('local_video');     // attachment ID

$background_color = get_sub_field('background_color') ?: '#f9fafb';

// Padding classes from repeater
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen = get_sub_field('screen_size');
        $pt     = get_sub_field('padding_top');
        $pb     = get_sub_field('padding_bottom');
        if ($screen !== null && $pt !== null) $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
        if ($screen !== null && $pb !== null) $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
    }
}
$padding_str = esc_attr(implode(' ', $padding_classes));

// Helpers
$heading_id = $left_heading ? "{$section_id}-heading" : '';
function cs_btn($link, $classes) {
    if (!is_array($link) || empty($link['url'])) return '';
    $url    = $link['url'];
    $title  = $link['title'] ?? '';
    $target = $link['target'] ?? '_self';
    $label  = $title !== '' ? $title : 'Open link';
    return '<a href="' . esc_url($url) . '" target="' . esc_attr($target) . '" aria-label="' . esc_attr($label) . '" class="' . esc_attr($classes) . '">
                <span class="text-sm font-semibold tracking-normal leading-6">' . esc_html($label) . '</span>
            </a>';
}
?>
<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo $padding_str; ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    <?php echo $heading_id ? 'aria-labelledby="' . esc_attr($heading_id) . '"' : ''; ?>
>
  <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
    <div class="box-border flex justify-between mx-auto my-0 w-full max-w-screen-xl bg-gray-50 max-md:flex-col max-sm:flex-col">

      <!-- Left Column -->
      <div class="box-border flex flex-1 gap-12 items-start p-20 bg-gray-50 max-md:p-12 max-sm:p-6">
        <div class="flex overflow-hidden flex-col flex-1 gap-6 items-start">

          <!-- Left Heading -->
          <header class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
            <div class="flex flex-col gap-6 items-start w-full">
              <?php if (!empty($left_heading)) : ?>
                <<?php echo esc_attr($left_heading_tag); ?>
                  id="<?php echo esc_attr($heading_id); ?>"
                  class="text-3xl font-semibold tracking-normal leading-10 text-slate-950"
                ><?php echo esc_html($left_heading); ?></<?php echo esc_attr($left_heading_tag); ?>>
              <?php endif; ?>

              <!-- Decorative bar -->
              <div class="flex justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                <div class="bg-lime-600 flex-1 h-[5px]"></div>
              </div>
            </div>
          </header>

          <!-- Left Description -->
          <?php if (!empty($left_description)) : ?>
            <div class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 wp_editor">
              <?php echo wp_kses_post($left_description); ?>
            </div>
          <?php endif; ?>

          <!-- Left Button -->
          <?php
          echo cs_btn(
            $left_button,
            'box-border flex gap-2.5 justify-center items-center px-2 py-0 h-11 whitespace-nowrap transition-colors duration-300 cursor-pointer max-md:w-full bg-[#0A1119] max-sm:h-12 btn w-fit hover:bg-[#40BFF5] hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119] text-slate-50'
          );
          ?>

          <!-- Calendly Shortcode -->
          <?php if (!empty($calendly_shortcode)) : ?>
            <div class="flex overflow-hidden flex-col justify-center items-center w-full wp_editor">
              <?php echo do_shortcode($calendly_shortcode); ?>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Right Column -->
      <div class="box-border flex flex-1 gap-12 items-start p-20 bg-gray-50 max-md:p-12 max-sm:p-6">
        <div class="flex overflow-hidden flex-col flex-1 gap-6 items-start">

          <!-- Right Heading -->
          <header class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
            <div class="flex flex-col gap-6 items-start w-full">
              <?php if (!empty($right_heading)) : ?>
                <<?php echo esc_attr($right_heading_tag); ?>
                  class="text-3xl font-semibold tracking-normal leading-10 text-slate-950"
                ><?php echo esc_html($right_heading); ?></<?php echo esc_attr($right_heading_tag); ?>>
              <?php endif; ?>

              <!-- Decorative bar -->
              <div class="flex justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                <div class="bg-lime-600 flex-1 h-[5px]"></div>
              </div>
            </div>
          </header>

          <!-- Right Description -->
          <?php if (!empty($right_description)) : ?>
            <div class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 wp_editor">
              <?php echo wp_kses_post($right_description); ?>
            </div>
          <?php endif; ?>

          <!-- Right Button -->
          <?php
          echo cs_btn(
            $right_button,
            'box-border flex gap-2.5 justify-center items-center px-2 py-0 h-11 whitespace-nowrap border-2 border-solid transition-colors duration-300 cursor-pointer border-slate-950 max-sm:h-12 btn w-fit hover:bg-slate-950 hover:text-white focus:ring-2 focus:ring-offset-2 focus:ring-slate-950 text-slate-950'
          );
          ?>

          <!-- Video -->
          <div class="flex overflow-hidden flex-col justify-center items-center w-full">
            <?php if ($video_type === 'youtube' && !empty($youtube_url)) : ?>
              <?php
              // Extract YouTube ID
              preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $m);
              $youtube_id = isset($m[1]) ? $m[1] : '';
              ?>
              <?php if ($youtube_id) : ?>
                <div class="relative w-full h-0 pb-[56.25%]">
                  <iframe
                    class="object-cover absolute top-0 left-0 w-full h-full"
                    src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo esc_attr($youtube_id); ?>"
                    title="<?php echo esc_attr($right_heading ?: 'Property video'); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                  ></iframe>
                </div>
              <?php endif; ?>
            <?php elseif ($video_type === 'local' && !empty($local_video)) : ?>
              <?php
              $video_url  = wp_get_attachment_url($local_video);
              $video_mime = get_post_mime_type($local_video);
              ?>
              <?php if ($video_url) : ?>
                <video
                  class="object-cover w-full h-auto"
                  autoplay
                  muted
                  loop
                  playsinline
                  preload="metadata"
                  aria-label="<?php echo esc_attr($right_heading ?: 'Property showcase video'); ?>"
                >
                  <source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($video_mime); ?>">
                  <p><?php esc_html_e('Your browser does not support the video tag.', 'textdomain'); ?></p>
                </video>
              <?php endif; ?>
            <?php else : ?>
              <div class="flex justify-center items-center w-full h-64 bg-gray-200 rounded">
                <p class="text-gray-500"><?php esc_html_e('Video content will appear here', 'textdomain'); ?></p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>
