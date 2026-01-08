<?php
/**
 * Footer Template
 *
 * @package Matrix_Starter
 */

// --- Helpers ---------------------------------------------------------------

/**
 * Render an ACF image which may be an ID or array.
 */
function matrix_render_image($image_field, $size = 'thumbnail', $fallback_alt = '', $extra_attrs = []) {
    if (!$image_field) return '';

    if (is_array($image_field)) {
        $id  = $image_field['id'] ?? ($image_field['ID'] ?? null);
        $alt = $image_field['alt'] ?? '';
    } else {
        $id  = (int) $image_field;
        $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
    }

    if (!$id) return '';
    $attrs = array_merge($extra_attrs, ['alt' => esc_attr($alt ?: $fallback_alt)]);
    return wp_get_attachment_image($id, $size, false, $attrs);
}

/**
 * Get URL safely from an ACF link array (or return empty string).
 */
function matrix_link_url($link_field) {
    if (is_array($link_field) && !empty($link_field['url'])) {
        return $link_field['url'];
    }
    return '';
}

/**
 * Get target safely from an ACF link array (defaults to _self).
 */
function matrix_link_target($link_field) {
    if (is_array($link_field) && !empty($link_field['target'])) {
        return $link_field['target'];
    }
    return '_self';
}

// --- Options ---------------------------------------------------------------

// Headings
$col1_heading = get_field('footer_col1_heading', 'option') ?: 'About us';
$col2_heading = get_field('footer_col2_heading', 'option') ?: 'Quick links';
$col3_heading = get_field('footer_col3_heading', 'option') ?: 'Homeowners';

// Branding
$company_logo   = get_field('company_logo', 'option');  // ID
$company_slogan = get_field('company_slogan', 'option') ?: "Your Irish property, expertly handled while you're abroad.";

// Accreditation + partners
$accreditation_image = get_field('accreditation_image', 'option'); // ID
$accreditation_link  = get_field('accreditation_link', 'option');  // array
$partner_logos       = get_field('partner_logos', 'option');       // repeater array

// Trustpilot
$trustpilot_link  = get_field('trustpilot_link', 'option'); // array
$trustpilot_badge = get_field('trustpilot_badge', 'option');// ID

// Contact
$phone_number  = get_field('phone_number', 'option');
$email_address = get_field('email_address', 'option'); // new field
$address       = get_field('address', 'option');

// Socials
$social_icons = get_field('social_icons', 'option'); // repeater

// Menus: use your original slugs exactly
$menu_about    = 'Footer One';
$menu_quick    = 'Footer Two';
$menu_owners   = 'Footer Three';
$menu_legal    = 'copyright';

// Copyright text with {year}
$copyright_tpl = get_field('copyright_text', 'option') ?: '© {year} Paul Tobin Estate Agents. All rights reserved.';
$copyright_txt = str_replace('{year}', date('Y'), $copyright_tpl);
?>

<div class="flex overflow-hidden relative bg-gray-50" role="contentinfo" aria-label="Site footer">
  <div class="flex flex-col gap-12 justify-center items-center px-8 pt-20 pb-12 w-full max-md:px-6 max-md:pt-12 max-md:pb-8 max-sm:gap-8 max-sm:px-4 max-sm:pt-8 max-sm:pb-6">

    <!-- Main Footer Content -->
    <!-- Base: grid-cols-2 so we can do the special stacking at <=768 -->
    <!-- md: keep 2 cols; md: 4 cols with first col full-width; lg: 5 cols -->
    <div class="grid w-full max-w-[1200px] grid-cols-2 gap-8 md:grid-cols-2 md:grid-cols-4 lg:grid-cols-5">

      <!-- 1) Logo + Slogan + Logos -->
      <!-- Base: full width -> col-span-2 -->
      <!-- md: normal -> col-span-1 -->
      <!-- md: full width across 4 cols -> col-span-4 -->
      <!-- lg: normal -> col-span-1 -->
      <div class="flex flex-col col-span-2 gap-6 items-start w-full md:col-span-1 md:col-span-4 lg:col-span-1">

        <!-- Footer logo (option) or custom_logo or fallback -->
        <div class="flex items-center">
          <?php
          if ($company_logo) {
              echo matrix_render_image($company_logo, 'full', get_bloginfo('name'), ['class' => 'h-10 w-auto']);
          } elseif (function_exists('the_custom_logo') && has_custom_logo()) {
              the_custom_logo();
          } else {
              ?>
              <div class="flex items-center">
                <!-- minimal fallback -->
                <span class="text-lg font-semibold"><?php echo esc_html(get_bloginfo('name')); ?></span>
              </div>
              <?php
          }
          ?>
        </div>

        <!-- Company Slogan -->
        <?php if (!empty($company_slogan)) : ?>
          <p class="text-[#0A1119] font-primary text-sm font-normal leading-5 tracking-[0.08px]">
            <?php echo esc_html($company_slogan); ?>
          </p>
        <?php endif; ?>

        <!-- Accreditation + Partner Logos -->
        <div class="flex flex-wrap gap-5 content-start items-start w-full max-sm:gap-3">
          <?php if ($accreditation_image): ?>
            <div class="flex flex-col items-center w-20 max-sm:w-[60px]">
              <?php
              $acc_url    = matrix_link_url($accreditation_link);
              $acc_target = matrix_link_target($accreditation_link);
              if ($acc_url) {
                  echo '<a href="' . esc_url($acc_url) . '" target="' . esc_attr($acc_target) . '" rel="noopener">';
              }
              echo matrix_render_image($accreditation_image, 'thumbnail', 'Accreditation', [
                  'class' => 'w-10 h-10 max-sm:h-[30px] max-sm:w-[30px]'
              ]);
              if ($acc_url) {
                  echo '</a>';
              }
              ?>
            </div>
          <?php endif; ?>

          <?php
          if (!empty($partner_logos) && is_array($partner_logos)) {
              foreach ($partner_logos as $pl) {
                  $logo_id  = $pl['logo_image'] ?? 0;
                  $logo_link = $pl['logo_link'] ?? null;
                  if (!$logo_id) continue;
                  $pl_url    = matrix_link_url($logo_link);
                  $pl_target = matrix_link_target($logo_link);
                  echo '<div class="flex flex-col items-center w-20 max-sm:w-[60px]">';
                  if ($pl_url) echo '<a href="' . esc_url($pl_url) . '" target="' . esc_attr($pl_target) . '" rel="noopener">';
                  echo matrix_render_image($logo_id, 'thumbnail', 'Partner logo', ['class' => 'object-contain']);
                  if ($pl_url) echo '</a>';
                  echo '</div>';
              }
          }
          ?>
        </div>
      </div>

      <!-- 2) About us -->
      <!-- Base: half width (col-span-1) so it sits next to Quick links -->
      <!-- md+: normal (col-span-1) -->
      <nav class="col-span-1 md:col-span-1 flex flex-col gap-5 items-start max-md:w-full relative xl:left-[3.5rem]" aria-labelledby="about-us-heading">
        <span id="about-us-heading" class="text-[#0A1119] font-primary text-base font-semibold leading-6 tracking-[0.08px]">
          <?php echo esc_html($col1_heading); ?>
        </span>
        <div class="flex flex-col gap-3 justify-center items-start">
          <?php
          wp_nav_menu([
            'theme_location' => $menu_about,
            'container'      => false,
            'menu_class'     => 'flex flex-col gap-3',
            'fallback_cb'    => function() {
              $default_items = ['Why us', 'Awards & Certification', 'Journal', 'Kind words'];
              foreach ($default_items as $item) {
                echo '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">' . esc_html($item) . '</div></div>';
              }
            },
            'link_before' => '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">',
            'link_after'  => '</div></div>',
          ]);
          ?>
        </div>
      </nav>

      <!-- 3) Quick links -->
      <!-- Base: half width (col-span-1) sitting next to About us -->
      <nav class="col-span-1 md:col-span-1 flex flex-col gap-5 items-start max-md:w-full relative xl:left-[3.5rem]" aria-labelledby="quick-links-heading">
        <span id="quick-links-heading" class="text-[#0A1119] font-primary text-base font-semibold leading-6 tracking-[0.08px]">
          <?php echo esc_html($col2_heading); ?>
        </span>
        <div class="flex flex-col gap-3 justify-center items-start">
          <?php
          wp_nav_menu([
            'theme_location' => $menu_quick,
            'container'      => false,
            'menu_class'     => 'flex flex-col gap-3',
            'fallback_cb'    => function() {
              $default_items = ['Rent', 'Buy', 'Sell'];
              foreach ($default_items as $item) {
                echo '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">' . esc_html($item) . '</div></div>';
              }
            },
            'link_before' => '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">',
            'link_after'  => '</div></div>',
          ]);
          ?>
        </div>
      </nav>

      <!-- 4) Homeowners -->
      <!-- Base: full width -> col-span-2 -->
      <!-- md+: normal -> col-span-1 -->
      <nav class="flex flex-col col-span-2 gap-5 items-start md:col-span-1 max-md:w-full max-md:py-[2.5rem]" aria-labelledby="homeowners-heading">
        <span id="homeowners-heading" class="text-[#0A1119] font-primary text-base font-semibold leading-6 tracking-[0.08px]">
          <?php echo esc_html($col3_heading); ?>
        </span>
        <div class="flex flex-col gap-3 justify-center items-start">
          <?php
          wp_nav_menu([
            'theme_location' => $menu_owners,
            'container'      => false,
            'menu_class'     => 'flex flex-col gap-3',
            'fallback_cb'    => function() {
              $default_items = ['Property management', 'Estate management', 'Refurbishment & Interiors', 'Property valuations', 'Landlords', 'Vendors', 'Private clients'];
              foreach ($default_items as $item) {
                echo '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">' . esc_html($item) . '</div></div>';
              }
            },
            'link_before' => '<div class="flex flex-col gap-2.5 items-start px-0 py-1"><div class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline">',
            'link_after'  => '</div></div>',
          ]);
          ?>
        </div>
      </nav>

      <!-- 5) Get in touch -->
      <!-- Base: full width -> col-span-2 -->
      <!-- md+: normal -> col-span-1 -->
      <div class="col-span-2 md:col-span-1 flex flex-col gap-5 items-start max-md:w-full relative xl:left-[1.5rem]">
        <span class="text-[#0A1119] font-primary text-base font-semibold leading-6 tracking-[0.08px]">Get in touch</span>

        <div class="flex flex-col gap-3.5 justify-center items-start w-full">
          <!-- Phone -->
          <?php if (!empty($phone_number)): ?>
            <div class="flex gap-2 items-center">
              <div class="flex justify-center items-center" aria-hidden="true">
                <!-- phone icon -->
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20.5131 15.5451V18.5451C20.5142 18.8236 20.4572 19.0993 20.3456 19.3544C20.2341 19.6096 20.0704 19.8387 19.8652 20.027C19.66 20.2152 19.4177 20.3586 19.1539 20.4478C18.89 20.537 18.6105 20.5702 18.3331 20.5451C15.256 20.2107 12.3001 19.1592 9.70312 17.4751C7.28694 15.9398 5.23845 13.8913 3.70312 11.4751C2.01309 8.8663 0.96136 5.89609 0.633117 2.8051C0.608127 2.52856 0.640992 2.24986 0.729617 1.98672C0.818243 1.72359 0.960688 1.48179 1.14788 1.27672C1.33508 1.07165 1.56292 0.907806 1.81691 0.795619C2.07089 0.683432 2.34546 0.625358 2.62312 0.625097H5.62312C6.10842 0.620321 6.57891 0.792176 6.94688 1.10863C7.31485 1.42508 7.55519 1.86454 7.62312 2.3451C7.74974 3.30516 7.98457 4.24782 8.32312 5.1551C8.45766 5.51302 8.48678 5.90201 8.40702 6.27598C8.32727 6.64994 8.14198 6.99321 7.87312 7.2651L6.60312 8.5351C8.02667 11.0386 10.0996 13.1115 12.6031 14.5351L13.8731 13.2651C14.145 12.9962 14.4883 12.8109 14.8622 12.7312C15.2362 12.6514 15.6252 12.6806 15.9831 12.8151C16.8904 13.1536 17.8331 13.3885 18.7931 13.5151C19.2789 13.5836 19.7225 13.8283 20.0396 14.2026C20.3568 14.5769 20.5253 15.0547 20.5131 15.5451Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                  <path d="M12.563 0.625122C14.6013 0.839891 16.5052 1.74392 17.9599 3.1877C19.4145 4.63148 20.3329 6.52853 20.563 8.56512" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                  <path d="M12.563 4.62512C13.5465 4.81906 14.449 5.30415 15.1534 6.01743C15.8577 6.73071 16.3314 7.63925 16.513 8.62512" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"/>
                  </svg>

              </div>
              <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone_number)); ?>" class="text-sm tracking-normal leading-6 text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <?php echo esc_html($phone_number); ?>
              </a>
            </div>
          <?php endif; ?>

          <!-- Email -->
          <?php
          $email_to_show = $email_address ?: get_option('admin_email');
          if (!empty($email_to_show)) : ?>
            <div class="flex gap-2 items-center">
              <div class="flex justify-center items-center" aria-hidden="true">
                <!-- mail icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="2" stroke="#0A1119" stroke-width="1.25"/><path d="M22 7L13.03 12.7C12.36 13.12 11.64 13.12 10.97 12.7L2 7" stroke="#0A1119" stroke-width="1.25"/></svg>
              </div>
              <a href="mailto:<?php echo esc_attr($email_to_show); ?>" class="text-sm tracking-normal leading-6 text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <?php echo esc_html($email_to_show); ?>
              </a>
            </div>
          <?php endif; ?>

          <!-- Address -->
          <?php if (!empty($address)) : ?>
            <div class="flex gap-2 items-center">
              <div class="flex justify-center items-center" aria-hidden="true">
                <!-- pin icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.9 4.84 5.84 6.34 4.34C7.84 2.84 9.88 2 12 2C14.12 2 16.16 2.84 17.66 4.34C19.16 5.84 20 7.88 20 10Z" stroke="#0A1119" stroke-width="1.25"/><circle cx="12" cy="10" r="3" stroke="#0A1119" stroke-width="1.25"/></svg>
              </div>
              <address class="text-sm not-italic tracking-normal leading-6 text-primary">
                <?php echo wp_kses_post($address); ?>
              </address>
            </div>
          <?php endif; ?>

          <!-- Social Icons -->
          <?php if (!empty($social_icons) && is_array($social_icons)) : ?>
            <nav class="flex gap-3 items-start max-sm:gap-2" aria-label="Social media links">
              <?php foreach ($social_icons as $social) :
                $label  = $social['social_label'] ?? 'Social link';
                $link   = $social['social_link'] ?? null; // array
                $icon   = $social['social_icon'] ?? 0;    // ID
                $url    = matrix_link_url($link);
                $target = matrix_link_target($link);
                if (!$url || !$icon) continue;
              ?>
                <a
                  href="<?php echo esc_url($url); ?>"
                  target="<?php echo esc_attr($target); ?>"
                  rel="noopener"
                  class="flex flex-col shrink-0 gap-2.5 justify-center items-center w-8 h-8 rounded-[32px] max-sm:w-7 max-sm:h-7 hover:opacity-50 bg-[#EDEDED] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 btn"
                  aria-label="<?php echo esc_attr($label); ?>"
                >
                  <?php echo matrix_render_image($icon, 'thumbnail', $label, ['class' => 'w-full h-full flex-shrink-0']); ?>
                </a>
              <?php endforeach; ?>
            </nav>
          <?php endif; ?>

          <!-- Trustpilot -->
          <?php
          $tp_url    = matrix_link_url($trustpilot_link);
          $tp_target = matrix_link_target($trustpilot_link);
          if ($trustpilot_badge || $tp_url) : ?>
            <div class="mt-2">
              <?php if ($tp_url) : ?><a href="<?php echo esc_url($tp_url); ?>" target="<?php echo esc_attr($tp_target); ?>" rel="noopener"><?php endif; ?>
                <?php
                if ($trustpilot_badge) {
                  echo matrix_render_image($trustpilot_badge, 'medium', 'Trustpilot rating', ['class' => 'h-[47px] w-auto']);
                } else {
                  echo '<span class="text-sm underline">Trustpilot</span>';
                }
                ?>
              <?php if ($tp_url) : ?></a><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="flex flex-col items-center w-full max-w-[1200px]">
      <div class="flex justify-between items-center pt-6 w-full border-t border-solid border-t-slate-300 max-md:flex-col max-md:gap-4 max-md:items-start">

        <!-- Left -->
        <div class="flex flex-1 gap-6 items-center max-md:flex-col max-md:flex-none max-md:gap-2 max-md:items-start">
          <div class="text-sm leading-5 text-stone-800 max-sm:text-xs max-sm:leading-5">
            <?php echo wp_kses_post($copyright_txt); ?>
          </div>
        </div>

        <!-- Right (Legal menu) -->
        <nav class="flex gap-3 items-center max-md:self-start" aria-label="Legal links">
          <?php
          wp_nav_menu([
            'theme_location' => $menu_legal,
            'container'      => false,
            'menu_class'     => 'flex gap-3 items-center text-[#0A1119] font-primary text-sm font-normal leading-[22px]  tracking-normal',
            'fallback_cb'    => function() {
              echo '<a href="#" class="text-sm tracking-normal leading-6 text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Cookie policy</a>';
              echo '<span class="text-[#0A1119] font-primary text-sm font-normal leading-[22px] tracking-normal hover:underline" aria-hidden="true">|</span>';
              echo '<a href="#" class="text-sm tracking-normal leading-6 text-primary hover:underline focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Privacy policy</a>';
            },
            // Keep simple links; no link_before/after wrappers here
          ]);
          ?>
        </nav>
      </div>
    </div>
  </div>
</div>

<?php wp_footer(); ?>
