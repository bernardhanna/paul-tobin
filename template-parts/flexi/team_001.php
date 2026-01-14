<?php
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$background_color = get_sub_field('background_color');
$team_selection_type = get_sub_field('team_selection_type');
$selected_team_members = get_sub_field('selected_team_members');
$posts_per_page = get_sub_field('posts_per_page') ?: 6;

// Build team query
$team_args = [
    'post_type' => 'team',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'orderby' => 'menu_order',
    'order' => 'ASC'
];

if ($team_selection_type === 'specific' && !empty($selected_team_members)) {
    $team_args['post__in'] = $selected_team_members;
    $team_args['orderby'] = 'post__in';
}

$team_query = new WP_Query($team_args);

// Padding classes
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

// Generate unique section ID
$section_id = 'team-section-' . uniqid();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex flex-col gap-12 items-start px-20 py-0 w-full max-md:gap-8 max-md:px-12 max-md:py-0 max-sm:gap-6 max-sm:px-6 max-sm:py-0">

            <!-- Section Header -->
            <header class="box-border flex flex-col gap-6 items-center w-full">
                <div class="box-border flex flex-col gap-6 items-start w-full max-sm:gap-4">
                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="box-border w-full text-3xl font-semibold tracking-normal leading-10 text-center text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                        >
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
            </header>

            <?php if ($team_query->have_posts()): ?>
                <!-- Team Grid -->
                <main class="w-full">
                    <div class="grid grid-cols-1 gap-12 w-full md:grid-cols-2 lg:grid-cols-3 max-sm:gap-6">
                        <?php
                        $member_count = 0;
                        while ($team_query->have_posts()):
                            $team_query->the_post();
                            $member_count++;

                            // Get team member data
                            $member_name = get_the_title();
                            $member_role = '';
                            $member_image = get_post_thumbnail_id();
                            $member_image_alt = get_post_meta($member_image, '_wp_attachment_image_alt', true) ?: $member_name;

                            // Get role from taxonomy
                            $roles = get_the_terms(get_the_ID(), 'team_role');
                            if ($roles && !is_wp_error($roles)) {
                                $member_role = $roles[0]->name;
                            }

                            // Fallback role from excerpt or custom field
                            if (empty($member_role)) {
                                $member_role = get_the_excerpt() ?: 'Team Member';
                            }
                        ?>
                            <article
                                class="box-border flex flex-col items-start h-[424px] max-md:h-[380px] max-sm:h-[340px]"
                                aria-labelledby="member-<?php echo esc_attr(get_the_ID()); ?>-name"
                            >
                                <div class="box-border flex overflow-hidden relative flex-col flex-1 justify-center items-center w-full">
                                    <?php if ($member_image): ?>
                                        <div class="absolute inset-0 w-full h-full">
                                            <?php echo wp_get_attachment_image($member_image, 'large', false, [
                                                'alt' => esc_attr($member_image_alt),
                                                'class' => 'w-full h-full object-cover',
                                                'loading' => $member_count <= 3 ? 'eager' : 'lazy'
                                            ]); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="box-border flex relative z-10 flex-col flex-1 justify-end items-start p-8 w-full max-sm:p-6">
                                        <div class="box-border flex flex-col items-start px-8 py-4 bg-gray-200 max-sm:px-6 max-sm:py-3">
                                            <h3
                                                id="member-<?php echo esc_attr(get_the_ID()); ?>-name"
                                                class="box-border text-3xl font-semibold tracking-normal leading-10 text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                                            >
                                                <?php echo esc_html($member_name); ?>
                                            </h3>
                                            <p class="box-border text-base tracking-normal leading-7 text-gray-700 max-md:text-base max-md:leading-6 max-sm:text-sm max-sm:leading-6">
                                                <?php echo esc_html($member_role); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </main>

                <?php wp_reset_postdata(); ?>

            <?php else: ?>
                <!-- No team members found -->
                <div class="py-12 w-full text-center">
                    <p class="text-lg text-gray-600">No team members found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
