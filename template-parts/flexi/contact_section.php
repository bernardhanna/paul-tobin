<?php
/**
 * Frontend: Calendly Contact Section (ACF Flexi)
 * Standardized wrapper + padding repeater + ACF Link array button.
 */

// Random section id (required)
$section_id = 'contact-section-' . wp_rand(1000, 999999);

// Pull groups via get_sub_field (never get_field)
$left  = get_sub_field('left_block') ?: [];
$right = get_sub_field('right_block') ?: [];

/** LEFT **/
$l_tag       = isset($left['heading_tag']) ? $left['heading_tag'] : 'h2';
$l_text      = isset($left['heading_text']) ? $left['heading_text'] : 'Got a question?';
$l_desc      = isset($left['description']) ? $left['description'] : '';
$l_link      = isset($left['cta_link']) ? $left['cta_link'] : [];
$l_media     = isset($left['media_type']) ? $left['media_type'] : 'image';
$l_image     = isset($left['image']) ? $left['image'] : null;
$l_shortcode = isset($left['shortcode']) ? $left['shortcode'] : '';

/** RIGHT **/
$r_tag     = isset($right['heading_tag']) ? $right['heading_tag'] : 'h2';
$r_text    = isset($right['heading_text']) ? $right['heading_text'] : 'About your property';
$r_desc    = isset($right['description']) ? $right['description'] : '';
$r_link    = isset($right['cta_link']) ? $right['cta_link'] : [];
$r_media   = isset($right['media_type']) ? $right['media_type'] : 'image';
$r_image   = isset($right['image']) ? $right['image'] : null;
$r_vtype   = isset($right['video_type']) ? $right['video_type'] : '';
$r_youtube = isset($right['youtube_url']) ? $right['youtube_url'] : '';
$r_local   = isset($right['local_video']) ? $right['local_video'] : null;
$r_poster  = isset($right['poster_image']) ? $right['poster_image'] : null;

/** MAP (Jawg) **/
$r_lat   = isset($right['map_latitude'])  ? (float) $right['map_latitude']  : 0.0;
$r_lng   = isset($right['map_longitude']) ? (float) $right['map_longitude'] : 0.0;
$r_zoom  = isset($right['map_zoom'])      ? (int)   $right['map_zoom']      : 15;
$r_token = isset($right['jawg_access_token']) ? $right['jawg_access_token'] : '';
$needs_leaflet = ($r_media === 'map_jawg' && $r_lat && $r_lng && $r_token !== '');

// Build padding classes (repeater)
$padding_classes = array();
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top     = get_sub_field('padding_top');
        $padding_bottom  = get_sub_field('padding_bottom');

        if (!empty($screen_size) && $padding_top !== null) {
            $padding_classes[] = $screen_size . ':pt-[' . $padding_top . 'rem]';
        }
        if (!empty($screen_size) && $padding_bottom !== null) {
            $padding_classes[] = $screen_size . ':pb-[' . $padding_bottom . 'rem]';
        }
    }
}
$padding_class_str = esc_attr(implode(' ', $padding_classes));

// Enqueue Leaflet if map needed
if ($needs_leaflet) {
    wp_enqueue_style('leaflet');
    wp_enqueue_script('leaflet');
}

// Helpers
function cs_build_heading_std($tag, $text, $class = '') {
    $allowed = array('h1','h2','h3','h4','h5','h6','span','p');
    $tag = in_array($tag, $allowed, true) ? $tag : 'h2';
    return '<' . $tag . ' class="' . esc_attr($class) . '">' . esc_html($text) . '</' . $tag . '>';
}

function cs_image_tag_std($img, $fallback_alt = 'Image', $fallback_title = '') {
    if (!is_array($img)) {
        return '';
    }
    $url = isset($img['url']) ? $img['url'] : '';
    if (empty($url)) {
        return '';
    }
    $alt   = !empty($img['alt']) ? $img['alt'] : $fallback_alt;
    $title = !empty($img['title']) ? $img['title'] : $fallback_title;
    return '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" title="' . esc_attr($title) . '" loading="lazy" decoding="async" class="object-cover w-full">';
}

function cs_video_local_std($file, $poster = null) {
    if (!is_array($file) || empty($file['url'])) {
        return '';
    }
    $type = !empty($file['mime_type']) ? $file['mime_type'] : 'video/mp4';
    $poster_url = (is_array($poster) && !empty($poster['url'])) ? $poster['url'] : '';
    $poster_attr = $poster_url ? ' poster="' . esc_url($poster_url) . '"' : '';
    return '<video class="w-full h-auto" controls preload="metadata"' . $poster_attr . '>
                <source src="' . esc_url($file['url']) . '" type="' . esc_attr($type) . '">
                ' . esc_html__('Your browser does not support the video tag.', 'textdomain') . '
            </video>';
}

function cs_video_youtube_std($url, $poster = null) {
    $poster_url = (is_array($poster) && !empty($poster['url'])) ? $poster['url'] : '';
    if (!empty($poster_url)) {
        $alt = isset($poster['alt']) ? $poster['alt'] : 'Video poster';
        $title = isset($poster['title']) ? $poster['title'] : 'Play video';
        return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener" class="block w-full" aria-label="' . esc_attr__('Play video on YouTube', 'textdomain') . '">
                    <img src="' . esc_url($poster_url) . '" alt="' . esc_attr($alt) . '" title="' . esc_attr($title) . '" class="object-cover w-full" loading="lazy" decoding="async">
                </a>';
    }
    $embed = function_exists('wp_oembed_get') ? wp_oembed_get($url, array('width' => 1200)) : '';
    return !empty($embed) ? $embed : '';
}

// Unique button classes for hover/focus compliance
$l_btn_class = 'btn-' . $section_id . '-l';
$r_btn_class = 'btn-' . $section_id . '-r';

// Base button colors (kept from original visual intent)
$l_base_bg     = '#0f172a';
$l_base_text   = '#ffffff';
$l_base_border = 'transparent';

$r_base_bg     = 'transparent';
$r_base_text   = '#0a1119';
$r_base_border = '#0a1119';

// Hover/focus colors (from original accents)
$l_hover_bg     = '#0098d8';
$l_hover_text   = '#ffffff';
$l_hover_border = '#0098d8';

$r_hover_bg     = '#0098d8';
$r_hover_text   = '#ffffff';
$r_hover_border = '#0098d8';
?>

<section id="<?php echo esc_attr($section_id); ?>" class="relative flex  bg-[#f9fafb]">
  <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5 <?php echo $padding_class_str; ?>">

    <!-- Keep original inner width + grid layout -->
    <div class="w-full max-w-[80rem]">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-[3rem]  pt-[5rem] bg-[#f9fafb] bg-center w-[80rem] max-w-full">

        <!-- LEFT COLUMN -->
        <div class="flex flex-col gap-[1.5rem]  max-w-full self-start">

          <!-- Heading -->
          <div class="flex flex-col gap-[1.5rem] max-w-full h-[4.3125rem]">
            <?php
              echo cs_build_heading_std(
                  $l_tag,
                  $l_text,
                  'max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary'
              );
            ?>
            <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem]" aria-hidden="true">
              <div class="grow basis-0 h-[0.3125rem] bg-[#ef7b10]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#0098d8]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#b6c0cb]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#74af27]"></div>
            </div>
          </div>

          <?php if (!empty($l_desc)) : ?>
            <div class="wp_editor max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d]">
              <?php echo wp_kses_post($l_desc); ?>
            </div>
          <?php endif; ?>

          <?php if (is_array($l_link) && !empty($l_link['url'])) : ?>
            <a
              href="<?php echo esc_url($l_link['url']); ?>"
              target="<?php echo esc_attr(!empty($l_link['target']) ? $l_link['target'] : '_self'); ?>"
              aria-label="<?php echo esc_attr(!empty($l_link['title']) ? $l_link['title'] : 'Open link'); ?>"
              class="<?php echo esc_attr($l_btn_class); ?> inline-flex w-full h-[44px] justify-center items-center gap-2 px-8 py-3.5 font-[600] transition-opacity duration-200 group"
              style="background-color: <?php echo esc_attr($l_base_bg); ?>; color: <?php echo esc_attr($l_base_text); ?>; border: 0.125rem solid <?php echo esc_attr($l_base_border); ?>;"
            >
              <span class="text-[0.875rem] leading-[1.375rem] font-primary group-hover:text-black">
                <?php echo esc_html(!empty($l_link['title']) ? $l_link['title'] : 'Book now via Calendly'); ?>
              </span>
            </a>
            <style>
              .<?php echo esc_attr($l_btn_class); ?>:hover,
              .<?php echo esc_attr($l_btn_class); ?>:focus {
                background-color: <?php echo esc_attr($l_hover_bg); ?> !important;
                color: <?php echo esc_attr($l_hover_text); ?> !important;
                border-color: <?php echo esc_attr($l_hover_border); ?> !important;
                outline: 2px solid <?php echo esc_attr($l_hover_border); ?>;
                outline-offset: 2px;
              }
              .<?php echo esc_attr($l_btn_class); ?>:hover svg path,
              .<?php echo esc_attr($l_btn_class); ?>:focus svg path {
                stroke: <?php echo esc_attr($l_hover_text); ?>;
              }
            </style>
          <?php endif; ?>

          <div class="self-center max-w-full">
            <?php
              if ($l_media === 'calendly') {
                  echo '<div class="w-full h-full wp_editor">';
                  echo do_shortcode($l_shortcode);
                  echo '</div>';
              } else {
                  echo cs_image_tag_std($l_image, 'Cover', 'Cover');
              }
            ?>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="flex flex-col gap-[1.5rem]  max-w-full self-start">

          <div class="flex flex-col gap-[1.5rem] max-w-full h-[4.3125rem]">
            <?php
              echo cs_build_heading_std(
                  $r_tag,
                  $r_text,
                  'max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary'
              );
            ?>
            <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem]" aria-hidden="true">
              <div class="grow basis-0 h-[0.3125rem] bg-[#ef7b10]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#0098d8]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#b6c0cb]"></div>
              <div class="grow basis-0 h-[0.3125rem] bg-[#74af27]"></div>
            </div>
          </div>

          <?php if (!empty($r_desc)) : ?>
            <div class="wp_editor max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d]">
              <?php echo wp_kses_post($r_desc); ?>
            </div>
          <?php endif; ?>

          <?php if (is_array($r_link) && !empty($r_link['url'])) : ?>
            <a
              href="<?php echo esc_url($r_link['url']); ?>"
              target="<?php echo esc_attr(!empty($r_link['target']) ? $r_link['target'] : '_self'); ?>"
              aria-label="<?php echo esc_attr(!empty($r_link['title']) ? $r_link['title'] : 'Open link'); ?>"
              class="<?php echo esc_attr($r_btn_class); ?> inline-flex w-full h-[44px] justify-center items-center gap-2 px-8 py-3.5 font-[600] transition-opacity duration-200 border-[0.125rem] group"
              style="background-color: <?php echo esc_attr($r_base_bg); ?>; color: <?php echo esc_attr($r_base_text); ?>; border-color: <?php echo esc_attr($r_base_border); ?>;"
            >
              <span class="text-[0.875rem] leading-[1.375rem] font-primary group-hover:text-black">
                <?php echo esc_html(!empty($r_link['title']) ? $r_link['title'] : 'Request a call'); ?>
              </span>
            </a>
            <style>
              .<?php echo esc_attr($r_btn_class); ?>:hover,
              .<?php echo esc_attr($r_btn_class); ?>:focus {
                background-color: <?php echo esc_attr($r_hover_bg); ?> !important;
                color: <?php echo esc_attr($r_hover_text); ?> !important;
                border-color: <?php echo esc_attr($r_hover_border); ?> !important;
                outline: 2px solid <?php echo esc_attr($r_hover_border); ?>;
                outline-offset: 2px;
              }
              .<?php echo esc_attr($r_btn_class); ?>:hover svg path,
              .<?php echo esc_attr($r_btn_class); ?>:focus svg path {
                stroke: <?php echo esc_attr($r_hover_text); ?>;
              }
            </style>
          <?php endif; ?>

          <div class="self-center max-w-full">
            <?php
              if ($r_media === 'video') {
                  if ($r_vtype === 'local' && $r_local) {
                      echo cs_video_local_std($r_local, $r_poster);
                  } elseif ($r_vtype === 'youtube' && $r_youtube) {
                      echo cs_video_youtube_std($r_youtube, $r_poster);
                  } else {
                      echo '<div class="w-full h-full bg-[#e5e7eb]"></div>';
                  }
              } elseif ($r_media === 'map_jawg') {
                  if ($needs_leaflet) {
                      $map_id = $section_id . '__map';
                      echo '<div id="' . esc_attr($map_id) . '" class="w-full h-full"></div>';
                      ?>
                      <script>
                        document.addEventListener('DOMContentLoaded', function() {
                          if (typeof L === 'undefined') { return; }

                          var map = L.map('<?php echo esc_js($map_id); ?>', {
                            scrollWheelZoom: false,
                            zoomControl: true
                          }).setView(
                            [<?php echo esc_js($r_lat); ?>, <?php echo esc_js($r_lng); ?>],
                            <?php echo esc_js($r_zoom ? $r_zoom : 15); ?>
                          );

                          L.tileLayer('https://{s}.tile.jawg.io/jawg-streets/{z}/{x}/{y}{r}.png?access-token=<?php echo esc_js($r_token); ?>', {
                            attribution: 'Map data © OpenStreetMap contributors, © <a href="https://www.jawg.io/" target="_blank" rel="noopener">JawgMaps</a>',
                            subdomains: 'abcd',
                            maxZoom: 22
                          }).addTo(map);

                          setTimeout(function(){ map.invalidateSize(); }, 200);
                        });
                      </script>
                      <?php
                  } else {
                      echo '<div class="w-full h-full bg-[#e5e7eb]"></div>';
                  }
              } else {
                  echo cs_image_tag_std($r_image, 'Cover', 'Cover');
              }
            ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
