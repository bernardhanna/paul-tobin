<?php
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$decorative_image = get_sub_field('decorative_image');
$decorative_image_alt = get_post_meta($decorative_image, '_wp_attachment_image_alt', true) ?: 'Decorative underline';
$solutions = get_sub_field('solutions');
$background_color = get_sub_field('background_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size   = get_sub_field('screen_size');
        $padding_top   = get_sub_field('padding_top');
        $padding_bottom= get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

$section_id = 'solutions_' . wp_rand(1000, 9999);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>_heading"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="gap-12 py-12 my-auto w-full lg:py-20 max-md:px-5 max-md:max-w-full">
            <?php if (!empty($heading)): ?>
                <header class="w-full text-3xl font-semibold tracking-normal leading-none text-center text-primary max-md:max-w-full">
                    <div class="flex flex-col gap-6 items-center w-full max-md:max-w-full">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>_heading"
                            class="text-[#0A1119] text-left font-secondary text-[32px] font-semibold leading-[40px] tracking-[-0.16px]"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                            <div class="flex gap-0.5 justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                    </div>
                </header>
            <?php endif; ?>

            <?php if ($solutions && is_array($solutions)): ?>
                <div class="grid grid-cols-1 gap-8 items-stretch mt-12 w-full md:grid-cols-3 max-md:mt-10 max-md:max-w-full" role="list">
                    <?php foreach ($solutions as $index => $solution):
                        $action_word    = $solution['action_word'] ?? '';
                        $description    = $solution['description'] ?? '';
                        $button_link    = $solution['button_link'] ?? '';
                        $underline_color= $solution['underline_color'] ?? '#0ea5e9';
                        $card_id        = $section_id . '_card_' . ($index + 1);
                    ?>
                    <article
                        class="flex flex-col p-8 h-full bg-[#F9FAFB] max-md:px-5"
                        role="listitem"
                        aria-labelledby="<?php echo esc_attr($card_id); ?>_heading"
                    >
                        <div class="flex flex-col justify-center w-full text-center">
                            <div class="flex flex-col items-center w-full text-2xl font-semibold tracking-normal leading-none text-primary">
                                <p class="tracking-normal leading-7 text-primary font-secondary">
                                    I want to
                                </p>

                                <div class="flex flex-col justify-center items-center text-7xl font-bold leading-none whitespace-nowrap max-md:text-4xl"
                                    >
                                    <span
                                        id="<?php echo esc_attr($card_id); ?>_heading"
                                        class="self-stretch my-auto text-7xl font-primary tracking-normal leading-[92px] text-primary z-10 max-md:text-4xl"
                                    >
                                        <?php echo esc_html($action_word); ?>
                                    </span>
                                    <div style="background-color: <?php echo esc_attr($underline_color); ?>;" class="w-[110%] h-2 relative -top-[15px] mx-auto z-0"></div>
                                </div>

                                <p class="tracking-normal leading-7 text-primary font-secondary">
                                    my property
                                </p>
                            </div>

                            <?php if (!empty($description)): ?>
                                <div class="mt-4 text-base tracking-normal leading-7 text-black font-primary wp_editor">
                                    <?php echo wp_kses_post($description); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($button_link && is_array($button_link) && isset($button_link['url'], $button_link['title'])): ?>
                            <div class="flex justify-center mt-4">
                                <a
                                    href="<?php echo esc_url($button_link['url']); ?>"
                                    class="flex justify-center items-center w-12 h-12 text-white border border-solid transition-colors duration-300 bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:bg-transparent border-primary hover:text-primary hover:border-primary"
                                    target="<?php echo esc_attr($button_link['target'] ?? '_self'); ?>"
                                    aria-label="<?php echo esc_attr($button_link['title'] . ' - ' . $action_word . ' property'); ?>"
                                >
                                    <span class="text-2xl fa-solid fa-plus" aria-hidden="true"></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
