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
  class="py-4 bg-white border-b-2 border-b-[#B6C0CB] border-solid"
  x-effect="isOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
>
  <nav class="flex justify-between items-center w-full mx-auto max-w-[1168px] px-5 xl:px-0">

    <!-- LEFT: Primary (first half) -->
    <?php if (!empty($left_menu_items)) : ?>
      <ul class="hidden gap-6 items-center leading-loose text-black max-md:gap-6 lg:flex"
          aria-label="Primary navigation (left)">
        <?php foreach ($left_menu_items as $index => $item) : ?>
          <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
            <a href="<?php echo esc_url($item->url); ?>"
               class="flex items-center gap-1 px-4 py-2 rounded-[8px] transition-colors duration-200
                      <?php echo $item->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                      group-hover:bg-[#40BFF5] group-hover:text-black focus:bg-[#40BFF5] focus:text-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 capitalize text-base font-[500] leading-normal">
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
  <ul class="hidden gap-6 items-center leading-loose text-black max-md:gap-6 lg:flex"
      aria-label="Primary navigation (right)">
    <?php foreach ($right_menu_items as $index => $item) : ?>
      <?php
        $is_last_item = ($index === (count($right_menu_items) - 1));
        $dropdown_translate_class = $is_last_item ? '-translate-x-[60%]' : '-translate-x-[25%]';
      ?>
      <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
        <a href="<?php echo esc_url($item->url); ?>"
           class="flex items-center gap-1 px-4 py-2 rounded-[8px] transition-colors duration-200
                  <?php echo $item->active ? 'bg-[#40BFF5] text-black' : 'text-[#1d2838]'; ?>
                  group-hover:bg-[#40BFF5] group-hover:text-black focus:bg-[#40BFF5] focus:text-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 capitalize text-base font-[500] leading-normal">
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
            class="absolute left-1/2 <?php echo esc_attr($dropdown_translate_class); ?> mt-2 p-3 w-[243px]
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

    <!-- Mobile menu (unchanged structure) -->
    <?php get_template_part('template-parts/header/navbar/mobile'); ?>

    <!-- (Optional) phone + CTA if you used them before) -->
    <?php if ($phone_number || $contact_button) : ?>
      <div class="hidden gap-4 pl-4 lg:flex">
        <?php if ($phone_number) : ?>
          <a href="tel:<?php echo esc_attr(preg_replace('/[^+\d]/', '', $phone_number)); ?>"
             class="text-[#1d2838] hover:text-[#025a70] text-base font-[500] flex items-center">
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
