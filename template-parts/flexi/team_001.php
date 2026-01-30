<?php
// ============ Fetch fields ============
$heading               = get_sub_field('heading');
$heading_tag           = get_sub_field('heading_tag') ?: 'h2';
$background_color      = get_sub_field('background_color');
$team_selection_type   = get_sub_field('team_selection_type'); // 'all' | 'specific'
$selected_team_members = (array) get_sub_field('selected_team_members');
$posts_per_page        = (int) (get_sub_field('posts_per_page') ?: 6);

// Whitelist heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// ============ Query ============
$team_args = [
    'post_type'      => 'team',
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
];
if ($team_selection_type === 'specific' && !empty($selected_team_members)) {
    $ids = array_map(function ($m) { return is_object($m) ? (int) $m->ID : (int) $m; }, $selected_team_members);
    $ids = array_filter($ids);
    if (!empty($ids)) {
        $team_args['post__in'] = $ids;
        $team_args['orderby']  = 'post__in';
    }
}
$team_query = new WP_Query($team_args);

// ============ Padding classes ============
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

// Unique section id
$section_id  = 'team-section-' . uniqid();
$has_heading = !empty($heading);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="<?php echo $background_color ? 'background-color:' . esc_attr($background_color) . ';' : ''; ?>"
    <?php echo $has_heading ? 'aria-labelledby="' . esc_attr($section_id) . '-heading"' : ''; ?>
>
    <div class="flex flex-col items-center py-10 mx-auto w-full max-w-container lg:py-20 max-lg:px-5">

        <div class="box-border flex flex-col gap-12 items-start w-full max-md:gap-8 max-sm:gap-6">

            <!-- Header -->
            <div class="flex flex-col gap-6 items-center w-full max-sm:gap-4">
                <div class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
                    <?php if ($has_heading): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9  max-sm:leading-8"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <!-- Decorative Color Bars -->
                    <div class="flex justify-between items-start w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                        <div class="bg-orange-500 flex-1 h-[5px]"></div>
                        <div class="bg-sky-500 flex-1 h-[5px]"></div>
                        <div class="bg-slate-300 flex-1 h-[5px]"></div>
                        <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
            </div>

            <!-- Team Grid — 1 col (mobile) -> 3 cols (lg+) -->
            <?php if ($team_query->have_posts()): ?>
                <div class="grid grid-cols-1 gap-12 w-full lg:grid-cols-3" role="region" aria-label="Team members">
                    <?php
                    $idx = -1;
                    while ($team_query->have_posts()):
                        $team_query->the_post();
                        $idx++;

                        $member_id   = get_the_ID();
                        $member_name = get_the_title();
                        $member_link = get_permalink();
                        $thumb_id    = get_post_thumbnail_id($member_id);
                        $image_alt   = $thumb_id ? (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $member_name) : $member_name;

                        // role from taxonomy `team_role` (first term), fallback to excerpt
                        $roles       = get_the_terms($member_id, 'team_role');
                        $member_role = (!empty($roles) && !is_wp_error($roles)) ? $roles[0]->name : (get_the_excerpt() ?: 'Team Member');
                    ?>
                        <artice
                           
                            class="group flex flex-col items-start h-[424px] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            aria-labelledby="member-<?php echo esc_attr($member_id); ?>-name"
                        >
                            <div class="flex overflow-hidden flex-col justify-end items-center w-full flex-[1_0_0] relative">
                                <?php if ($thumb_id): ?>
                                    <div class="absolute inset-0 w-full h-full">
                                        <?php echo wp_get_attachment_image($thumb_id, 'large', false, [
                                            'alt'     => esc_attr($image_alt),
                                            'class'   => 'w-full h-full object-cover', // fills 424px container
                                            'loading' => ($idx < 3 ? 'eager' : 'lazy'),
                                        ]); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="absolute inset-0 w-full h-full bg-gray-200" aria-hidden="true"></div>
                                <?php endif; ?>

                                <!-- Gradient overlay on hover/focus (same as properties) -->
                                <div
                                    class="absolute inset-0 opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 group-focus:opacity-100"
                                    style="background: linear-gradient(0deg, rgba(0, 152, 216, 0.25) 0%, rgba(0, 152, 216, 0.25) 100%);"
                                    aria-hidden="true"
                                ></div>

                                <div class="box-border flex relative z-10 flex-col justify-end items-start p-8 w-full">
                                    <div class="flex flex-col items-start px-8 py-4 bg-gray-200">
                                        <h3
                                            id="member-<?php echo esc_attr($member_id); ?>-name"
                                            class="font-secondary font-semibold text-[2.125rem]   leading-[2.5rem] tracking-[-0.01rem] text-[#0A1119]"
                                        >
                                            <span class="transition-colors duration-200"><?php echo esc_html($member_name); ?></span>
                                        </h3>
                                        <p class="font-normal text-[1rem] leading-[1.625rem] text-[#434B53]">
                                            <?php echo esc_html($member_role); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </artice>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <div class="py-12 w-full text-center">
                    <p class="text-lg text-gray-600">No team members found.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
