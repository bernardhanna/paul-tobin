<?php
$section_id = 'related-projects-' . uniqid();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$projects = get_sub_field('projects');
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
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex flex-col gap-12 items-start px-20 py-0 w-full max-md:gap-10 max-md:px-10 max-md:py-0 max-sm:gap-8 max-sm:px-5 max-sm:py-0">

            <?php if (!empty($heading)): ?>
            <header class="flex flex-col gap-6 items-center w-full">
                <div class="flex flex-col gap-6 items-start w-full">
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="w-full text-3xl font-semibold tracking-normal leading-10 text-center text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div
                        class="flex justify-between items-start w-[71px] max-sm:w-[60px]"
                        role="presentation"
                        aria-hidden="true"
                    >
                        <div class="bg-orange-500 flex-[1_0_0] h-[5px]"></div>
                        <div class="bg-sky-500 flex-[1_0_0] h-[5px]"></div>
                        <div class="bg-slate-300 flex-[1_0_0] h-[5px]"></div>
                        <div class="bg-lime-600 flex-[1_0_0] h-[5px]"></div>
                    </div>
                </div>
            </header>
            <?php endif; ?>

            <?php if ($projects && is_array($projects)): ?>
            <div class="flex flex-wrap gap-12 content-center items-center w-full max-md:gap-8 max-sm:flex-col max-sm:gap-6">
                <?php foreach ($projects as $index => $project):
                    $project_name = $project['project_name'] ?? '';
                    $project_type = $project['project_type'] ?? '';
                    $project_image = $project['project_image'] ?? '';
                    $project_image_alt = '';

                    if ($project_image) {
                        $project_image_alt = get_post_meta($project_image, '_wp_attachment_image_alt', true) ?: $project_name ?: 'Project image';
                    }

                    $card_id = $section_id . '-card-' . ($index + 1);
                ?>
                <article
                    id="<?php echo esc_attr($card_id); ?>"
                    class="flex flex-col items-start flex-[1_0_0] h-[318px] max-md:h-[280px] max-sm:flex-none max-sm:w-full max-sm:h-[300px]"
                    aria-labelledby="<?php echo esc_attr($card_id); ?>-title"
                >
                    <div class="flex overflow-hidden flex-col justify-center items-center w-full flex-[1_0_0] relative">
                        <?php if ($project_image): ?>
                            <?php echo wp_get_attachment_image($project_image, 'full', false, [
                                'alt' => esc_attr($project_image_alt),
                                'class' => 'absolute inset-0 w-full h-full object-cover',
                                'loading' => 'lazy'
                            ]); ?>
                        <?php endif; ?>

                        <div class="box-border flex flex-col justify-end items-start p-8 w-full flex-[1_0_0] relative z-10">
                            <?php if (!empty($project_name) || !empty($project_type)): ?>
                            <div class="flex flex-col items-start px-8 py-4 bg-gray-200 max-md:px-6 max-md:py-3 max-sm:px-5 max-sm:py-3">
                                <?php if (!empty($project_name)): ?>
                                <h3
                                    id="<?php echo esc_attr($card_id); ?>-title"
                                    class="text-3xl font-semibold tracking-normal leading-10 text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                                >
                                    <?php echo esc_html($project_name); ?>
                                </h3>
                                <?php endif; ?>

                                <?php if (!empty($project_type)): ?>
                                <p class="text-base tracking-normal leading-7 text-gray-700 max-md:text-base max-md:leading-6 max-sm:text-sm max-sm:leading-6">
                                    <?php echo esc_html($project_type); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
