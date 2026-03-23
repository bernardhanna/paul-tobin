<?php
/**
 * Content – Fixed design, dynamic content via ACF.
 * - Uses get_sub_field only
 * - Keeps exact provided styles (no design options)
 * - Includes required Flexi wrapper + padding repeater
 * - CTA uses ACF Link array (<a>, not <button>)
 */

$section_id   = 'content-one-' . wp_rand(1000, 9999);

$heading      = get_sub_field('heading');
$heading_tag  = get_sub_field('heading_tag'); // h1..h6, p, span
$description  = get_sub_field('description'); // WYSIWYG
$left_description = get_sub_field('left_description'); // Optional left-side WYSIWYG
if (is_string($left_description) && trim(wp_strip_all_tags($left_description)) === '') {
    // Fallback to raw value in case formatted output is empty.
    $left_description = get_sub_field('left_description', false, false);
}
$has_left_description = is_string($left_description) && trim(wp_strip_all_tags($left_description)) !== '';
$button_link  = get_sub_field('button_link'); // ACF link array

// Padding controls (Repeater → arbitrary-value classes)
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');      // xxs,xs,mob,sm,md,lg,xl,xxl,ultrawide
        $padding_top    = get_sub_field('padding_top');      // rem
        $padding_bottom = get_sub_field('padding_bottom');   // rem

        if ($screen_size !== '' && $padding_top !== '' && $padding_bottom !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

if (empty($heading_tag)) {
    $heading_tag = 'h2';
}

// On single Property pages, always provide a CTA to book consultation and prefill form context.
$effective_button_link = $button_link;
if (
    is_singular('property')
    && (
        empty($button_link)
        || !is_array($button_link)
        || empty($button_link['url'])
        || empty($button_link['title'])
    )
) {
    $property_id = get_queried_object_id();
    $property_title = $property_id ? get_the_title($property_id) : '';
    $property_address = $property_id ? ((string) get_post_meta($property_id, 'daft_address', true)) : '';
    if ($property_address === '') {
        $property_address = $property_title;
    }

    $property_type_terms = $property_id ? get_the_terms($property_id, 'property_type') : [];
    $property_type_name = (!empty($property_type_terms) && !is_wp_error($property_type_terms)) ? (string) $property_type_terms[0]->name : '';
    $bedrooms = $property_id ? (string) get_post_meta($property_id, 'bedrooms', true) : '';
    $bathrooms = $property_id ? (string) get_post_meta($property_id, 'bathrooms', true) : '';
    $property_url = $property_id ? get_permalink($property_id) : '';

    $consult_url = add_query_arg([
        'from_property' => 1,
        'query_type' => 'request_a_call',
        'query_type_label' => 'Request a call',
        'property_id' => $property_id ?: '',
        'property_url' => $property_url,
        'property_address' => $property_address,
        'property_type' => $property_type_name,
        'bedrooms' => $bedrooms,
        'bathrooms' => $bathrooms,
    ], home_url('/book-a-consultation/'));

    $effective_button_link = [
        'url' => $consult_url,
        'title' => 'Request a call',
        'target' => '_self',
    ];
}
?>

<section id="<?php echo esc_attr($section_id); ?>" class="relative flex overflow-hidden bg-[#ededed]">
    <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="mx-auto w-full max-w-[1200px] px-5 xl:px-0">
            <div class="grid grid-cols-1 gap-6 py-[2.5rem] md:grid-cols-2 md:gap-[3rem] lg:py-[5rem]">
                <!-- Left: Heading + decorative bar -->
                <div class="w-full">
                    <?php if (!empty($heading)) : ?>
                        <<?php echo esc_attr($heading_tag); ?> class="max-w-[33.5rem] text-left text-[2.125rem] font-[600] leading-[2.5rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <div class="mt-6 flex h-[0.3125rem] w-[4.4375rem]" aria-hidden="true" role="presentation">
                        <span class="h-full flex-1 bg-[#ef7b10]"></span>
                        <span class="h-full flex-1 bg-[#0098d8]"></span>
                        <span class="h-full flex-1 bg-[#b6c0cb]"></span>
                        <span class="h-full flex-1 bg-[#74af27]"></span>
                    </div>

                    <?php if ($has_left_description) : ?>
                        <div class="pt-4 w-full md:pt-5">
                            <div class="max-w-[33.5rem] text-left text-[1rem] !font-[300] leading-[1.625rem] text-[#000000] font-primary wp_editor">
                                <?php echo wp_kses_post($left_description); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Text + CTA -->
                <div class="w-full">
                    <?php if (!empty($description)) : ?>
                        <div class="max-w-[33.5rem] text-left text-[1rem] !font-[300] leading-[1.625rem] text-[#000000] font-primary wp_editor">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($effective_button_link) && is_array($effective_button_link) && !empty($effective_button_link['url']) && !empty($effective_button_link['title'])) : ?>
                        <a
                            href="<?php echo esc_url($effective_button_link['url']); ?>"
                            class="btn mt-6 inline-flex w-fit items-center justify-center gap-2 bg-[#0f172a] px-8 py-3.5 text-[0.875rem] font-[600] leading-[1.375rem] text-white transition-opacity duration-200 hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 hover:bg-[#40bff5] hover:text-black lg:h-[2.75rem] max-w-max"
                            target="<?php echo esc_attr(!empty($effective_button_link['target']) ? $effective_button_link['target'] : '_self'); ?>"
                            aria-label="<?php echo esc_attr($effective_button_link['title']); ?>"
                        >
                            <span class="font-primary">
                                <?php echo esc_html($effective_button_link['title']); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
