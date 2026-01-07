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
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="flex gap-12 justify-center items-center p-20 w-full max-md:px-5">
            <div class="flex flex-col md:flex-row  gap-16 items-center self-stretch my-auto w-full max-w-[911px]">
                <div class="gap-6 self-stretch my-auto text-gray-50 w-full max-w-[700px] max-md:max-w-full">
                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="text-3xl font-semibold tracking-normal leading-10 text-gray-50 font-secondary max-md:max-w-full"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if ($decorative_image): ?>
                        <div class="mt-6">
                            <?php echo wp_get_attachment_image($decorative_image, 'full', false, [
                                'alt' => esc_attr($decorative_image_alt),
                                'class' => 'object-contain aspect-[14.29] w-[71px]',
                                'role' => 'presentation',
                                'aria-hidden' => 'true'
                            ]); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($description)): ?>
                        <div class="mt-6 text-base tracking-normal leading-7 text-gray-50 font-primary max-md:max-w-full wp_editor">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                    <div class="flex-1 gap-6 self-stretch my-auto text-sm font-semibold tracking-normal leading-loose bg-[#F9FAFB] border-4 border-solid shrink basis-0 border-primary text-primary">
                        <div class="flex gap-2.5 justify-center items-center px-7 w-full bg-gray-50 min-h-11 max-md:px-5">
                            <a
                                href="<?php echo esc_url($button['url']); ?>"
                                class="self-stretch my-auto text-sm tracking-normal leading-6 whitespace-nowrap transition-colors duration-200 text-primary w-fit btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:bg-slate-100"
                                target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                                aria-label="<?php echo esc_attr($button['title']); ?>"
                            >
                                <?php echo esc_html($button['title']); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
