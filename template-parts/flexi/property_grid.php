<?php
// Fields
$section_heading      = get_sub_field('section_heading');
$section_heading_tag  = get_sub_field('section_heading_tag') ?: 'h2';
$selection_type       = get_sub_field('selection_type') ?: 'filter';
$property_statuses    = get_sub_field('property_statuses');    // taxonomy term IDs
$selected_properties  = get_sub_field('selected_properties');  // relationship objects
$background_color     = get_sub_field('background_color') ?: '#f9fafb';

// Padding classes
$padding_classes = ['pt-5','pb-5'];
if (have_rows('padding_settings')) {
    $padding_classes = [];
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        if ($screen_size !== '' && $screen_size !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// Query properties (filter by property_status or manual select)
$properties = [];
if ($selection_type === 'filter') {
    $tax_query = [];
    if (!empty($property_statuses) && is_array($property_statuses)) {
        $tax_query[] = [
            'taxonomy' => 'property_status',
            'field'    => 'term_id',
            'terms'    => array_map('intval', $property_statuses),
        ];
    }
    $args = [
        'post_type'      => 'property',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ];
    if ($tax_query) $args['tax_query'] = $tax_query;

    $q = new WP_Query($args);
    if ($q->have_posts()) $properties = $q->posts;
    wp_reset_postdata();
} elseif ($selection_type === 'manual' && !empty($selected_properties)) {
    $properties = $selected_properties;
}

// Normalize to IDs for rendering
$property_ids = array_map(function ($p) {
    return is_object($p) ? (int) $p->ID : (int) $p;
}, $properties);

// Unique section ID
$section_id = 'property-grid-' . uniqid('', true);

// Allowed heading tags
$allowed_headings = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($section_heading_tag, $allowed_headings, true)) {
    $section_heading_tag = 'h2';
}

/**
 * Span classes per-tile (index-based).
 *
 * Breakpoints (custom):
 * md: 768px, lg: 1084px
 *
 * < md:                1 col – all items full width
 * md–(lg-1):           rows repeat: 50/50, 100/100, 100/100, 50/50, 100/100, 100/100, ...
 * ≥ lg:                rows repeat: 50/50, 40/60, 60/40, 50/50, ...
 */
function property_grid_span_classes($index) {
    $pair_index = intdiv($index, 2);  // which two-up row we're on (0-based)
    $pos        = $index % 2;         // 0 = left, 1 = right

    // Tablet pattern (md): 6-row cycle
    switch ($pair_index % 6) {
        case 0: // 50/50
            $md_span = 'md:col-span-1';
            break;
        case 1: // 100/100
            $md_span = 'md:col-span-2';
            break;
        case 2: // 100/100
            $md_span = 'md:col-span-2';
            break;
        case 3: // 50/50
            $md_span = 'md:col-span-1';
            break;
        case 4: // 100/100
            $md_span = 'md:col-span-2';
            break;
        case 5: // 100/100
        default:
            $md_span = 'md:col-span-2';
            break;
    }

    // Desktop pattern (lg): 4-row cycle
    switch ($pair_index % 4) {
        case 0: // 50/50
            $lg_span = 'lg:col-span-5';
            break;
        case 1: // 40/60
            $lg_span = ($pos === 0) ? 'lg:col-span-4' : 'lg:col-span-6';
            break;
        case 2: // 60/40
            $lg_span = ($pos === 0) ? 'lg:col-span-6' : 'lg:col-span-4';
            break;
        case 3: // 50/50
        default:
            $lg_span = 'lg:col-span-5';
            break;
    }

    // Base: 1 col (< md). At md: 2 cols. At lg: 10 cols.
    return "col-span-1 {$md_span} {$lg_span}";
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center mx-auto w-full max-w-[70rem] max-lg:px-5 lg:py-[4rem] py-[2.5rem]">

        <?php if (!empty($section_heading)): ?>
            <header class="py-12 w-full md:py-16 lg:py-20">
                <<?php echo esc_attr($section_heading_tag); ?>
                    id="<?php echo esc_attr($section_id); ?>-heading"
                    class="text-3xl font-semibold tracking-normal leading-tight text-center md:text-4xl text-slate-950"
                >
                    <?php echo esc_html($section_heading); ?>
                </<?php echo esc_attr($section_heading_tag); ?>>
            </header>
        <?php endif; ?>

        <?php if (!empty($property_ids)): ?>
            <div class="w-full">
                <!-- Base: 1 col; md: 2 cols (tablet cycle handled via md:col-span-*); lg: 10 cols (desktop cycle via lg:col-span-*) -->
                <div class="grid grid-cols-1 gap-6 px-0 md:grid-cols-2 lg:grid-cols-10 lg:gap-12">
                    <?php foreach ($property_ids as $i => $pid): ?>
                        <?php
                        $title    = get_the_title($pid);
                        $link     = get_permalink($pid);
                        $thumb_id = get_post_thumbnail_id($pid);
                        $img_url  = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
                        $img_alt  = $thumb_id ? (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $title) : 'Property image';
                        if (!$img_url) {
                            $img_url = 'https://via.placeholder.com/1200x800/e5e7eb/6b7280?text=Property';
                        }
                        $type_terms = get_the_terms($pid, 'property_type');
                        $type_label = (!is_wp_error($type_terms) && !empty($type_terms)) ? $type_terms[0]->name : 'Residential';

                        $tile_classes = property_grid_span_classes($i);
                        ?>
                        <div class="<?php echo esc_attr($tile_classes); ?>">
                            <a
                                href="<?php echo esc_url($link); ?>"
                                class="block group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900"
                                aria-label="<?php echo esc_attr('View ' . $title); ?>"
                            >
                                <div class="relative flex flex-col justify-end items-start overflow-hidden max-md:h-[18.75rem] h-[31.25rem]">
                                    <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('<?php echo esc_url($img_url); ?>');"></div>

                                    <div class="relative p-3 px-6 m-4 bg-white sm:m-8 sm:p-4 sm:px-8">
                                        <h3 class="font-secondary font-semibold text-xl sm:text-[32px] leading-tight sm:leading-[40px] tracking-[-0.16px] text-slate-900">
                                            <?php echo esc_html($title); ?>
                                        </h3>
                                        <p class="font-primary text-sm sm:text-base leading-relaxed sm:leading-[26px] text-slate-600">
                                            <?php echo esc_html($type_label); ?>
                                        </p>
                                    </div>
                                </div>
                                <span class="sr-only">Image alt: <?php echo esc_html($img_alt); ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="py-12 w-full text-center">
                <p class="text-lg text-slate-600">No properties found. Adjust your selection or add properties.</p>
            </div>
        <?php endif; ?>

    </div>
</section>
