<?php
// Get all ACF fields
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$left_heading = get_sub_field('left_heading');
$left_heading_tag = get_sub_field('left_heading_tag');
$left_content = get_sub_field('left_content');
$left_image = get_sub_field('left_image');
$left_image_alt = get_post_meta($left_image, '_wp_attachment_image_alt', true) ?: 'Expert guidance illustration';
$right_heading = get_sub_field('right_heading');
$right_heading_tag = get_sub_field('right_heading_tag');
$right_content = get_sub_field('right_content');
$right_image = get_sub_field('right_image');
$right_image_alt = get_post_meta($right_image, '_wp_attachment_image_alt', true) ?: 'Property sales and lettings illustration';
$cta_button = get_sub_field('cta_button');
$background_color = get_sub_field('background_color');

// Generate unique section ID
$section_id = 'expert-guidance-' . wp_rand(1000, 9999);

// Build padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden bg-[#EDEDED] <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center py-20 mx-auto w-full max-md:py-8 max-w-container max-xl:px-6">

        <!-- Main Heading Section -->
        <?php if (!empty($heading)): ?>
        <header class="flex flex-col gap-6 items-center self-stretch mb-12">
            <div class="flex flex-col gap-6 items-center self-stretch">
                <<?php echo esc_attr($heading_tag); ?>
                    id="<?php echo esc_attr($section_id); ?>-heading"
                    class="text-3xl font-semibold tracking-normal leading-10 text-center font-secondary text-primary max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8 max-md:text-left"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <!-- Decorative Color Bars -->
                <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px] max-md:mr-auto" role="presentation" aria-hidden="true">
                    <div class="bg-orange-500 flex-1 h-[5px]"></div>
                    <div class="bg-sky-500 flex-1 h-[5px]"></div>
                    <div class="bg-slate-300 flex-1 h-[5px]"></div>
                    <div class="bg-lime-600 flex-1 h-[5px]"></div>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-12 items-start self-stretch max-md:gap-8 max-sm:gap-6">

            <!-- Left Content Block -->
            <article class="flex flex-col gap-8 items-start max-md:gap-6 max-sm:gap-5">
                <div class="flex flex-col gap-2.5 items-start self-stretch">
                    <?php if (!empty($left_heading)): ?>
                    <<?php echo esc_attr($left_heading_tag); ?> class="text-[#0A1119] font-secondary text-[24px] font-semibold leading-[26px] tracking-[-0.16px]">
                        <?php echo esc_html($left_heading); ?>
                    </<?php echo esc_attr($left_heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($left_content)): ?>
                    <div class="text-black text-[16px] font-normal leading-[26px] tracking-[0] wp_editor max-md:flex-col max-md:gap-8 max-sm:gap-6 font-primary">
                        <?php echo wp_kses_post($left_content); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($left_image): ?>
                <div class="self-stretch">
                    <?php echo wp_get_attachment_image($left_image, 'full', false, [
                        'alt' => esc_attr($left_image_alt),
                        'class' => 'object-cover w-full h-[220px] max-sm:h-[180px] md:rounded',
                        'loading' => 'lazy'
                    ]); ?>
                </div>
                <?php endif; ?>
            </article>

            <!-- Vertical Separator -->
            <div
                class="justify-between items-start self-stretch w-1 max-md:hidden"
                role="presentation"
                aria-hidden="true"
            >
                <div class="self-stretch w-full md:w-1 shrink-0 bg-[#E0E0E0] md:h-full"></div>
            </div>

            <!-- Right Content Block -->
            <article class="flex flex-col gap-8 items-start max-md:flex-col-reverse max-md:gap-6 max-sm:gap-5">
                <?php if ($right_image): ?>
                <div class="self-stretch">
                    <?php echo wp_get_attachment_image($right_image, 'full', false, [
                        'alt' => esc_attr($right_image_alt),
                        'class' => 'object-cover w-full h-[220px] max-sm:h-[180px] md:rounded',
                        'loading' => 'lazy'
                    ]); ?>
                </div>
                <?php endif; ?>

                <div class="flex flex-col gap-2.5 items-start self-stretch max-md:flex-col max-md:gap-8 max-sm:gap-6">
                    <?php if (!empty($right_heading)): ?>
                    <<?php echo esc_attr($right_heading_tag); ?> class="text-[#0A1119] font-secondary text-[24px] font-semibold leading-[26px] tracking-[-0.16px]">
                        <?php echo esc_html($right_heading); ?>
                    </<?php echo esc_attr($right_heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($right_content)): ?>
                    <div class="text-black  text-[16px] font-normal leading-[26px] tracking-[0] wp_editor max-md:flex-col max-md:gap-8 max-sm:gap-6 font-primary">
                        <?php echo wp_kses_post($right_content); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <!-- CTA Button -->
        <?php if ($cta_button && is_array($cta_button) && isset($cta_button['url'], $cta_button['title'])): ?>
        <div class="mt-12">
            <a
                href="<?php echo esc_url($cta_button['url']); ?>"
                class="xl:min-w-[325px] font-primary flex gap-2.5 justify-center items-center px-6 py-3 h-11 text-sm font-semibold tracking-normal leading-[22px] whitespace-nowrap transition-all duration-300 w-fit bg-[#0A1119] text-slate-50 hover:text-black  hover:bg-[#40BFF5] focus:bg-[#40BFF5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119] max-sm:px-5 max-sm:w-full max-sm:h-12 btn"
                target="<?php echo esc_attr($cta_button['target'] ?? '_self'); ?>"
                aria-label="<?php echo esc_attr($cta_button['title']); ?>"
            >
                <span><?php echo esc_html($cta_button['title']); ?></span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
