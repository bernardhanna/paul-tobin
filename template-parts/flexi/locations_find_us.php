<?php
/**
 * Frontend: Locations Find Us (Leaflet like Booking section)
 * - Per-location display: Leaflet | iframe | image
 * - Map fills container width, fixed height h-[500px]
 * - Uses get_sub_field() only
 */

$heading          = get_sub_field('heading') ?: 'Where you can find us';
$heading_tag      = get_sub_field('heading_tag') ?: 'h2';
$background_color = get_sub_field('background_color') ?: '#ffffff';

// Padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size    = get_sub_field('screen_size');
    $padding_top    = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');
    if ($screen_size !== '' && $padding_top !== null)   $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
    if ($screen_size !== '' && $padding_bottom !== null) $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
  }
}

// Unique section ID
$section_id = 'locations-find-us-' . wp_generate_uuid4();
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
  style="background-color: <?php echo esc_attr($background_color); ?>;"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
  <div class="flex flex-col items-center py-10 mx-auto w-full lg:py-20 max-w-container max-lg:px-5">

    <div class="flex flex-col gap-6 w-full">
      <?php if ($heading): ?>
        <<?php echo esc_attr($heading_tag); ?>
          id="<?php echo esc_attr($section_id); ?>-heading"
          class="max-w-full text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary"
        ><?php echo esc_html($heading); ?></<?php echo esc_attr($heading_tag); ?>>
      <?php endif; ?>

      <div class="flex justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
        <div class="bg-orange-500 flex-1 h-[5px]"></div>
        <div class="bg-sky-500 flex-1 h-[5px]"></div>
        <div class="bg-slate-300 flex-1 h-[5px]"></div>
        <div class="bg-lime-600 flex-1 h-[5px]"></div>
      </div>
    </div>

    <!-- Locations Grid -->
    <?php if (have_rows('locations')): ?>
      <div class="grid grid-cols-1 gap-10 mt-12 w-full md:grid-cols-2 max-md:mt-10">
        <?php while (have_rows('locations')): the_row();
          $office_name   = get_sub_field('office_name');
          $address       = get_sub_field('address');
          $phone_numbers = get_sub_field('phone_numbers');
          $email         = get_sub_field('email');

          $map_type      = get_sub_field('map_display_type') ?: 'leaflet'; // leaflet | iframe | image

          // Static image
          $map_image_id  = get_sub_field('map_image');
          $map_image_alt = $map_image_id ? (get_post_meta($map_image_id, '_wp_attachment_image_alt', true) ?: 'Office location map') : 'Office location map';

          // Leaflet fields (match booking section behavior)
          $lat            = (float) (get_sub_field('map_latitude')  ?: 0);
          $lng            = (float) (get_sub_field('map_longitude') ?: 0);
          $zoom           = (int)   (get_sub_field('map_zoom')      ?: 15);
          $provider       = 'jawg-light'; // you can change to 'jawg-dark' or 'osm' if needed
          $tile_api_key   = get_sub_field('tile_api_key') ?: 'zxWPtYn9xCoXLAzkN6ckqMOHRw7Xf0zsTWBN0EmR7BSjUMW2F0hsBScanw15iLpX';
          $marker_icon_id = get_sub_field('map_icon');
          $marker_icon    = $marker_icon_id ? wp_get_attachment_image_url($marker_icon_id, 'full') : '';

          // iframe
          $map_iframe_html = get_sub_field('map_iframe_html');

          $location_id   = 'location-' . wp_generate_uuid4();
        ?>
          <article class="flex flex-col" aria-labelledby="<?php echo esc_attr($location_id); ?>-title">

            <!-- Contact Information Card -->
            <div class="p-8 w-full bg-[#B6C0CB] min-h-[332px] h-full">
              <header class="pb-4">
                <div class="flex justify-between items-center py-4 w-full">
                  <span id="<?php echo esc_attr($location_id); ?>-title" class="font-secondary text-[1.2rem] font-[600] leading-6 tracking-[0.005rem]">
                    <?php echo esc_html($office_name); ?>
                  </span>
                  <i class="w-4 h-4 fas fa-chevron-down text-slate-950" aria-hidden="true"></i>
                </div>
              </header>

              <div class="space-y-4" role="region" aria-labelledby="<?php echo esc_attr($location_id); ?>-title">
                <?php if ($address): ?>
                  <div class="flex gap-2 items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 18 22" fill="none" aria-hidden="true">
                      <path d="M16.625 8.625C16.625 14.625 8.625 20.625 8.625 20.625C8.625 20.625 0.625 14.625 0.625 8.625C0.625 6.50327 1.46785 4.46844 2.96815 2.96815C4.46844 1.46785 6.50327 0.625 8.625 0.625C10.7467 0.625 12.7816 1.46785 14.2819 2.96815C15.7821 4.46844 16.625 6.50327 16.625 8.625Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                      <path d="M8.625 11.625C10.2819 11.625 11.625 10.2819 11.625 8.625C11.625 6.96815 10.2819 5.625 8.625 5.625C6.96815 5.625 5.625 6.96815 5.625 8.625C5.625 10.2819 6.96815 11.625 8.625 11.625Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                    </svg>
                    <address class="flex-1 not-italic font-primary text-sm font-normal leading-[1.375rem] tracking-normal text-[#0A1119]">
                      <?php echo wp_kses_post(nl2br($address)); ?>
                    </address>
                  </div>
                <?php endif; ?>

                <?php if ($phone_numbers): ?>
                  <div class="flex gap-2 items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                      <path d="M20.5131 15.5451V18.5451C20.5142 18.8236 20.4572 19.0993 20.3456 19.3544C20.2341 19.6096 20.0704 19.8387 19.8652 20.027C19.66 20.2152 19.4177 20.3586 19.1539 20.4478C18.89 20.537 18.6105 20.5702 18.3331 20.5451C15.256 20.2107 12.3001 19.1592 9.70312 17.4751C7.28694 15.9398 5.23845 13.8913 3.70312 11.4751C2.01309 8.8663 0.96136 5.89609 0.633117 2.8051C0.608127 2.52856 0.640992 2.24986 0.729617 1.98672C0.818243 1.72359 0.960688 1.48179 1.14788 1.27672C1.33508 1.07165 1.56292 0.907806 1.81691 0.795619C2.07089 0.683432 2.34546 0.625358 2.62312 0.625097H5.62312C6.10842 0.620321 6.57891 0.792176 6.94688 1.10863C7.31485 1.42508 7.55519 1.86454 7.62312 2.3451C7.74974 3.30516 7.98457 4.24782 8.32312 5.1551C8.45766 5.51302 8.48678 5.90201 8.40702 6.27598C8.32727 6.64994 8.14198 6.99321 7.87312 7.2651L6.60312 8.5351C8.02667 11.0386 10.0996 13.1115 12.6031 14.5351L13.8731 13.2651C14.145 12.9962 14.4883 12.8109 14.8622 12.7312C15.2362 12.6514 15.6252 12.6806 15.9831 12.8151C16.8904 13.1536 17.8331 13.3885 18.7931 13.5151C19.2789 13.5836 19.7225 13.8283 20.0396 14.2026C20.3568 14.5769 20.5253 15.0547 20.5131 15.5451Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                      <path d="M12.5635 0.625C14.6017 0.839769 16.5056 1.7438 17.9603 3.18758C19.415 4.63136 20.3334 6.52841 20.5635 8.565" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                      <path d="M12.5635 4.625C13.547 4.81894 14.4495 5.30403 15.1539 6.01731C15.8582 6.73059 16.3319 7.63913 16.5135 8.625" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                    </svg>
                    <div class="flex-1 text-sm tracking-normal leading-6 text-slate-950">
                      <?php
                        $lines = explode("\n", (string) $phone_numbers);
                        foreach ($lines as $line):
                          $clean = preg_replace('/[^+\d]/', '', trim($line));
                          if (!empty(trim($line))):
                      ?>
                        <div><a href="tel:<?php echo esc_attr($clean); ?>" class="hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500"><?php echo esc_html(trim($line)); ?></a></div>
                      <?php endif; endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($email): ?>
                  <div class="flex gap-2 items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 18" fill="none" aria-hidden="true">
                      <path d="M18.625 0.625H2.625C1.52043 0.625 0.625 1.52043 0.625 2.625V14.625C0.625 15.7296 1.52043 16.625 2.625 16.625H18.625C19.7296 16.625 20.625 15.7296 20.625 14.625V2.625C20.625 1.52043 19.7296 0.625 18.625 0.625Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                      <path d="M20.625 3.625L11.655 9.325C11.3463 9.51843 10.9893 9.62101 10.625 9.62101C10.2607 9.62101 9.90373 9.51843 9.595 9.325L0.625 3.625" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                    </svg>
                    <a href="mailto:<?php echo esc_attr($email); ?>" class="text-sm tracking-normal leading-6 text-slate-950 hover:underline"><?php echo esc_html($email); ?></a>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Map/Embed/Image -->
            <div class="w-full">
              <?php if ($map_type === 'leaflet' && $lat && $lng): ?>
                <div
                  id="<?php echo esc_attr($location_id); ?>__map"
                  class="w-full h-[500px] overflow-hidden"
                  data-leaflet
                  data-provider="<?php echo esc_attr($provider); ?>"
                  data-token="<?php echo esc_attr($tile_api_key); ?>"
                  data-lat="<?php echo esc_attr($lat); ?>"
                  data-lng="<?php echo esc_attr($lng); ?>"
                  data-zoom="<?php echo esc_attr($zoom); ?>"
                  <?php if ($marker_icon): ?>
                  data-marker-icon="<?php echo esc_url($marker_icon); ?>"
                  <?php endif; ?>
                ></div>

              <?php elseif ($map_type === 'iframe' && !empty($map_iframe_html)): ?>
                <div class="w-full h-[500px] overflow-hidden">
                  <?php
                  $iframe = preg_replace(['#\s(width)="[^"]*"#i', '#\s(height)="[^"]*"#i'], ['', ''], (string) $map_iframe_html);
                  if (stripos($iframe, '<iframe') !== false) {
                    $iframe = preg_replace('#<iframe#i', '<iframe style="width:100%;height:100%;border:0;"', $iframe, 1);
                  }
                  echo wp_kses($iframe, [
                    'iframe' => [
                      'src'=>[], 'title'=>[], 'style'=>[], 'loading'=>[], 'referrerpolicy'=>[],
                      'allow'=>[], 'allowfullscreen'=>[]
                    ],
                  ]);
                  ?>
                </div>

              <?php elseif ($map_image_id): ?>
                <div class="w-full h-[500px] overflow-hidden">
                  <?php
                  echo wp_get_attachment_image($map_image_id, 'full', false, [
                    'alt'    => esc_attr($map_image_alt),
                    'class'  => 'w-full h-full object-cover',
                    'loading'=> 'lazy'
                  ]);
                  ?>
                </div>
              <?php endif; ?>
            </div>

          </article>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<style>
  /* Accordion animation (kept for consistency if you add expanding bits later) */
  .accordion-content { overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease-out; }
  .accordion-content.collapsed { max-height: 0; opacity: 0; }
  .accordion-content.expanded  { max-height: 1000px; opacity: 1; }
</style>

<!-- Leaflet assets (once) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
(function(){
  // Initialize all Leaflet map divs rendered in this section
  function initLeafletMap(container) {
    if (!container || typeof L === 'undefined' || container.dataset.initialized === '1') return;

    const provider   = container.getAttribute('data-provider') || 'jawg-light';
    const token      = container.getAttribute('data-token') || '';
    const lat        = parseFloat(container.getAttribute('data-lat')  || '53.349805');
    const lng        = parseFloat(container.getAttribute('data-lng')  || '-6.26031');
    const zoom       = parseInt(container.getAttribute('data-zoom')   || '14', 10);
    const markerIcon = container.getAttribute('data-marker-icon') || '';

    const map = L.map(container).setView([lat, lng], zoom);

    // Tile layer (same as booking section)
    let tileUrl, tileOpts = {};
    if (provider === 'osm') {
      tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
      tileOpts = { maxZoom: 19, attribution: '&copy; OpenStreetMap' };
    } else if (provider === 'jawg-dark') {
      tileUrl = 'https://tile.jawg.io/jawg-dark/{z}/{x}/{y}{r}.png?access-token=' + encodeURIComponent(token);
      tileOpts = { maxZoom: 22, attribution: '&copy; <a href="https://www.jawg.io" target="_blank" rel="noopener">Jawg</a>' };
    } else {
      tileUrl = 'https://tile.jawg.io/jawg-light/{z}/{x}/{y}{r}.png?access-token=' + encodeURIComponent(token);
      tileOpts = { maxZoom: 22, attribution: '&copy; <a href="https://www.jawg.io" target="_blank" rel="noopener">Jawg</a>' };
    }
    L.tileLayer(tileUrl, tileOpts).addTo(map);

    // Custom marker (if provided)
    let icon = null;
    if (markerIcon) {
      icon = L.icon({
        iconUrl: markerIcon,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
      });
    }
    L.marker([lat, lng], icon ? { icon } : undefined).addTo(map);

    container.dataset.initialized = '1';
    setTimeout(() => { map.invalidateSize(); }, 100);
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('#<?php echo esc_js($section_id); ?> [data-leaflet]').forEach(initLeafletMap);
  });
})();
</script>
