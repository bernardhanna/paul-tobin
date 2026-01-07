<?php
// Logo: prefer WP Site Logo, fallback to ACF option 'logo'
$theme_logo_id = get_theme_mod('custom_logo');
$acf_logo_id   = get_field('logo', 'option');
$logo_id       = $theme_logo_id ?: $acf_logo_id;

$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? (get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name')) : get_bloginfo('name');

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
?>

<section
  id="site-nav"
  x-data="{
    isOpen: false,
    activeDropdown: null,
    toggleDropdown(index) {
      this.activeDropdown = (this.activeDropdown === index ? null : index);
    },
    checkWindowSize() {
      if (window.innerWidth > 1084) {
        this.isOpen = false;
        this.activeDropdown = null;
      }
    }
  }"
  x-init="window.addEventListener('resize', () => checkWindowSize())"
  class="py-4 bg-white"
  x-effect="isOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
>
  <nav class="flex justify-between items-center w-full mx-auto max-w-[1168px] px-5 lg:px-0">

    <!-- LEFT: Primary (first half) -->
    <?php if (!empty($left_menu_items)) : ?>
      <ul class="hidden gap-9 items-center leading-loose text-black max-md:gap-6 lg:flex"
          aria-label="Primary navigation (left)">
        <?php foreach ($left_menu_items as $index => $item) : ?>
          <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <a href="<?php echo esc_url($item->url); ?>"
               class="gap-2.5 self-stretch my-auto whitespace-nowrap text-[#1d2838] hover:text-[#025a70] text-base font-normal leading-normal flex items-center capitalize <?php echo $item->active ? 'active-item' : ''; ?>">
              <?php echo esc_html($item->label); ?>
              <?php if (!empty($item->children)) : ?>
                <span class="ml-[2px]">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none">
                    <path d="M4.25 6.875L8.5 11.125L12.75 6.875" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
              <?php endif; ?>
            </a>

            <?php if (!empty($item->children)) : ?>
              <ul class="absolute left-0 hidden space-y-2 border-b-2 border-primary bg-white group-hover:block min-w-[200px] z-50">
                <?php foreach ($item->children as $child) : ?>
                  <li class="group <?php echo esc_attr($child->classes); ?> <?php echo $child->active ? 'current-item' : ''; ?> hover:bg-secondary">
                    <a href="<?php echo esc_url($child->url); ?>"
                       class="block px-4 py-2 text-sm font-normal leading-normal text-[#1d2838] ">
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

    <!-- CENTER: Logo (centered) -->
    <a style="z-index: 99999999;" href="<?php echo esc_url(home_url('/')); ?>" class="flex justify-center">
      <?php if ($logo_url) : ?>
        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" />
      <?php else : ?>
        <span><?php echo esc_html(get_bloginfo('name')); ?></span>
      <?php endif; ?>
    </a>

    <!-- RIGHT: Primary (second half) -->
    <?php if (!empty($right_menu_items)) : ?>
      <ul class="hidden gap-9 items-center leading-loose text-black max-md:gap-6 lg:flex"
          aria-label="Primary navigation (right)">
        <?php foreach ($right_menu_items as $index => $item) : ?>
          <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <a href="<?php echo esc_url($item->url); ?>"
               class="gap-2.5 self-stretch my-auto whitespace-nowrap text-[#1d2838] hover:text-[#025a70] text-base font-normal leading-normal flex items-center capitalize <?php echo $item->active ? 'active-item' : ''; ?>">
              <?php echo esc_html($item->label); ?>
              <?php if (!empty($item->children)) : ?>
                <span class="ml-[2px]">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 17 18" fill="none">
                    <path d="M4.25 6.875L8.5 11.125L12.75 6.875" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
              <?php endif; ?>
            </a>

            <?php if (!empty($item->children)) : ?>
              <ul class="absolute left-0 hidden space-y-2 border-b-2 border-primary bg-white group-hover:block min-w-[200px] z-50">
                <?php foreach ($item->children as $child) : ?>
                  <li class="group <?php echo esc_attr($child->classes); ?> <?php echo $child->active ? 'current-item' : ''; ?> hover:bg-secondary">
                    <a href="<?php echo esc_url($child->url); ?>"
                       class="block px-4 py-2 text-sm font-normal leading-normal text-[#1d2838] ">
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

    <!-- Mobile menu (unchanged structure) -->
    <?php get_template_part('template-parts/header/navbar/mobile'); ?>

    <!-- (Optional) phone + CTA if you used them before -->
    <?php if ($phone_number || $contact_button) : ?>
      <div class="hidden gap-4 pl-4 lg:flex">
        <?php if ($phone_number) : ?>
          <a href="tel:<?php echo esc_attr(preg_replace('/[^+\d]/', '', $phone_number)); ?>"
             class="text-[#1d2838] hover:text-[#025a70] text-base font-normal flex items-center">
            <?php echo esc_html($phone_number); ?>
          </a>
        <?php endif; ?>
        <?php if (!empty($contact_button['url'])) : ?>
          <a href="<?php echo esc_url($contact_button['url']); ?>"
             target="<?php echo esc_attr($contact_button['target'] ?? '_self'); ?>"
             class="px-4 py-2 font-semibold text-black rounded btn bg-secondary hover:bg-orange-500">
            <?php echo esc_html($contact_button['title'] ?? 'Contact'); ?>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </nav>
</section>

<script>
  // Re-enable Headroom on the SAME element as before
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
