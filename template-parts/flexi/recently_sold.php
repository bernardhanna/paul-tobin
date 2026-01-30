<?php
// ================== Fetch fields ==================
$heading                  = get_sub_field('heading');
$heading_tag              = get_sub_field('heading_tag');
$selected_properties      = get_sub_field('selected_properties'); // relationship (object)
$filter_by                = get_sub_field('filter_by'); // 'none' | 'property_status' | 'property_type'
$property_status_terms    = get_sub_field('property_status_terms'); // term objects/ids
$property_type_terms      = get_sub_field('property_type_terms');   // term objects/ids
$auto_related_on_single   = (bool) get_sub_field('auto_related_on_single');
$limit                    = (int) (get_sub_field('limit') ?: 3);
$order_by                 = get_sub_field('order_by') ?: 'modified';
$order                    = get_sub_field('order') ?: 'DESC';

// Whitelist heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// ================== Build padding classes ==================
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

// Helper: normalize term values to term_ids
$term_ids = static function ($terms) {
    $ids = [];
    if (is_array($terms)) {
        foreach ($terms as $t) {
            if (is_object($t) && isset($t->term_id)) {
                $ids[] = (int) $t->term_id;
            } elseif (is_numeric($t)) {
                $ids[] = (int) $t;
            }
        }
    }
    return array_filter($ids);
};

// ================== Resolve Property IDs ==================
// 1) Manual selection (highest priority)
$property_ids = [];
if (!empty($selected_properties) && is_array($selected_properties)) {
    foreach ($selected_properties as $p) {
        $property_ids[] = is_object($p) ? (int) $p->ID : (int) $p;
    }
    $property_ids = array_filter($property_ids);
}

// 2) If no manual selection, build query by taxonomy filters / related mode / default "Sold"
if (empty($property_ids)) {
    $tax_query = [];

    // Related mode on single property: use current post's property_status
    if ($auto_related_on_single && is_singular('property')) {
        $current_id = get_the_ID();
        $current_status = get_the_terms($current_id, 'property_status');
        $current_status_ids = [];
        if (!empty($current_status) && !is_wp_error($current_status)) {
            foreach ($current_status as $ct) {
                $current_status_ids[] = (int) $ct->term_id;
            }
        }
        if (!empty($current_status_ids)) {
            $tax_query[] = [
                'taxonomy' => 'property_status',
                'field'    => 'term_id',
                'terms'    => $current_status_ids,
            ];
        }
    } else {
        // Admin-chosen taxonomy filters
        if ($filter_by === 'property_status') {
            $ids = $term_ids($property_status_terms);
            if (!empty($ids)) {
                $tax_query[] = [
                    'taxonomy' => 'property_status',
                    'field'    => 'term_id',
                    'terms'    => $ids,
                ];
            }
        } elseif ($filter_by === 'property_type') {
            $ids = $term_ids($property_type_terms);
            if (!empty($ids)) {
                $tax_query[] = [
                    'taxonomy' => 'property_type',
                    'field'    => 'term_id',
                    'terms'    => $ids,
                ];
            }
        }
    }

    // Build args
    $args = [
        'post_type'      => ['property'],
        'posts_per_page' => $limit ?: 3,
        'post_status'    => 'publish',
        'orderby'        => $order_by,
        'order'          => $order,
        'fields'         => 'ids',
    ];

    // If no filters set and not in single related mode, prefer Sold by default
    if (empty($tax_query) && !is_singular('property')) {
        $args['tax_query'] = [[
            'taxonomy' => 'property_status',
            'field'    => 'slug',
            'terms'    => ['sold'],
        ]];
    } elseif (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    // Exclude current property when on single
    if (is_singular('property')) {
        $args['post__not_in'] = [get_the_ID()];
    }

    $property_ids = get_posts($args);

    // Absolute fallback: most recently modified properties
    if (empty($property_ids)) {
        $property_ids = get_posts([
            'post_type'      => ['property'],
            'posts_per_page' => $limit ?: 3,
            'post_status'    => 'publish',
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ]);
    }
}

// 3) Normalize to IDs (in case anything slipped through)
$property_ids = array_map(function ($p) {
    return is_object($p) ? (int) $p->ID : (int) $p;
}, (array) $property_ids);

// Random section id
$section_id  = 'recently-sold-' . uniqid();
$has_heading = !empty($heading);
?>

<!-- ========== FROM HERE DOWN, YOUR FRONTEND IS UNCHANGED ========== -->

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    <?php echo $has_heading ? 'aria-labelledby="' . esc_attr($section_id) . '-heading"' : ''; ?>
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-10 lg:py-20 pt-5 pb-5 max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <div class="box-border flex flex-col gap-12 items-start py-0 w-full max-md:gap-8 max-md:py-0 max-sm:gap-6 max-sm:py-0">

            <!-- Header -->
            <div class="flex flex-col gap-6 items-center w-full max-sm:gap-4">
                <div class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
                    <?php if ($has_heading) : ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <div class="flex justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
            </div>

            <!-- Properties Grid -->
            <?php if (!empty($property_ids)) : ?>
                <!-- Below lg: 2 columns; pattern 50/50 then 100% (spans 2) -->
                <!-- At lg: 3 equal columns -->
                <div class="grid grid-cols-1 gap-12 w-full sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 max-md:gap-8 max-sm:gap-6" role="region" aria-label="Recently sold properties">
                    <?php foreach ($property_ids as $idx => $property_id) :
                        $property_title     = get_the_title($property_id);
                        $property_permalink = get_permalink($property_id);
                        $featured_image     = get_post_thumbnail_id($property_id);
                        $image_alt          = $featured_image ? (get_post_meta($featured_image, '_wp_attachment_image_alt', true) ?: $property_title) : $property_title;

                        $property_types = get_the_terms($property_id, 'property_type');
                        $property_type  = (!empty($property_types) && !is_wp_error($property_types)) ? $property_types[0]->name : 'Property';

                        // Every 3rd card (0-based idx 2,5,8,...) spans 2 cols on sm/md; reset to 1 at lg
                        $span_classes = ($idx % 3 === 2)
                            ? 'sm:col-span-2 md:col-span-2 lg:col-span-1'
                            : 'sm:col-span-1 md:col-span-1 lg:col-span-1';
                    ?>
                        <a href="<?php echo esc_url($property_permalink); ?>"
                           class="group flex flex-col items-start h-[318px] max-md:h-[280px] max-sm:h-[250px] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 <?php echo esc_attr($span_classes); ?>">
                            <div class="flex overflow-hidden flex-col justify-center items-center w-full flex-[1_0_0] relative">
                                <?php if ($featured_image) : ?>
                                    <div class="absolute inset-0 w-full h-full">
                                        <?php echo wp_get_attachment_image($featured_image, 'large', false, [
                                            'alt'     => esc_attr($image_alt),
                                            'class'   => 'w-full h-full object-cover',
                                            'loading' => 'lazy',
                                        ]); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Gradient overlay on hover/focus -->
                                <div
                                  class="absolute inset-0 opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 group-focus:opacity-100"
                                  style="background: linear-gradient(0deg, rgba(0, 152, 216, 0.25) 0%, rgba(0, 152, 216, 0.25) 100%);"
                                  aria-hidden="true"
                                ></div>

                                <div class="box-border flex flex-col justify-end items-start p-8 w-full flex-[1_0_0] max-sm:p-6 relative z-10">
                                    <div class="flex flex-col items-start px-8 py-4 bg-gray-200 max-md:px-6 max-md:py-3 max-sm:px-5 max-sm:py-3">
                                        <span class="font-secondary font-semibold text-[2.125rem] leading-[2.5rem] tracking-[-0.01rem] text-[#0A1119]">
                                            <div class="transition-colors duration-200" aria-describedby="property-type-<?php echo esc_attr($property_id); ?>">
                                                <?php echo esc_html($property_title); ?>
                                            </div>
                                        </span>
                                        <p id="property-type-<?php echo esc_attr($property_id); ?>" class="font-normal text-[1rem] leading-[1.625rem] text-[#434B53]">
                                            <?php echo esc_html($property_type); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="py-12 w-full text-center">
                    <p class="text-lg text-gray-600">No sold properties found. Please add some properties with “Sold” status or select specific properties in the admin panel.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
