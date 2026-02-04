<?php
/**
 * Frontend: Calendly Contact Section (ACF Flexi)
 * - Uses grouped fields: left_block / right_block
 * - Left media: image OR Calendly shortcode
 * - Right media: image OR video (YouTube/local) with optional poster
 * - Builds padding classes from padding_settings repeater
 * - Keeps your exact Tailwind classes/design
 */

// ===== Unique section id
$section_id = 'contact-section-' . wp_rand(1000, 999999);

// ===== Get grouped fields
$left  = get_sub_field('left_block') ?: [];
$right = get_sub_field('right_block') ?: [];

// Left fields
$l_tag       = $left['heading_tag'] ?? 'h2';
$l_text      = $left['heading_text'] ?? 'Got a question?';
$l_desc      = $left['description'] ?? '';
$l_link      = $left['cta_link'] ?? [];
$l_media     = $left['media_type'] ?? 'image';       // 'image' | 'calendly'
$l_image     = $left['image'] ?? null;               // array (url, alt, title)
$l_shortcode = $left['shortcode'] ?? '';             // WYSIWYG/textarea for Calendly

// Right fields
$r_tag     = $right['heading_tag'] ?? 'h2';
$r_text    = $right['heading_text'] ?? 'About your property';
$r_desc    = $right['description'] ?? '';
$r_link    = $right['cta_link'] ?? [];
$r_media   = $right['media_type'] ?? 'image';        // 'image' | 'video'
$r_image   = $right['image'] ?? null;                // array (url, alt, title)
$r_vtype   = $right['video_type'] ?? '';             // 'youtube' | 'local'
$r_youtube = $right['youtube_url'] ?? '';
$r_local   = $right['local_video'] ?? null;          // array (url, mime_type) if ACF return_format = array
$r_poster  = $right['poster_image'] ?? null;         // array (url, alt, title)

// ===== Build padding classes from repeater
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) { the_row();
    $screen = get_sub_field('screen_size');
    $pt     = get_sub_field('padding_top');
    $pb     = get_sub_field('padding_bottom');
    if ($screen !== null && $pt !== null) $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    if ($screen !== null && $pb !== null) $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
  }
}
$padding_class_str = esc_attr(implode(' ', $padding_classes));

// ===== Helpers
function cs_build_heading($tag, $text, $class=''){
  $allowed = ['h1','h2','h3','h4','h5','h6','span','p'];
  $tag = in_array($tag, $allowed, true) ? $tag : 'h2';
  return '<'.$tag.' class="'.esc_attr($class).'">'.esc_html($text).'</'.$tag.'>';
}
function cs_image_tag($img, $fallback_alt='Image', $fallback_title=''){
  if (!is_array($img)) return '';
  $url = $img['url'] ?? '';
  if (!$url) return '';
  $alt   = ($img['alt'] ?? '') ?: $fallback_alt;
  $title = ($img['title'] ?? '') ?: $fallback_title;
  return '<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" title="'.esc_attr($title).'" loading="lazy" decoding="async" class="object-cover w-full h-full max-md:object-contain">';
}
function cs_button_from_link($link, $classes, $fallback='Open link'){
  if (!is_array($link) || empty($link['url'])) return '';
  $label  = ($link['title'] ?? '') ?: $fallback;
  $target = $link['target'] ?? '_self';
  return '<a href="'.esc_url($link['url']).'" target="'.esc_attr($target).'" aria-label="'.esc_attr($label).'" class="'.esc_attr($classes).'">
            <span class="text-[0.875rem] font-[600] leading-[1.375rem] font-[\'Montserrat\']">'.esc_html($label).'</span>
          </a>';
}
function cs_video_local($file, $poster=null){
  if (!is_array($file) || empty($file['url'])) return '';
  $type = $file['mime_type'] ?? 'video/mp4';
  $poster_url = (is_array($poster) && !empty($poster['url'])) ? $poster['url'] : '';
  $poster_attr = $poster_url ? ' poster="'.esc_url($poster_url).'"' : '';
  return '<video class="w-full" controls preload="metadata"'.$poster_attr.'>
            <source src="'.esc_url($file['url']).'" type="'.esc_attr($type).'">
            '.esc_html__('Your browser does not support the video tag.', 'textdomain').'
          </video>';
}
function cs_video_youtube($url, $poster=null){
  $poster_url = (is_array($poster) && !empty($poster['url'])) ? $poster['url'] : '';
  if ($poster_url){
    $alt = $poster['alt'] ?? 'Video poster';
    $title = $poster['title'] ?? 'Play video';
    return '<a href="'.esc_url($url).'" target="_blank" rel="noopener" class="block w-full" aria-label="'.esc_attr__('Play video on YouTube', 'textdomain').'">
              <img src="'.esc_url($poster_url).'" alt="'.esc_attr($alt).'" title="'.esc_attr($title).'" class="object-cover w-full" loading="lazy" decoding="async">
            </a>';
  }
  $embed = function_exists('wp_oembed_get') ? wp_oembed_get($url, ['width'=>1200]) : '';
  return $embed ?: '';
}
?>

<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative max-md:overflow-visible bg-[#f9fafb]">
  <div class="w-full max-w-[80rem] mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-[3rem] pt-[2.5rem] md:pt-[5rem] pr-[5rem] pb-[2.5rem] md:pb-[5rem] pl-[5rem] max-xl:px-5 bg-[#f9fafb] bg-center h-auto md:h-[48.831875rem] w-full <?php echo $padding_class_str; ?>">

      <!-- LEFT COLUMN -->
      <div class="flex flex-col md:flex-col md:justify-start md:items-start gap-[1.5rem] overflow-hidden max-w-full self-start w-full">
        <div class="flex flex-col md:flex-col md:justify-center md:items-start gap-[1.5rem] self-start">
          <div class="flex flex-col md:flex-col md:justify-start md:items-start gap-[1.5rem] w-[33.5rem] max-w-full self-start">
            <?php
              echo cs_build_heading(
                $l_tag,
                $l_text,
                "w-[33.5rem] max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary"
              );
            ?>
            <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem] self-start">
              <div class="h-[0.3125rem] bg-[#ef7b10] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#0098d8] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#b6c0cb] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#74af27] bg-center grow basis-0 min-w-0 w-full"></div>
            </div>
          </div>
        </div>

        <?php if (!empty($l_desc)) : ?>
          <div class="wp_editor w-[33.5rem] max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d] font-primary">
            <?php echo wp_kses_post($l_desc); ?>
          </div>
        <?php endif; ?>

        <div>
          <?php
            echo cs_button_from_link(
              $l_link,
              'flex-col md:flex-row md:justify-center md:items-center pr-[2rem] pl-[2rem] bg-[#0f172a] bg-center btn inline-flex justify-center items-center gap-2 whitespace-nowrap hover:opacity-90 transition-opacity duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 px-8 py-3.5 min-h-12 lg:h-[2.75rem] lg:min-h-0 h-[2.75rem] hover:bg-[#40bff5] self-center w-full text-white hover:text-black',
              'Book now via Calendly'
            );
          ?>
        </div>

        <div class="flex overflow-hidden flex-col self-center w-full md:flex-col md:justify-center md:items-center">
          <?php
            if ($l_media === 'calendly') {
              echo '<div class="w-full wp_editor">';
              echo do_shortcode($l_shortcode);
              echo '</div>';
            } else {
              echo cs_image_tag($l_image, 'Cover image', 'Cover image');
            }
          ?>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="flex flex-col md:flex-col md:justify-start md:items-start gap-[1.5rem] overflow-hidden max-w-full self-start w-full">
        <div class="flex flex-col md:flex-col md:justify-center md:items-start gap-[1.5rem] self-start">
          <div class="flex flex-col md:flex-col md:justify-start md:items-start gap-[1.5rem] self-start">
            <?php
              echo cs_build_heading(
                $r_tag,
                $r_text,
                "w-[33.5rem] max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary"
              );
            ?>
            <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem] self-start">
              <div class="h-[0.3125rem] bg-[#ef7b10] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#0098d8] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#b6c0cb] bg-center grow basis-0 min-w-0 w-full"></div>
              <div class="h-[0.3125rem] bg-[#74af27] bg-center grow basis-0 min-w-0 w-full"></div>
            </div>
          </div>
        </div>

        <?php if (!empty($r_desc)) : ?>
          <div class="wp_editor w-[33.5rem] max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d] font-primary">
            <?php echo wp_kses_post($r_desc); ?>
          </div>
        <?php endif; ?>

        <div>
          <?php
            echo cs_button_from_link(
              $r_link,
              'flex-col md:flex-row md:justify-center md:items-center pr-[2rem] pl-[2rem] border-[0.125rem] border-[rgba(10,17,25,1)] btn inline-flex justify-center items-center gap-2 whitespace-nowrap hover:opacity-90 transition-opacity duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 px-8 py-3.5 min-h-12 lg:h-[2.75rem] lg:min-h-0 h-[2.75rem] hover:bg-[#40bff5] hover:bg-center self-center w-full',
              'Request a call'
            );
          ?>
        </div>

        <div class="flex overflow-hidden flex-col self-center w-full md:flex-col md:justify-center md:items-center">
          <?php
            if ($r_media === 'video') {
              if ($r_vtype === 'local' && $r_local) {
                echo cs_video_local($r_local, $r_poster);
              } elseif ($r_vtype === 'youtube' && $r_youtube) {
                echo cs_video_youtube($r_youtube, $r_poster);
              }
            } else {
              echo cs_image_tag($r_image, 'Cover image', 'Cover image');
            }
          ?>
        </div>
      </div>

    </div>
  </div>
</section>
