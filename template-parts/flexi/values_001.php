<?php
/**
 * Frontend: Clients 001
 * Notes:
 * - Always used inside ACF Flexible Content with get_sub_field().
 * - Tailwind utilities only; colors via inline style (per spec).
 * - Accessibility: semantic headings, alt/title fallbacks, focusable content.
 */

// -------------------- Fetch all fields up-front (get_sub_field ONLY)
$heading_group           = get_sub_field('heading_group');
$heading_text            = isset($heading_group['heading_text']) ? $heading_group['heading_text'] : '';
$heading_tag             = isset($heading_group['heading_tag']) ? $heading_group['heading_tag'] : 'h2';

$image                   = get_sub_field('image');
$show_divider            = (bool) get_sub_field('show_divider');
$divider_colors          = get_sub_field('divider_colors');

$intro_rich_text         = get_sub_field('intro_rich_text');

$features                = get_sub_field('features');

$background_color        = get_sub_field('background_color');
$text_color              = get_sub_field('text_color');
$section_border_radius   = get_sub_field('section_border_radius');
$image_border_radius     = get_sub_field('image_border_radius');

// Padding repeater -> classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if (!empty($screen_size) && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = esc_attr($screen_size . ':pt-[' . $padding_top . 'rem]');
        }
        if (!empty($screen_size) && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = esc_attr($screen_size . ':pb-[' . $padding_bottom . 'rem]');
        }
    }
}

// -------------------- Safe defaults / fallbacks
$heading_text_fallback = !empty($heading_text) ? $heading_text : 'We are all about our clients';
$allowed_heading_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}

$section_id = 'section-' . uniqid('', true); // Random unique section ID

// Image attrs with fallbacks
$image_url   = '';
$image_alt   = '';
$image_title = '';
$image_width = '';
$image_height = '';

if (is_array($image)) {
    $image_url   = isset($image['url']) ? $image['url'] : '';
    $image_alt   = isset($image['alt']) && $image['alt'] !== '' ? $image['alt'] : 'Decorative image';
    $image_title = isset($image['title']) && $image['title'] !== '' ? $image['title'] : 'Image';
    $image_width = isset($image['width']) ? (int) $image['width'] : '';
    $image_height = isset($image['height']) ? (int) $image['height'] : '';
}

// Divider colors (4 bars)
$div_colors = [
    isset($divider_colors['color_1']) ? $divider_colors['color_1'] : '#1D4ED8',
    isset($divider_colors['color_2']) ? $divider_colors['color_2'] : '#F97316',
    isset($divider_colors['color_3']) ? $divider_colors['color_3'] : '#10B981',
    isset($divider_colors['color_4']) ? $divider_colors['color_4'] : '#6B7280',
];

// Compose dynamic classes
$section_classes = trim('relative flex overflow-hidden');
$inner_wrapper_classes = trim('flex flex-col items-center w-full mx-auto max-w-container py-20 max-xl:px-5 ' . implode(' ', $padding_classes));

// Section style (colors)
$section_style = '';
if (!empty($background_color) || !empty($text_color)) {
    $section_style = 'style="';
    if (!empty($background_color)) {
        $section_style .= 'background-color: ' . esc_attr($background_color) . ';';
    }
    if (!empty($text_color)) {
        $section_style .= ' color: ' . esc_attr($text_color) . ';';
    }
    $section_style .= '"';
}
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr($section_classes); ?> <?php echo esc_attr($section_border_radius ? $section_border_radius : ''); ?>" <?php echo $section_style; ?>>
    <div class="<?php echo esc_attr($inner_wrapper_classes); ?>">
        <div class="mx-auto w-full max-w-7xl">
            <div class="flex flex-col gap-8 items-start md:flex-row md:gap-12 max-xl:px-5">
                <div class="flex-shrink-0 w-full md:w-1/2  lg:w-[41%]">
                    <?php if (!empty($image_url)) : ?>
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            <?php if (!empty($image_width)) : ?>width="<?php echo esc_attr((string)$image_width); ?>"<?php endif; ?>
                            <?php if (!empty($image_height)) : ?>height="<?php echo esc_attr((string)$image_height); ?>"<?php endif; ?>
                            alt="<?php echo esc_attr($image_alt); ?>"
                            title="<?php echo esc_attr($image_title); ?>"
                            loading="lazy"
                            decoding="async"
                            class="w-full h-auto max-w-md lg:max-w-none object-contain lg:object-cover max-h-[480px] <?php echo esc_attr($image_border_radius ? $image_border_radius : 'rounded-none'); ?>"
                        />
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-6 w-full md:w-1/2 lg:w-[59%]">
                    <div class="flex flex-col gap-6">
                        <<?php echo tag_escape($heading_tag); ?> class="text-[#0A1119] text-left font-secondary text-[32px] font-semibold leading-[40px] tracking-[-0.16px]">
                            <?php echo esc_html($heading_text_fallback); ?>
                        </<?php echo tag_escape($heading_tag); ?>>

                        <?php if ($show_divider) : ?>
                            <div class="flex gap-0.5 justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($intro_rich_text)) : ?>
                        <div class="text-black max-lg:hidden font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
                            <?php
                            // Intro is WYSIWYG -> allow safe HTML output
                            echo wp_kses_post($intro_rich_text);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($features) && is_array($features)) : ?>
                        <div class="grid grid-cols-1 gap-6 mt-2 lg:grid-cols-2 md:gap-8">
                            <?php foreach ($features as $feature) :
                                $bar_color = isset($feature['bar_color']) ? $feature['bar_color'] : '#1D4ED8';
                                $feature_heading = isset($feature['feature_heading']) ? $feature['feature_heading'] : '';
                                $feature_text = isset($feature['feature_text']) ? $feature['feature_text'] : '';
                            ?>
                                <div class="flex gap-4 md:gap-6 bg-[#E0E0E0] p-5">
                                    <div class="flex-shrink-0 w-1" style="background-color: <?php echo esc_attr($bar_color); ?>;" aria-hidden="true"></div>
                                    <div class="flex flex-col gap-2">
                                        <?php if (!empty($feature_heading)) : ?>
                                            <h3 class="text-[#0A1119] font-secondary text-[24px] font-semibold leading-[26px] tracking-[-0.16px]">
                                                <?php echo esc_html($feature_heading); ?>
                                            </h3>
                                        <?php endif; ?>
                                        <?php if (!empty($feature_text)) : ?>
                                            <div class="text-black font-primary text-base font-normal leading-[26px] tracking-normal wp_editor">
                                                <?php echo wp_kses_post($feature_text); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>
