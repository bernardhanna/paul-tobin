<?php
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$content = get_sub_field('content');
$image = get_sub_field('image');
$image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'About us image';
$show_decorative_bars = get_sub_field('show_decorative_bars');
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

$section_id = 'content-two-' . wp_rand(1000, 9999);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex gap-12 items-start p-20 w-full max-md:flex-col max-md:gap-8 max-md:p-12 max-sm:flex-col max-sm:gap-6 max-sm:p-6">

            <?php if ($image): ?>
                <div class="flex-1 w-full max-md:flex-none max-md:w-full max-sm:flex-none max-sm:w-full">
                    <?php echo wp_get_attachment_image($image, 'full', false, [
                        'alt' => esc_attr($image_alt),
                        'class' => 'object-cover w-full h-[349px] max-md:h-[300px] max-sm:h-[250px] rounded-lg',
                        'loading' => 'lazy'
                    ]); ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col flex-1 gap-6 items-start max-md:flex-none max-md:w-full max-sm:flex-none max-sm:w-full">

                <?php if (!empty($heading)): ?>
                    <header class="flex flex-col gap-6 items-center w-full">
                        <div class="flex flex-col gap-6 items-start w-full">
                            <<?php echo esc_attr($heading_tag); ?>
                                id="<?php echo esc_attr($section_id); ?>-heading"
                                class="w-full text-3xl font-semibold tracking-normal leading-10 text-center text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8 max-sm:text-left"
                            >
                                <?php echo esc_html($heading); ?>
                            </<?php echo esc_attr($heading_tag); ?>>

                            <?php if ($show_decorative_bars): ?>
                                <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                    <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                    <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                    <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                    <div class="bg-lime-600 flex-1 h-[5px]"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </header>
                <?php endif; ?>

                <?php if (!empty($content)): ?>
                    <div class="w-full text-base tracking-normal leading-7 text-black max-md:text-base max-md:leading-6 max-sm:text-sm max-sm:leading-6 wp_editor">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
