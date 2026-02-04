<?php
/**
 * Frontend: Calendly Contact Section (ACF Flexi)
 * Exact design/classes preserved. Jawg Streets map option on right media.
 */

// Random section id
$section_id = 'contact-section-' . wp_rand(1000, 999999);

// Pull groups (get_sub_field only)
$left  = get_sub_field('left_block') ?: [];
$right = get_sub_field('right_block') ?: [];

// Left
$l_tag       = $left['heading_tag'] ?? 'h2';
$l_text      = $left['heading_text'] ?? 'Got a question?';
$l_desc      = $left['description'] ?? '';
$l_link      = $left['cta_link'] ?? [];
$l_media     = $left['media_type'] ?? 'image';
$l_image     = $left['image'] ?? null;
$l_shortcode = $left['shortcode'] ?? '';

// Right
$r_tag     = $right['heading_tag'] ?? 'h2';
$r_text    = $right['heading_text'] ?? 'About your property';
$r_desc    = $right['description'] ?? '';
$r_link    = $right['cta_link'] ?? [];
$r_media   = $right['media_type'] ?? 'image';
$r_image   = $right['image'] ?? null;
$r_vtype   = $right['video_type'] ?? '';
$r_youtube = $right['youtube_url'] ?? '';
$r_local   = $right['local_video'] ?? null;
$r_poster  = $right['poster_image'] ?? null;

// Map (Jawg)
$r_lat   = isset($right['map_latitude'])  ? (float) $right['map_latitude']  : 0.0;
$r_lng   = isset($right['map_longitude']) ? (float) $right['map_longitude'] : 0.0;
$r_zoom  = isset($right['map_zoom'])      ? (int)   $right['map_zoom']      : 15;
$r_token = $right['jawg_access_token'] ?? '';
$needs_leaflet = ($r_media === 'map_jawg' && $r_lat && $r_lng && $r_token !== '');

// Build padding classes
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

// Enqueue Leaflet if map needed
if ($needs_leaflet) {
    wp_enqueue_style('leaflet');
    wp_enqueue_script('leaflet');
}

// Helpers
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
  return '<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" title="'.esc_attr($title).'" loading="lazy" decoding="async" class="object-cover w-full">';
}
function cs_button_from_link($link, $classes, $fallback='Open link'){
  if (!is_array($link) || empty($link['url'])) return '';
  $label  = ($link['title'] ?? '') ?: $fallback;
  $target = $link['target'] ?? '_self';
  return '<a href="'.esc_url($link['url']).'" target="'.esc_attr($target).'" aria-label="'.esc_attr($label).'" class="'.esc_attr($classes).'">
            <p class="text-[0.875rem] font-[600] leading-[1.375rem] font-[\'Montserrat\']">'.esc_html($label).'</p>
          </a>';
}
function cs_video_local($file, $poster=null){
  if (!is_array($file) || empty($file['url'])) return '';
  $type = $file['mime_type'] ?? 'video/mp4';
  $poster_url = (is_array($poster) && !empty($poster['url'])) ? $poster['url'] : '';
  $poster_attr = $poster_url ? ' poster="'.esc_url($poster_url).'"' : '';
  return '<video class="w-full h-auto" controls preload="metadata"'.$poster_attr.'>
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

<section id="<?php echo esc_attr($section_id); ?>" class="relative flex max-md:overflow-visible overflow-hidden bg-[#f9fafb]">
  <div class="w-full max-w-[80rem] mx-auto">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-[3rem] pt-[5rem] pr-[5rem] pb-[5rem] pl-[5rem] max-xl:px-5 bg-[#f9fafb] bg-center w-[80rem] max-w-full  <?php echo $padding_class_str; ?>">

      <!-- LEFT COLUMN -->
      <div class="flex flex-col gap-[1.5rem] overflow-hidden  max-w-full  self-start">

        <!-- Heading -->
        <div class="flex flex-col gap-[1.5rem]  max-w-full h-[4.3125rem]">
          <?php
            echo cs_build_heading(
              $l_tag,
              $l_text,
              " max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-['Playfair']"
            );
          ?>
          <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem]">
            <div class="grow basis-0 h-[0.3125rem] bg-[#ef7b10]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#0098d8]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#b6c0cb]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#74af27]"></div>
          </div>
        </div>

        <?php if (!empty($l_desc)) : ?>
          <div class="wp_editor  max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d] ">
            <?php echo wp_kses_post($l_desc); ?>
          </div>
        <?php endif; ?>


          <?php echo cs_button_from_link($l_link, ' h-[44px] w-full  inline-flex justify-center items-center hover:bg-[#0098d8] btn inline-flex justify-center items-center gap-2 px-8 py-3.5 bg-[#0f172a] text-white hover:opacity-90 transition-opacity duration-200  max-w-full h-[2.75rem] self-center', 'Book now via Calendly'); ?>
        <div class="overflow-hidden self-center max-w-full">
          <?php
            if ($l_media === 'calendly') {
              echo '<div class="w-full h-full wp_editor">';
              echo do_shortcode($l_shortcode);
              echo '</div>';
            } else {
              echo cs_image_tag($l_image, 'Cover', 'Cover');
            }
          ?>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="flex flex-col gap-[1.5rem] overflow-hidden  max-w-full  self-start">

        <div class="flex flex-col gap-[1.5rem]  max-w-full h-[4.3125rem]">
          <?php
            echo cs_build_heading(
              $r_tag,
              $r_text,
              " max-w-full break-words text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-['Playfair']"
            );
          ?>
          <div class="flex flex-row justify-between items-start w-[4.4375rem] h-[0.3125rem]">
            <div class="grow basis-0 h-[0.3125rem] bg-[#ef7b10]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#0098d8]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#b6c0cb]"></div>
            <div class="grow basis-0 h-[0.3125rem] bg-[#74af27]"></div>
          </div>
        </div>

        <?php if (!empty($r_desc)) : ?>
          <div class="wp_editor  max-w-full break-words text-left text-[1.125rem] font-[500] leading-[1.75rem] tracking-[0.0625rem] text-[#4d4d4d] ">
            <?php echo wp_kses_post($r_desc); ?>
          </div>
        <?php endif; ?>

        <?php echo cs_button_from_link($r_link, 'btn inline-flex justify-center items-center gap-2 px-8 py-3.5 border-[0.125rem] border-[#0a1119] transition-opacity duration-200  max-w-full  self-center hover:bg-[#0098d8] w-full  inline-flex justify-center items-center h-[44px] hover:border-[#0098d8]', 'Request a call'); ?>
        

        <div class="overflow-hidden self-center max-w-full">
          <?php
            if ($r_media === 'video') {
              if ($r_vtype === 'local' && $r_local) {
                echo cs_video_local($r_local, $r_poster);
              } elseif ($r_vtype === 'youtube' && $r_youtube) {
                echo cs_video_youtube($r_youtube, $r_poster);
              } else {
                echo '<div class="w-full h-full bg-[#e5e7eb]"></div>';
              }
            } elseif ($r_media === 'map_jawg') {
              if ($needs_leaflet) {
                $map_id = $section_id . '__map';
                echo '<div id="'.esc_attr($map_id).'" class="w-full h-full"></div>';

                // Inline Leaflet init with Jawg Streets
                ?>
                <script>
                  document.addEventListener('DOMContentLoaded', function() {
                    if (typeof L === 'undefined') return;

                    var map = L.map('<?php echo esc_js($map_id); ?>', {
                      scrollWheelZoom: false,
                      zoomControl: true
                    }).setView(
                      [<?php echo esc_js($r_lat); ?>, <?php echo esc_js($r_lng); ?>],
                      <?php echo esc_js($r_zoom ?: 15); ?>
                    );

                    L.tileLayer('https://{s}.tile.jawg.io/jawg-streets/{z}/{x}/{y}{r}.png?access-token=<?php echo esc_js($r_token); ?>', {
                      attribution: 'Map data © OpenStreetMap contributors, © <a href="https://www.jawg.io/" target="_blank" rel="noopener">JawgMaps</a>',
                      subdomains: 'abcd',
                      maxZoom: 22
                    }).addTo(map);

                    // Small color nudge if you want slightly greener (optional – comment out if not wanted)
                    // document.querySelectorAll('#<?php echo esc_js($map_id); ?> .leaflet-tile-pane .leaflet-tile').forEach(function(el){
                    //   el.style.filter = 'hue-rotate(110deg) saturate(1.08) brightness(1.02)';
                    // });

                    setTimeout(function(){ map.invalidateSize(); }, 200);
                  });
                </script>
                <?php
              } else {
                echo '<div class="w-full h-full bg-[#e5e7eb]"></div>';
              }
            } else {
              echo cs_image_tag($r_image, 'Cover', 'Cover');
            }
          ?>
        </div>

      </div>

    </div>
  </div>
</section>
