<?php
$heading_group         = get_sub_field('heading_group');
$heading_text          = isset($heading_group['heading_text']) ? $heading_group['heading_text'] : '';
$heading_tag           = isset($heading_group['heading_tag']) ? $heading_group['heading_tag'] : 'h2';
$show_divider          = (bool) get_sub_field('show_divider');
$intro_rich_text       = get_sub_field('intro_rich_text');
$desktop_columns       = (string) get_sub_field('desktop_columns');
$features              = get_sub_field('features');
$background_color      = get_sub_field('background_color');
$card_background_color = get_sub_field('card_background_color');
$text_color            = get_sub_field('text_color');

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
$heading_text_fallback = $heading_text ?: 'Why Choose Us';
$allowed_heading_tags  = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}

$section_id = 'section-' . uniqid('', true);

// Compose classes/styles
$section_classes = 'relative flex overflow-hidden';
$inner_wrapper_classes = 'flex flex-col items-center w-full mx-auto max-w-container py-8 md:py-20 max-xl:px-5 '
    . implode(' ', array_map('esc_attr', $padding_classes));

$grid_class = ($desktop_columns === '2') ? 'lg:grid-cols-2' : 'lg:grid-cols-3';

$style_bits = [];
if (!empty($background_color)) {
    $style_bits[] = 'background-color:' . esc_attr($background_color);
}
if (!empty($text_color)) {
    $style_bits[] = 'color:' . esc_attr($text_color);
}
$section_style_attr = $style_bits ? ' style="' . implode(';', $style_bits) . ';"' : '';

$card_style_attr = '';
if (!empty($card_background_color)) {
    $card_style_attr = ' style="background-color:' . esc_attr($card_background_color) . ';"';
}
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr($section_classes); ?>"<?php echo $section_style_attr; ?>>
    <div class="<?php echo esc_attr($inner_wrapper_classes); ?>">
        <div class="mx-auto w-full max-w-7xl">
            <div class="flex flex-col gap-6">
                <<?php echo tag_escape($heading_tag); ?> class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9 max-sm:leading-8">
                    <?php echo esc_html($heading_text_fallback); ?>
                </<?php echo tag_escape($heading_tag); ?>>

                <?php if ($show_divider) : ?>
                    <div class="flex justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($intro_rich_text)) : ?>
                    <div class="text-black font-primary text-[16px] font-normal leading-[26px] tracking-[0] wp_editor max-w-4xl">
                        <?php echo wp_kses_post($intro_rich_text); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($features) && is_array($features)) : ?>
                <div class="grid grid-cols-1 gap-6 mt-8 md:gap-8 <?php echo esc_attr($grid_class); ?>">
                    <?php foreach ($features as $feature) :
                        $feature_heading = isset($feature['feature_heading']) ? $feature['feature_heading'] : '';
                        $feature_text    = isset($feature['feature_text']) ? $feature['feature_text'] : '';
                        $bar_color       = !empty($feature['bar_color']) ? $feature['bar_color'] : '#0ea5e9';
                    ?>
                        <div class="flex gap-4 md:gap-6 p-5" <?php echo $card_style_attr; ?>>
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
</section>
