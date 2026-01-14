<?php
// ================== Fetch fields ==================
$heading             = get_sub_field('heading');
$heading_tag         = get_sub_field('heading_tag');
$selected_properties = get_sub_field('selected_properties');

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

// ================== Auto fallback: 3 most recently UPDATED Sold ==================
if (empty($selected_properties)) {
    $selected_properties = get_posts([
        'post_type'      => ['property'],
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'tax_query'      => [
            [
                'taxonomy' => 'property_status',
                'field'    => 'slug',
                'terms'    => ['sold'], // "Sold" term slug
            ],
        ],
        'orderby' => 'modified', // last updated
        'order'   => 'DESC',
        'fields'  => 'ids',
    ]);

    // Optional soft fallback if no "sold" items exist
    if (empty($selected_properties)) {
        $selected_properties = get_posts([
            'post_type'      => ['property'],
            'posts_per_page' => 3,
            'post_status'    => 'publish',
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ]);
    }
}

// Normalize to IDs
$property_ids = array_map(function ($p) {
    return is_object($p) ? (int) $p->ID : (int) $p;
}, (array) $selected_properties);

// Random section id
$section_id  = 'recently-sold-' . uniqid();
$has_heading = !empty($heading);
?>

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
                            class="text-3xl font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <!-- Decorative Bar (neutral; no design fields) -->
                    <div class="flex   justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-slate-300 flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
                    </div>

            <!-- Properties Grid -->
            <?php if (!empty($property_ids)) : ?>
                <div class="grid grid-cols-1 gap-12 w-full md:grid-cols-2 lg:grid-cols-3 max-md:gap-8 max-sm:gap-6" role="region" aria-label="Recently sold properties">
                    <?php foreach ($property_ids as $property_id) :
                        $property_title     = get_the_title($property_id);
                        $property_permalink = get_permalink($property_id);
                        $featured_image     = get_post_thumbnail_id($property_id);
                        $image_alt          = $featured_image ? (get_post_meta($featured_image, '_wp_attachment_image_alt', true) ?: $property_title) : $property_title;

                        $property_types = get_the_terms($property_id, 'property_type');
                        $property_type  = (!empty($property_types) && !is_wp_error($property_types)) ? $property_types[0]->name : 'Property';
                    ?>
                        <a href="<?php echo esc_url($property_permalink); ?>" class="flex flex-col items-start h-[318px] max-md:h-[280px] max-sm:h-[250px]">
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

                                <div class="box-border flex flex-col justify-end items-start p-8 w-full flex-[1_0_0] max-sm:p-6 relative z-10">
                                    <div class="flex flex-col items-start px-8 py-4 bg-gray-200 max-md:px-6 max-md:py-3 max-sm:px-5 max-sm:py-3">
                                        <span class="font-secondary font-semibold text-xl sm:text-[32px] leading-tight sm:leading-[40px] tracking-[-0.16px] text-slate-900">
                                            <div
                                                
                                                class="transition-colors duration-200"
                                                aria-describedby="property-type-<?php echo esc_attr($property_id); ?>"
                                            >
                                                <?php echo esc_html($property_title); ?>
                                            </div>
                                        </span>
                                        <p id="property-type-<?php echo esc_attr($property_id); ?>" class="text-base tracking-normal leading-7 text-gray-700 max-md:text-sm max-md:leading-6 max-sm:text-sm max-sm:leading-6">
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
