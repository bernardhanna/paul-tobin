<?php
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$description = get_sub_field('description');
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

$section_id = 'content-one-' . wp_rand(1000, 9999);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex gap-12 items-start p-20 w-full max-md:gap-8 max-md:p-12 max-sm:flex-col max-sm:gap-6 max-sm:p-6">

            <!-- Heading and Decorative Bars Section -->
            <div class="flex flex-col flex-1 gap-6 items-center">
                <div class="flex flex-col gap-6 items-start self-stretch">
                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?> class="self-stretch text-3xl font-semibold tracking-normal leading-10 text-slate-950 max-sm:text-3xl max-sm:leading-9">
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <!-- Decorative Color Bars -->
                    <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-slate-300 flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
            </div>

            <!-- Content and Button Section -->
            <div class="flex flex-col flex-1 gap-6 items-start self-stretch">
                <?php if (!empty($description)): ?>
                    <div class="self-stretch text-base tracking-normal leading-7 text-black max-sm:text-sm max-sm:leading-6 wp_editor">
                        <?php echo wp_kses_post($description); ?>
                    </div>
                <?php endif; ?>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                    <a
                        href="<?php echo esc_url($button['url']); ?>"
                        class="box-border flex gap-2.5 justify-center items-center px-6 py-0 h-11 whitespace-nowrap transition-colors duration-300 cursor-pointer bg-slate-900 max-sm:px-5 max-sm:py-0 max-sm:w-full w-fit btn hover:bg-slate-700 focus:bg-slate-700"
                        target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                        aria-label="<?php echo esc_attr($button['title']); ?>"
                    >
                        <span class="text-sm font-semibold tracking-normal leading-6 text-slate-50">
                            <?php echo esc_html($button['title']); ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
