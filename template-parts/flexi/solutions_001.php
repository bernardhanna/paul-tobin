<?php
$heading            = get_sub_field('heading');
$heading_tag        = get_sub_field('heading_tag');
$decorative_image   = get_sub_field('decorative_image');
$decorative_image_alt = $decorative_image ? (get_post_meta($decorative_image, '_wp_attachment_image_alt', true) ?: 'Decorative underline') : '';
$solutions          = get_sub_field('solutions');
$background_color   = get_sub_field('background_color');

// Padding classes on inner container
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Enforce max 3 items in case legacy content had more
if (is_array($solutions)) {
    $solutions = array_slice($solutions, 0, 3);
}

// Fixed underline/band colors by index
$fixed_colors = ['#0ea5e9', '#74af27', '#ef7b10'];

$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$section_id = 'solutions_' . wp_rand(1000, 9999);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>_heading"
>
    <!-- Per-card background colors on hover/focus (only the card, not the button) -->
    <style>
      /* Card 1 */
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(1):hover,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(1):focus,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(1):focus-visible {
        background-color: #D9F1FC !important;
      }
      /* Card 2 */
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(2):hover,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(2):focus,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(2):focus-visible {
        background-color: #E0F4C5 !important;
      }
      /* Card 3 */
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(3):hover,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(3):focus,
      #<?php echo esc_attr($section_id); ?> [role="list"] > *:nth-child(3):focus-visible {
        background-color: #FFE5CC !important;
      }
    </style>

    <div class="flex flex-col items-center w-full mx-auto max-w-container max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="gap-12 py-12 my-auto w-full lg:py-20 max-md:max-w-full">
            <?php if (!empty($heading)): ?>
                <header class="w-full text-[2.125rem] font-semibold tracking-normal leading-none text-center text-primary max-md:max-w-full">
                    <div class="flex flex-col gap-6 items-center w-full max-md:max-w-full">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>_heading"
                            class="text-[2.125rem] font-semibold tracking-normal leading-10 text-center font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if ($decorative_image): ?>
                            <figure class="w-[71px] max-sm:w-[60px]" aria-hidden="true">
                                <?php echo wp_get_attachment_image($decorative_image, 'medium', false, [
                                    'alt'   => esc_attr($decorative_image_alt),
                                    'class' => 'w-full h-auto',
                                    'loading' => 'lazy',
                                ]); ?>
                            </figure>
                        <?php else: ?>
                            <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </header>
            <?php endif; ?>

            <?php if ($solutions && is_array($solutions)): ?>
                <div class="grid grid-cols-1 gap-8 items-stretch mt-12 w-full md:grid-cols-3 max-md:mt-10 max-md:max-w-full" role="list">
                    <?php foreach ($solutions as $index => $solution):
                        $action_word     = $solution['action_word'] ?? '';
                        $description     = $solution['description'] ?? '';
                        $button_link     = $solution['button_link'] ?? '';
                        $underline_color = $fixed_colors[$index] ?? $fixed_colors[0];
                        $card_id         = $section_id . '_card_' . ($index + 1);

                        // Make entire card a link if ACF link exists
                        $is_link_card  = (is_array($button_link) && !empty($button_link['url']) && !empty($button_link['title']));
                        $wrapper_tag   = $is_link_card ? 'a' : 'article';
                        $wrapper_attrs = '';
                        if ($is_link_card) {
                            $wrapper_attrs .= ' href="' . esc_url($button_link['url']) . '"';
                            $wrapper_attrs .= ' target="' . esc_attr($button_link['target'] ?? '_self') . '"';
                            $wrapper_attrs .= ' aria-label="' . esc_attr(trim(($button_link['title'] ?? '') . ' - ' . $action_word . ' property')) . '"';
                            $wrapper_attrs .= ' title="' . esc_attr($button_link['title']) . '"';
                        }
                    ?>
                    <<?php echo $wrapper_tag; ?>
                        class="group flex flex-col p-8 h-full bg-[#EDEDED] transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary max-md:px-5"
                        role="listitem"
                        aria-labelledby="<?php echo esc_attr($card_id); ?>_heading"
                        <?php echo $wrapper_attrs; ?>
                    >
                        <div class="flex flex-col justify-center w-full text-center">
                            <div class="flex flex-col items-center w-full text-2xl font-semibold tracking-normal leading-none text-primary">
                                <p class="tracking-normal leading-7 text-primary font-secondary">
                                    I want to
                                </p>

                                <div class="flex flex-col justify-center items-center text-7xl font-bold leading-none whitespace-nowrap max-md:text-4xl">
                                    <span
                                        id="<?php echo esc_attr($card_id); ?>_heading"
                                        class="self-stretch my-auto text-7xl font-primary tracking-normal leading-[92px] text-primary z-10 max-md:text-4xl"
                                    >
                                        <?php echo esc_html($action_word); ?>
                                    </span>
                                    <div style="background-color: <?php echo esc_attr($underline_color); ?>;" class="w-[110%] h-2 relative md:-top-[15px] mx-auto z-0"></div>
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

                        <!-- Visual “button” (span) reacts to its own hover AND card hover; blue bg + black icon/text/border -->
                        <div class="flex justify-center mt-4">
                            <span
                                class="flex justify-center items-center w-12 h-12 text-white border border-solid transition-colors duration-300 bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary border-primary
                                       hover:bg-[#40BFF5] hover:text-black hover:border-black
                                       group-hover:bg-[#40BFF5] group-hover:text-black group-hover:border-black"
                                aria-hidden="true"
                            >
                                <span class="text-2xl transition-colors duration-300 fa-solid fa-plus group-hover:text-black hover:text-black" aria-hidden="true"></span>
                            </span>
                        </div>

                    </<?php echo $wrapper_tag; ?>>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
