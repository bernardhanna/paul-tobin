<?php
// ========================== FETCH FIELDS ==========================
$heading               = get_sub_field('heading') ?: 'Book an evaluation';
$heading_tag           = get_sub_field('heading_tag') ?: 'h2';
$description           = get_sub_field('description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';
$form_markup           = get_sub_field('form_markup', false, false);
$privacy_policy_url    = get_sub_field('privacy_policy_url') ?: '#';

// Find Us
$find_us_heading       = get_sub_field('find_us_heading') ?: 'Where you can find us';
$find_us_heading_tag   = get_sub_field('find_us_heading_tag') ?: 'h2';
$find_us_description   = get_sub_field('find_us_description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';

// Design/Layout
$background_color      = get_sub_field('background_color') ?: '#ffffff';

// Padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        if ($screen_size !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// Whitelist heading tags
$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true))         { $heading_tag = 'h2'; }
if (!in_array($find_us_heading_tag, $allowed_tags, true)) { $find_us_heading_tag = 'h2'; }

// Unique section id
$section_id = 'booking-form-' . wp_generate_uuid4();

// =================== FORM PLUMBING INJECTION ======================
if ($form_markup) {
    $form_markup = str_replace(
        '<form',
        sprintf(
            '<form action="%1$s" method="post" enctype="multipart/form-data" data-theme-form="%2$s"',
            esc_url(admin_url('admin-post.php')),
            esc_attr(get_row_index())
        ),
        $form_markup
    );

    $hidden = sprintf(
        '<input type="hidden" name="action" value="theme_form_submit">
         <input type="hidden" name="theme_form_nonce" value="%1$s">
         <input type="hidden" name="_theme_form_id" value="%2$s">
         <input type="hidden" name="_submission_uid" value="%3$s">',
        esc_attr(wp_create_nonce('theme_form_submit')),
        esc_attr(get_row_index()),
        esc_attr(wp_generate_uuid4())
    );

    if ($name = get_sub_field('form_name')) {
        $hidden .= '<input type="hidden" name="_theme_form_name" value="' . esc_attr($name) . '">';
    }
    if (get_sub_field('save_entries_to_db')) {
        $hidden .= '<input type="hidden" name="_theme_save_to_db" value="1">';
    }

    // Mail config (posted)
    $cfg_to        = get_sub_field('email_to') ?: get_option('admin_email');
    $cfg_bcc       = get_sub_field('email_bcc') ?: '';
    $cfg_subject   = get_sub_field('email_subject') ?: '';
    $cfg_from_name = get_sub_field('from_name') ?: '';
    $cfg_from_email= get_sub_field('from_email') ?: '';

    $hidden_cfg  = '<input type="hidden" name="_cfg_to" value="'.esc_attr($cfg_to).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_bcc" value="'.esc_attr($cfg_bcc).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_subject" value="'.esc_attr($cfg_subject).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_name" value="'.esc_attr($cfg_from_name).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_email" value="'.esc_attr($cfg_from_email).'">';

    if (get_sub_field('enable_autoresponder')) {
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_enabled" value="1">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_subject" value="'.esc_attr(get_sub_field('autoresponder_subject') ?: '').'">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_message" value="'.esc_attr(get_sub_field('autoresponder_message') ?: '').'">';
    }

    $form_markup = str_replace('</form>', ($hidden . $hidden_cfg) . '</form>', $form_markup);
    $form_markup = str_replace('href="#"', 'href="' . esc_url($privacy_policy_url) . '"', $form_markup);
}
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
  style="background-color: <?php echo esc_attr($background_color); ?>;"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
  <div class="flex flex-col items-center pt-[10rem] pb-20 mx-auto w-full max-w-container max-lg:px-5  max-sm:pb-10">

    <div class="grid grid-cols-1 gap-10 w-full lg:gap-20 lg:grid-cols-2 max-md:gap-16 max-sm:gap-16">

      <!-- LEFT: Booking Form -->
      <div class="flex flex-col">
        <?php if ($heading): ?>
          <<?php echo esc_attr($heading_tag); ?>
            id="<?php echo esc_attr($section_id); ?>-heading"
            class="mb-4 max-w-full text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary"
          ><?php echo esc_html($heading); ?></<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>

        <div class="mb-6 flex justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
          <div class="bg-orange-500 flex-1 h-[5px]"></div>
          <div class="bg-sky-500 flex-1 h-[5px]"></div>
          <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
          <div class="bg-lime-600 flex-1 h-[5px]"></div>
        </div>

        <?php if ($description): ?>
          <p class="mb-8 text-black max-lg:hidden font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
            <?php echo esc_html($description); ?>
          </p>
        <?php endif; ?>

        <div class="p-[2rem] bg-[#EDEDED] max-sm:p-6">
          <?php if ($form_markup): ?>
            <?php
            echo wp_kses(
              $form_markup,
              [
                'form'=>['class'=>[], 'role'=>[], 'aria-labelledby'=>[], 'novalidate'=>[], 'action'=>[], 'method'=>[], 'enctype'=>[], 'data-theme-form'=>[]],
                'div'=>['class'=>[],'id'=>[],'role'=>[],'aria-live'=>[],'aria-describedby'=>[]],
                'label'=>['for'=>[], 'class'=>[], 'id'=>[]],
                'input'=>['type'=>[], 'id'=>[], 'name'=>[], 'placeholder'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'autocomplete'=>[], 'class'=>[], 'value'=>[], 'accept'=>[]],
                'select'=>['id'=>[], 'name'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'class'=>[]],
                'option'=>['value'=>[], 'selected'=>[], 'disabled'=>[]],
                'textarea'=>['id'=>[], 'name'=>[], 'placeholder'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'rows'=>[], 'class'=>[]],
                'button'=>['type'=>[], 'class'=>[], 'aria-describedby'=>[]],
                'span'=>['class'=>[], 'id'=>[]],
                'i'=>['class'=>[]],
                'img'=>['src'=>[], 'alt'=>[], 'class'=>[]],
                'a'=>['href'=>[], 'class'=>[], 'target'=>[], 'aria-label'=>[]],
              ]
            );
            ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: Find Us (Accordion — each panel includes its own map) -->
      <div class="flex flex-col">
        <?php if ($find_us_heading): ?>
          <<?php echo esc_attr($find_us_heading_tag); ?>
            class="mb-4 max-w-full text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary"
          ><?php echo esc_html($find_us_heading); ?></<?php echo esc_attr($find_us_heading_tag); ?>>
        <?php endif; ?>

        <div class="mb-6 flex justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
          <div class="bg-orange-500 flex-1 h-[5px]"></div>
          <div class="bg-sky-500 flex-1 h-[5px]"></div>
          <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
          <div class="bg-lime-600 flex-1 h-[5px]"></div>
        </div>

        <?php if ($find_us_description): ?>
          <p class="mb-8 text-black max-lg:hidden font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
            <?php echo esc_html($find_us_description); ?>
          </p>
        <?php endif; ?>

        <div class="w-full">
          <div class="overflow-hidden p-8 bg-[#B6C0CB] max-md:p-6 max-sm:p-4">
            <?php if (have_rows('office_locations')): ?>
              <div class="space-y-0" role="region" aria-label="Office Locations">
                <?php
                $idx = 0;
                while (have_rows('office_locations')): the_row();
                  $office_name      = get_sub_field('office_name');
                  $address          = get_sub_field('address');
                  $phone_numbers    = get_sub_field('phone_numbers');
                  $email            = get_sub_field('email');
                  $team_link        = get_sub_field('team_link');

                  // Per-location map controls (gracefully handle missing fields)
                  $loc_map_type     = get_sub_field('map_display_type') ?: 'leaflet'; // leaflet | iframe | image
                  $loc_iframe       = get_sub_field('map_iframe_html');
                  $lat              = get_sub_field('latitude');
                  $lng              = get_sub_field('longitude');
                  $zoom             = (int) (get_sub_field('map_zoom') ?: 15);
                  $jawg_token_field = get_sub_field('tile_api_key'); // may be missing
                  $jawg_token       = $jawg_token_field ?: 'zxWPtYn9xCoXLAzkN6ckqMOHRw7Xf0zsTWBN0EmR7BSjUMW2F0hsBScanw15iLpX';
                  $provider         = 'jawg-light'; // default provider for per-location map (can extend to field if needed)
                  $marker_icon_id   = get_sub_field('map_icon');
                  $marker_icon_url  = $marker_icon_id ? wp_get_attachment_image_url($marker_icon_id, 'full') : '';

                  $map_image_id     = get_sub_field('map_image'); // for image mode fallback

                  $is_expanded      = (bool) get_sub_field('is_expanded');

                  $accordion_id     = $section_id . '-accordion-' . $idx;
                  $content_id       = $section_id . '-content-' . $idx;

                  // Map DOM id (unique per location)
                  $map_div_id       = $section_id . '-map-' . $idx;
                ?>
                <article class="border-b border-slate-200 <?php echo $idx === 0 ? 'pb-4' : ''; ?>">
                  <header>
                    <button
                      class="flex justify-between items-center py-4 w-full text-left btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-600"
                      aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
                      aria-controls="<?php echo esc_attr($content_id); ?>"
                      id="<?php echo esc_attr($accordion_id); ?>"
                      data-accordion-trigger
                      data-map-type="<?php echo esc_attr($loc_map_type); ?>"
                      data-map-id="<?php echo esc_attr($map_div_id); ?>"
                      data-provider="<?php echo esc_attr($provider); ?>"
                      data-token="<?php echo esc_attr($jawg_token); ?>"
                      <?php if ($lat && $lng): ?>
                        data-lat="<?php echo esc_attr($lat); ?>"
                        data-lng="<?php echo esc_attr($lng); ?>"
                        data-zoom="<?php echo esc_attr($zoom); ?>"
                      <?php endif; ?>
                      <?php if ($marker_icon_url): ?>
                        data-marker-icon="<?php echo esc_url($marker_icon_url); ?>"
                      <?php endif; ?>
                    >
                      <h2 class="text-base font-semibold leading-6 text-slate-950 max-sm:text-sm max-sm:leading-5">
                        <?php echo esc_html($office_name); ?>
                      </h2>
                      <svg
                        width="16" height="16" viewBox="0 0 16 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="transition-transform duration-200 <?php echo $is_expanded ? 'rotate-180' : ''; ?>"
                        aria-hidden="true"
                      >
                        <path d="M4 6L8 10L12 6" stroke="#020617" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </button>
                  </header>

                  <div
                    class="accordion-content <?php echo $is_expanded ? 'expanded' : 'collapsed'; ?>"
                    id="<?php echo esc_attr($content_id); ?>"
                    aria-labelledby="<?php echo esc_attr($accordion_id); ?>"
                    role="region"
                  >
                    <div class="space-y-4">
                      <!-- Details -->
                      <?php if (!empty($address)): ?>
                        <div class="flex gap-2 items-start">
                          <div class="flex items-center py-1" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                              <path d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                              <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                            </svg>
                          </div>
                          <address class="py-1 not-italic">
                            <div class="text-sm leading-6 text-slate-950 max-sm:text-xs max-sm:leading-5 wp_editor">
                              <?php echo wp_kses_post(nl2br($address)); ?>
                            </div>
                          </address>
                        </div>
                      <?php endif; ?>

                      <?php if (!empty($phone_numbers)): ?>
                        <div class="flex gap-2 items-start">
                          <div class="flex items-center py-1" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                              <path d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4741 21.8324 20.7293C21.7209 20.9845 21.5572 21.2136 21.352 21.4018C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.945 19.8199 21.92C16.7428 21.5856 13.7869 20.5341 11.1899 18.85C8.77376 17.3146 6.72527 15.2661 5.18993 12.85C3.49991 10.2412 2.44818 7.27097 2.11993 4.17997C2.09494 3.90344 2.12781 3.62474 2.21643 3.3616C2.30506 3.09846 2.4475 2.85666 2.6347 2.6516C2.82189 2.44653 3.04974 2.28268 3.30372 2.1705C3.55771 2.05831 3.83227 2.00024 4.10993 1.99997H7.10993C7.59524 1.9952 8.06572 2.16705 8.43369 2.48351C8.80166 2.79996 9.04201 3.23942 9.10993 3.71997C9.23656 4.68004 9.47138 5.6227 9.80993 6.52997C9.94448 6.8879 9.9736 7.27689 9.89384 7.65086C9.81408 8.02482 9.6288 8.36809 9.35993 8.63998L8.08993 9.90997C9.51349 12.4135 11.5864 14.4864 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1858 16.3491 14.1061C16.723 14.0263 17.112 14.0554 17.4699 14.19C18.3772 14.5285 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                              <path d="M14.0498 2C16.0881 2.21477 17.992 3.1188 19.4467 4.56258C20.9014 6.00636 21.8197 7.90341 22.0498 9.94" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                              <path d="M14.0498 6C15.0333 6.19394 15.9358 6.67903 16.6402 7.39231C17.3446 8.10559 17.8183 9.01413 17.9998 10" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                            </svg>
                          </div>
                          <div class="py-1">
                            <div class="text-sm leading-6 text-slate-950 max-sm:text-xs max-sm:leading-5 wp_editor">
                              <?php echo wp_kses_post($phone_numbers); ?>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>

                      <?php if (!empty($email)): ?>
                        <div class="flex gap-2 items-center">
                          <div aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                              <path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                              <path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                            </svg>
                          </div>
                          <div class="py-1">
                            <a href="mailto:<?php echo esc_attr($email); ?>" class="text-sm leading-6 text-slate-950 hover:underline max-sm:text-xs max-sm:leading-5">
                              <?php echo esc_html($email); ?>
                            </a>
                          </div>
                        </div>
                      <?php endif; ?>

                      <?php if ($team_link && is_array($team_link) && isset($team_link['url'], $team_link['title'])): ?>
                        <div class="pt-2">
                          <a href="<?php echo esc_url($team_link['url']); ?>"
                             class="text-base leading-7 text-black underline cursor-pointer hover:no-underline max-sm:text-sm max-sm:leading-6"
                             target="<?php echo esc_attr($team_link['target'] ?? '_self'); ?>">
                            <?php echo esc_html($team_link['title']); ?>
                          </a>
                        </div>
                      <?php endif; ?>

                      <!-- Per-location MAP -->
                      <div class="w-full">
                        <?php if ($loc_map_type === 'iframe' && !empty($loc_iframe)): ?>
                          <div class="w-full h-[300px]">
                            <?php echo wp_kses($loc_iframe, [
                              'iframe' => [
                                'src'=>[], 'width'=>[], 'height'=>[], 'style'=>[], 'frameborder'=>[],
                                'allowfullscreen'=>[], 'loading'=>[], 'referrerpolicy'=>[], 'title'=>[], 'aria-label'=>[]
                              ]
                            ]); ?>
                          </div>

                        <?php elseif ($loc_map_type === 'image' && $map_image_id): ?>
                          <div class="overflow-hidden w-full bg-gray-400 h-[300px]">
                            <?php
                            $map_image_alt = get_post_meta($map_image_id, '_wp_attachment_image_alt', true) ?: 'Office location map';
                            echo wp_get_attachment_image($map_image_id, 'full', false, [
                                'alt' => esc_attr($map_image_alt),
                                'class' => 'w-full h-full object-cover',
                                'loading' => 'lazy'
                            ]);
                            ?>
                          </div>

                        <?php else: // default to Leaflet ?>
                          <div
                            id="<?php echo esc_attr($map_div_id); ?>"
                            class="w-full h-[300px] bg-slate-200"
                            data-leaflet
                            data-provider="<?php echo esc_attr($provider); ?>"
                            data-token="<?php echo esc_attr($jawg_token); ?>"
                            <?php if ($lat && $lng): ?>
                              data-lat="<?php echo esc_attr($lat); ?>"
                              data-lng="<?php echo esc_attr($lng); ?>"
                              data-zoom="<?php echo esc_attr($zoom); ?>"
                            <?php endif; ?>
                            <?php if ($marker_icon_url): ?>
                              data-marker-icon="<?php echo esc_url($marker_icon_url); ?>"
                            <?php endif; ?>
                          ></div>
                        <?php endif; ?>
                      </div>
                      <!-- /Per-location MAP -->
                    </div>
                  </div>
                </article>
                <?php $idx++; endwhile; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
      <!-- /RIGHT -->
    </div>
  </div>
</section>

<style>
  /* Accordion animation */
  .accordion-content { overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease-out; }
  .accordion-content.collapsed { max-height: 0; opacity: 0; }
  .accordion-content.expanded  { max-height: 1000px; opacity: 1; }
</style>

<!-- Leaflet assets (once) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
/* Form validation (unchanged) */
(function() {
  const form = document.querySelector('form[role="form"]');
  if (!form) return;

  const byId = (id) => document.getElementById(id);
  const getErrorEl = (field) => field && byId(field.id + '-error');

  const showError = (field, msg) => {
    const err = getErrorEl(field);
    if (!err) return;
    err.textContent = msg || 'This field is required.';
    err.classList.remove('hidden');
    field.setAttribute('aria-invalid', 'true');
    field.classList.add('border-red-600');
  };

  const clearError = (field) => {
    const err = getErrorEl(field);
    if (!err) return;
    err.textContent = '';
    err.classList.add('hidden');
    field.removeAttribute('aria-invalid');
    field.classList.remove('border-red-600');
  };

  const isEmpty = (v) => v == null || String(v).trim() === '';
  const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  const isPhone = (v) => /^\+?[0-9() \-]{6,}$/.test(v);

  const validateField = (field) => {
    if (!field) return true;
    const tag = field.tagName.toLowerCase();
    const type = (field.getAttribute('type') || '').toLowerCase();
    const required = field.hasAttribute('required') || field.getAttribute('aria-required') === 'true';
    const value = field.value;

    clearError(field);

    if (type === 'checkbox') { if (required && !field.checked) { showError(field, 'Please agree before submitting.'); return false; } return true; }
    if (tag === 'select')     { if (required && isEmpty(value)) { showError(field, 'Please make a selection.'); return false; } return true; }
    if (required && isEmpty(value)) { showError(field); return false; }
    if (type === 'email' && !isEmpty(value) && !isEmail(value)) { showError(field, 'Please enter a valid email address.'); return false; }
    if (type === 'tel' && !isEmpty(value) && !isPhone(value))   { showError(field, 'Please enter a valid phone number (e.g., +353 ...).'); return false; }
    return true;
  };

  const fields = [
    byId('first-name'), byId('last-name'), byId('email-address'), byId('phone-number'),
    byId('query-type'), byId('property-address'), byId('property-type'), byId('property-condition'),
    byId('bedrooms'), byId('bathrooms'), byId('message'), byId('privacy')
  ].filter(Boolean);

  fields.forEach(clearError);

  fields.forEach((f) => {
    const evt = (f.tagName.toLowerCase() === 'select' || f.type === 'checkbox') ? 'change' : 'blur';
    f.addEventListener(evt, () => validateField(f));
    if (['text','email','tel','textarea'].includes((f.type || f.tagName).toLowerCase())) {
      f.addEventListener('input', () => { if (f.getAttribute('aria-invalid') === 'true') validateField(f); });
    }
  });

  form.addEventListener('submit', (e) => {
    let firstInvalid = null;
    let allValid = true;
    fields.forEach((f) => { const valid = validateField(f); if (!valid) { allValid = false; if (!firstInvalid) firstInvalid = f; }});
    if (!allValid) {
      e.preventDefault();
      firstInvalid && firstInvalid.focus();
      const messages = document.getElementById('form-messages');
      if (messages) { messages.classList.remove('hidden'); messages.textContent = 'Please correct the highlighted fields.'; }
    }
  });
})();

/* Accordion + per-location map initialization */
(function() {
  const TEST_JAWG_TOKEN = 'zxWPtYn9xCoXLAzkN6ckqMOHRw7Xf0zsTWBN0EmR7BSjUMW2F0hsBScanw15iLpX';

  function initLeafletMap(container) {
    if (!container || typeof L === 'undefined') return null;
    if (container.dataset.initialized === '1') return null;

    const provider   = container.getAttribute('data-provider') || 'jawg-light';
    const token      = container.getAttribute('data-token') || TEST_JAWG_TOKEN;
    const lat        = parseFloat(container.getAttribute('data-lat') || '53.349805');
    const lng        = parseFloat(container.getAttribute('data-lng') || '-6.26031');
    const zoom       = parseInt(container.getAttribute('data-zoom') || '14', 10);
    const markerIcon = container.getAttribute('data-marker-icon');

    const m = L.map(container).setView([lat, lng], zoom);

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
    L.tileLayer(tileUrl, tileOpts).addTo(m);

    let icon = null;
    if (markerIcon) {
      icon = L.icon({ iconUrl: markerIcon, iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32] });
    }
    L.marker([lat, lng], icon ? { icon } : undefined).addTo(m);

    container.dataset.initialized = '1';
    // Ensure proper sizing if shown after being hidden
    setTimeout(() => { m.invalidateSize(); }, 100);
    return m;
  }

  function maybeInitMapsIn(el) {
    // Initialize any data-leaflet containers that are visible in this accordion panel
    const mapDivs = el.querySelectorAll('[data-leaflet]');
    mapDivs.forEach(initLeafletMap);
  }

  // On load: if any panels are expanded by default, init their maps now
  document.querySelectorAll('.accordion-content.expanded').forEach(maybeInitMapsIn);

  // Toggle behavior
  const triggers = document.querySelectorAll('[data-accordion-trigger]');
  triggers.forEach(trigger => {
    trigger.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      const contentId  = this.getAttribute('aria-controls');
      const content    = document.getElementById(contentId);
      const icon       = this.querySelector('svg');

      this.setAttribute('aria-expanded', !isExpanded);
      if (content) {
        content.classList.toggle('expanded', !isExpanded);
        content.classList.toggle('collapsed', isExpanded);
        if (!isExpanded) {
          // Now visible → initialize maps (if not already)
          maybeInitMapsIn(content);
        }
      }
      if (icon) icon.classList.toggle('rotate-180', !isExpanded);
    });

    // Keyboard a11y
    trigger.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
    });
  });
})();
</script>