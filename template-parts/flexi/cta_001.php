<?php
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$description = get_sub_field('description');
$decorative_image = get_sub_field('decorative_image');
$decorative_image_alt = get_post_meta($decorative_image, '_wp_attachment_image_alt', true) ?: 'Decorative line';
$button = get_sub_field('button');
$background_color = get_sub_field('background_color');

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

$section_id = 'cta-' . uniqid();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center mx-auto w-full max-w-container max-xl:px-5">
        <div class="flex gap-12 justify-center items-center p-20 w-full max-md:py-8 max-md:px-5">
            <div class="flex flex-col md:flex-row  gap-8 md:gap-16 items-center self-stretch my-auto w-full max-w-[911px]">
                <div class="gap-6 self-stretch my-auto text-gray-50 w-full max-w-[700px] max-md:max-w-full">
                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="text-[#F9FAFB] font-secondary text-3xl font-semibold leading-10 tracking-[-0.16px] max-md:max-w-full"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>
                        
                    <div class="mt-6">
                        <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-slate-300 flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                        </div>
               

                    <?php if (!empty($description)): ?>
                        <div class="mt-6 text-[#F9FAFB] font-primary text-base font-normal leading-[26px] tracking-normal max-md:max-w-full wp_editor">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                            <a
                                href="<?php echo esc_url($button['url']); ?>"
                                class="self-stretch my-auto font-primary text-sm font-semibold leading-[22px] tracking-normal  whitespace-nowrap transition-colors duration-200 text-primary w-fit btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary bg-secondary hover:bg-[#40BFF5] hover:border hover:border-[#40BFF5] border-solid border-primary border h-[44px] flex items-center justify-center px-7  max-md:w-full hover:text-black"
                                target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                                aria-label="<?php echo esc_attr($button['title']); ?>"
                            >
                                <?php echo esc_html($button['title']); ?>
                            </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
