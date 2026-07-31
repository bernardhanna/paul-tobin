<?php
// Logo: prefer WP Site Logo, fallback to ACF option 'logo'
$theme_logo_id = get_theme_mod('custom_logo');
$acf_logo_id   = get_field('logo', 'option');
$logo_id       = $theme_logo_id ?: $acf_logo_id;

$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? (get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name')) : get_bloginfo('name');
$logo_meta = $logo_id ? wp_get_attachment_image_src($logo_id, 'full') : false;
$logo_w = (is_array($logo_meta) && !empty($logo_meta[1])) ? (int) $logo_meta[1] : 200;
$logo_h = (is_array($logo_meta) && !empty($logo_meta[2])) ? (int) $logo_meta[2] : 80;

// Optional: phone + CTA
$nav_settings   = get_field('navigation_settings_start', 'option') ?: [];
$phone_number   = $nav_settings['phone_number'] ?? null;
$contact_button = $nav_settings['contact_button'] ?? null;

use Log1x\Navi\Navi;

$primary_navigation = Navi::make()->build('primary');

// Split primary menu evenly (left/right) for the centered logo layout
$left_menu_items  = [];
$right_menu_items = [];
if ($primary_navigation->isNotEmpty()) {
  $items = $primary_navigation->toArray();
  $count = count($items);
  $left_count = (int) floor($count / 2);
  $left_menu_items  = array_slice($items, 0, $left_count);
  $right_menu_items = array_slice($items, $left_count);
}

// Split desktop nav from just over 1200px; hamburger only at 1200px and below.
$desktop_nav_min = 1201;
?>

<style>
  /*
   * Theme hamburger CSS includes `lg:hidden` (1084px), which hides the button too early.
   * These rules own show/hide so mid-width desktops still get a working hamburger.
   */
  #site-nav .site-nav-desktop,
  #site-nav .site-nav-desktop-spacer {
    display: none !important;
  }
  #site-nav .site-nav-mobile-wrap {
    display: flex !important;
    align-items: center;
    margin-left: auto;
    z-index: 60;
  }
  #site-nav .site-nav-mobile-wrap .hamburger {
    display: inline-block !important;
    position: relative;
    z-index: 61;
  }
  /* Full-screen flyout must position against the nav bar, not the hamburger wrap. */
  #site-nav .site-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  }
  #site-nav #site-mobile-nav {
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    width: 100% !important;
    max-width: 100vw;
    z-index: 55;
  }
  #site-nav .site-nav-logo-link {
    position: relative;
    z-index: 1;
  }

  @media (min-width: <?php echo (int) $desktop_nav_min; ?>px) {
    #site-nav .site-nav-mobile-wrap,
    #site-nav .site-nav-mobile-wrap .hamburger {
      display: none !important;
    }
    #site-nav .site-nav-bar {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
      align-items: center;
      column-gap: 1rem;
      max-width: 96rem;
      margin-left: auto;
      margin-right: auto;
      padding-left: 1.25rem;
      padding-right: 1.25rem;
    }
    #site-nav .site-nav-desktop {
      display: flex !important;
      min-width: 0;
      align-items: center;
    }
    #site-nav .site-nav-desktop-spacer {
      display: block !important;
      min-width: 0;
    }
    #site-nav .site-nav-desktop--left {
      justify-content: flex-end;
      padding-right: 0.5rem;
    }
    #site-nav .site-nav-desktop--right {
      justify-content: flex-start;
      padding-left: 0.5rem;
      gap: 0.75rem;
    }
    #site-nav .site-nav-list {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 0;
    }
    #site-nav .site-nav-link {
      font-size: 0.8rem;
      line-height: 1.5;
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }
    #site-nav .site-nav-logo {
      max-width: 180px !important;
      max-height: 3.5rem !important;
    }
  }

  @media (min-width: 1351px) {
    #site-nav .site-nav-link {
      font-size: 1rem;
    }
  }

  @media (min-width: 1600px) {
    #site-nav .site-nav-bar {
      column-gap: 1.25rem;
    }
    #site-nav .site-nav-list {
      gap: 1rem;
    }
    #site-nav .site-nav-link {
      padding-left: 0.9rem;
      padding-right: 0.9rem;
    }
    #site-nav .site-nav-logo {
      max-width: 200px !important;
      max-height: 4rem !important;
    }
  }
</style>

<section
  id="site-nav"
  x-data="{
    isOpen: false,
    activeDropdown: null,
    toggleDropdown(index) {
      this.activeDropdown = (this.activeDropdown === index ? null : index);
    },
    checkWindowSize() {
      if (window.innerWidth >= <?php echo (int) $desktop_nav_min; ?>) {
        this.isOpen = false;
        this.activeDropdown = null;
      }
    }
  }"
  x-init="window.addEventListener('resize', () => checkWindowSize())"
  class="py-4 bg-white border-b-2 border-b-[#B6C0CB] border-solid"
  x-effect="isOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
>
  <nav class="site-nav-bar relative w-full mx-auto px-5">

    <!-- LEFT: Primary (first half) -->
    <?php if (!empty($left_menu_items)) : ?>
      <div class="site-nav-desktop site-nav-desktop--left">
      <ul class="site-nav-list leading-loose text-black"
          aria-label="Primary navigation (left)">
        <?php foreach ($left_menu_items as $index => $item) : ?>
          <li class="relative group shrink-0 <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <a href="<?php echo esc_url($item->url); ?>"
               class="site-nav-link flex font-[500] items-center gap-1 py-2 rounded-[8px] transition-colors duration-200
                      <?php echo $item->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                      group-hover:bg-[#40BFF5] group-hover:text-black focus:bg-[#40BFF5] focus:text-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 capitalize leading-normal whitespace-nowrap">
              <?php echo esc_html($item->label); ?>
              <?php if (!empty($item->children)) : ?>
                <span class="ml-[2px]">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none" class="shrink-0">
                    <path d="M4.25 6.875L8.5 11.125L12.75 6.875"
                          class="transition-colors duration-200 <?php echo $item->active ? 'stroke-black' : 'stroke-[#1D2939]'; ?> group-hover:stroke-black"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
              <?php endif; ?>
            </a>

            <?php if (!empty($item->children)) : ?>
              <ul
                class="absolute left-1/2 -translate-x-[35%] mt-2 p-3 w-[243px]
bg-gray-50 rounded-none border-b-4 border-solid border-b-slate-300 shadow-lg z-50
opacity-0 invisible -translate-y-2 transition-all duration-200 ease-in-out
group-hover:opacity-100 group-hover:visible group-hover:translate-y-0
focus-within:opacity-100 focus-within:visible focus-within:translate-y-0"
                role="menu"
              >
                <?php foreach ($item->children as $child) : ?>
                  <li class="group <?php echo esc_attr($child->classes); ?> <?php echo $child->active ? 'current-item' : ''; ?>" role="none">
                    <a href="<?php echo esc_url($child->url); ?>"
                       class="menu-item block px-4 py-2 rounded-[8px] text-sm font-semibold leading-[1.375rem] transition-colors duration-200
                              <?php echo $child->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                              hover:bg-sky-100 focus:bg-sky-100 focus:outline-none"
                       role="menuitem"
                    >
                      <?php echo esc_html($child->label); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      </div>
    <?php else : ?>
      <div class="site-nav-desktop-spacer" aria-hidden="true"></div>
    <?php endif; ?>

    <!-- CENTER: Logo -->
    <a style="z-index: 1;" href="<?php echo esc_url(home_url('/')); ?>" class="site-nav-logo-link flex justify-center shrink-0">
      <?php if ($logo_url) : ?>
        <img
          src="<?php echo esc_url($logo_url); ?>"
          alt="<?php echo esc_attr($logo_alt); ?>"
          width="<?php echo esc_attr((string) $logo_w); ?>"
          height="<?php echo esc_attr((string) $logo_h); ?>"
          class="site-nav-logo h-auto w-auto"
          decoding="async"
        />
      <?php else : ?>
        <span><?php echo esc_html(get_bloginfo('name')); ?></span>
      <?php endif; ?>
    </a>

    <!-- RIGHT: Primary (second half) + optional phone/CTA -->
<?php if (!empty($right_menu_items) || $phone_number || $contact_button) : ?>
  <div class="site-nav-desktop site-nav-desktop--right">
  <?php if (!empty($right_menu_items)) : ?>
  <ul class="site-nav-list leading-loose text-black"
      aria-label="Primary navigation (right)">
    <?php foreach ($right_menu_items as $index => $item) : ?>
      <?php
        $is_last_item = ($index === (count($right_menu_items) - 1));
        $dropdown_position_class = $is_last_item ? 'right-0 left-auto translate-x-0' : 'left-1/2 -translate-x-[25%]';
      ?>
      <li class="relative group shrink-0 <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
        <a href="<?php echo esc_url($item->url); ?>"
           class="site-nav-link flex items-center gap-1 py-2 rounded-[8px] transition-colors duration-200
                  <?php echo $item->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                  group-hover:bg-[#40BFF5] group-hover:text-black focus:bg-[#40BFF5] focus:text-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 capitalize font-[500] leading-normal whitespace-nowrap">
          <?php echo esc_html($item->label); ?>
          <?php if (!empty($item->children)) : ?>
            <span class="ml-[2px]">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none" class="shrink-0">
                <path d="M4.25 6.875L8.5 11.125L12.75 6.875"
                      class="transition-colors duration-200 <?php echo $item->active ? 'stroke-black' : 'stroke-[#1D2939]'; ?> group-hover:stroke-black"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
          <?php endif; ?>
        </a>

        <?php if (!empty($item->children)) : ?>
          <ul
            class="absolute <?php echo esc_attr($dropdown_position_class); ?> mt-2 p-3 w-[243px]
                   bg-gray-50 rounded-none border-b-4 border-solid border-b-slate-300 shadow-lg z-50
                   opacity-0 invisible -translate-y-2 transition-all duration-200 ease-in-out
                   group-hover:opacity-100 group-hover:visible group-hover:translate-y-0
                   focus-within:opacity-100 focus-within:visible focus-within:translate-y-0"
            role="menu"
          >
            <?php foreach ($item->children as $child) : ?>
              <li class="group <?php echo esc_attr($child->classes); ?> <?php echo $child->active ? 'current-item' : ''; ?>" role="none">
                <a href="<?php echo esc_url($child->url); ?>"
                   class="menu-item block px-4 py-2 rounded-[8px] text-sm font-[500] leading-normal transition-colors duration-200
                          <?php echo $child->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                          hover:bg-sky-100 focus:bg-sky-100 focus:outline-none"
                   role="menuitem"
                >
                  <?php echo esc_html($child->label); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

    <?php if ($phone_number || $contact_button) : ?>
      <div class="flex gap-2 items-center border-l border-[#B6C0CB]/60 pl-2 shrink-0">
        <?php if ($phone_number) : ?>
          <a href="tel:<?php echo esc_attr(preg_replace('/[^+\d]/', '', $phone_number)); ?>"
             class="text-[#1d2838] hover:text-[#025a70] text-sm font-[500] flex items-center whitespace-nowrap">
            <?php echo esc_html($phone_number); ?>
          </a>
        <?php endif; ?>
        <?php if (!empty($contact_button['url'])) : ?>
          <a href="<?php echo esc_url($contact_button['url']); ?>"
             target="<?php echo esc_attr($contact_button['target'] ?? '_self'); ?>"
             class="px-3 py-2 text-sm font-semibold text-black whitespace-nowrap rounded btn bg-secondary hover:bg-orange-500">
            <?php echo esc_html($contact_button['title'] ?? 'Contact'); ?>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php else : ?>
  <div class="site-nav-desktop-spacer" aria-hidden="true"></div>
<?php endif; ?>

    <div class="site-nav-mobile-wrap">
      <?php get_template_part('template-parts/header/navbar/mobile'); ?>
    </div>

  </nav>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.Headroom) {
      var el = document.getElementById('site-nav');
      if (el) {
        var headroom = new Headroom(el);
        headroom.init();
      }
    }
  });
</script>
