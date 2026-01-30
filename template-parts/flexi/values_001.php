<?php
$heading_group           = get_sub_field('heading_group');
$heading_text            = isset($heading_group['heading_text']) ? $heading_group['heading_text'] : '';
$heading_tag             = isset($heading_group['heading_tag']) ? $heading_group['heading_tag'] : 'h2';

$image                   = get_sub_field('image');
$show_divider            = (bool) get_sub_field('show_divider');

$intro_rich_text         = get_sub_field('intro_rich_text');
$features                = get_sub_field('features');

$background_color        = get_sub_field('background_color');
$text_color              = get_sub_field('text_color');
$section_border_radius   = get_sub_field('section_border_radius');
$image_border_radius     = get_sub_field('image_border_radius');

// Padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = $screen_size . ':pt-[' . $padding_top . 'rem]';
        }
        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = $screen_size . ':pb-[' . $padding_bottom . 'rem]';
        }
    }
}

// Defaults / safety
$heading_text_fallback = $heading_text ?: 'We are all about our clients';
$allowed_heading_tags  = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}

$section_id = 'section-' . uniqid('', true);

// Image attrs
$image_url   = '';
$image_alt   = '';
$image_title = '';
$image_width = '';
$image_height= '';

if (is_array($image)) {
    $image_url    = isset($image['url']) ? $image['url'] : '';
    $image_alt    = !empty($image['alt']) ? $image['alt'] : 'Decorative image';
    $image_title  = !empty($image['title']) ? $image['title'] : 'Image';
    $image_width  = isset($image['width']) ? (int)$image['width'] : '';
    $image_height = isset($image['height']) ? (int)$image['height'] : '';
}

// Compose classes/styles
$section_classes        = 'relative flex overflow-hidden'
    . (!empty($section_border_radius) ? ' ' . $section_border_radius : '');
$inner_wrapper_classes  = 'flex flex-col items-center w-full mx-auto max-w-container py-8 md:py-20 max-xl:px-5 '
    . implode(' ', array_map('esc_attr', $padding_classes));

$style_bits = [];
if (!empty($background_color)) $style_bits[] = 'background-color:' . esc_attr($background_color);
if (!empty($text_color))       $style_bits[] = 'color:' . esc_attr($text_color);
$section_style_attr = $style_bits ? ' style="' . implode(';', $style_bits) . ';"' : '';

// Ensure max 2 features
if (is_array($features)) {
    $features = array_slice($features, 0, 2);
}
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr($section_classes); ?>"<?php echo $section_style_attr; ?>>
    <div class="<?php echo esc_attr($inner_wrapper_classes); ?>">
        <div class="mx-auto w-full max-w-7xl">
                 <div class="hidden flex-col gap-6 mb-5 max-lg:flex">
                        <div class="flex flex-col gap-6 text-left">
                            <<?php echo tag_escape($heading_tag); ?> class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8">
                                <?php echo esc_html($heading_text_fallback); ?>
                            </<?php echo tag_escape($heading_tag); ?>>

                            <?php if ($show_divider) : ?>
                                <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
                                    <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                    <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                    <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                                    <div class="bg-lime-600 flex-1 h-[5px]"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php if (!empty($intro_rich_text)) : ?>
                        <div class="text-black max-sm:hidden  font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
                            <?php echo wp_kses_post($intro_rich_text); ?>
                        </div>
                    <?php endif; ?>
                    </div>
            <div class="flex flex-col gap-8 items-start sm:flex-row md:gap-12">
            
                <!-- Image -->
                <div class="flex-shrink-0 w-full sm:w-1/2 xl:w-[41%]">
                    <?php if (!empty($image_url)) : ?>
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            <?php if (!empty($image_width))  : ?>width="<?php echo esc_attr((string)$image_width); ?>"<?php endif; ?>
                            <?php if (!empty($image_height)) : ?>height="<?php echo esc_attr((string)$image_height); ?>"<?php endif; ?>
                            alt="<?php echo esc_attr($image_alt); ?>"
                            title="<?php echo esc_attr($image_title); ?>"
                            loading="lazy" decoding="async"
                            class="w-full h-auto max-w-md lg:max-w-none object-contain lg:object-cover max-h-[480px] <?php echo esc_attr($image_border_radius ?: 'rounded-none'); ?>"
                        />
                    <?php endif; ?>
                    <?php if (!empty($intro_rich_text)) : ?>
                        <div class="text-black mt-8  max-sm:flex hidden font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
                            <?php echo wp_kses_post($intro_rich_text); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Copy & features -->
                <div class="flex flex-col gap-6 w-full sm:w-1/2 xl:w-[59%]">
                    <div class="flex flex-col gap-6 max-lg:hidden">
                        <<?php echo tag_escape($heading_tag); ?> class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8 max-lg:hidden">
                            <?php echo esc_html($heading_text_fallback); ?>
                        </<?php echo tag_escape($heading_tag); ?>>

                        <?php if ($show_divider) : ?>
                            <div class="flex  max-lg:hidden justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($intro_rich_text)) : ?>
                        <div class="text-black max-lg:hidden font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor">
                            <?php echo wp_kses_post($intro_rich_text); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($features) && is_array($features)) : ?>
                        <div class="grid grid-cols-1 gap-6 mt-2 lg:grid-cols-2 md:gap-8">
                            <?php foreach ($features as $i => $feature) :
                                $feature_heading = isset($feature['feature_heading']) ? $feature['feature_heading'] : '';
                                $feature_text    = isset($feature['feature_text']) ? $feature['feature_text'] : '';

                                // Fixed bar colors by index:
                                // 0 => #0ea5e9, 1 => #74af27
                                $bar_color = ($i === 0) ? '#0ea5e9' : '#74af27';
                            ?>
                                <div class="flex gap-4 md:gap-6 bg-[#E0E0E0] p-5">
                                    <div class="flex-shrink-0 w-1" style="background-color: <?php echo esc_attr($bar_color); ?>;" aria-hidden="true"></div>
                                    <div class="flex flex-col gap-2">
                                        <?php if (!empty($feature_heading)) : ?>
                                            <span class="text-[#0A1119] font-secondary text-[24px] lg:text-[1.5rem] font-semibold leading-[26px] tracking-[-0.16px]">
                                                <?php echo esc_html($feature_heading); ?>
                                            </span>
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
