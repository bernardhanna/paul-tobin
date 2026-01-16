<?php
// Get ACF fields
$left_heading = get_sub_field('left_heading');
$left_heading_tag = get_sub_field('left_heading_tag');
$left_description = get_sub_field('left_description');
$left_button = get_sub_field('left_button');
$calendly_shortcode = get_sub_field('calendly_shortcode');

$right_heading = get_sub_field('right_heading');
$right_heading_tag = get_sub_field('right_heading_tag');
$right_description = get_sub_field('right_description');
$right_button = get_sub_field('right_button');
$video_embed = get_sub_field('video_embed');
$video_type = get_sub_field('video_type');
$youtube_url = get_sub_field('youtube_url');
$local_video = get_sub_field('local_video');

$background_color = get_sub_field('background_color') ?: '#f9fafb';

// Generate padding classes
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
$section_id = 'contact-section-' . wp_rand(1000, 9999);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex justify-between mx-auto my-0 w-full max-w-screen-xl bg-gray-50 max-md:flex-col max-sm:flex-col">

            <!-- Left Column: Got a question? -->
            <div class="box-border flex flex-1 gap-12 items-start p-20 bg-gray-50 max-md:p-12 max-sm:p-6">
                <div class="flex overflow-hidden flex-col flex-1 gap-6 items-start">

                    <!-- Left Heading Section -->
                    <header class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
                        <div class="flex flex-col gap-6 items-start w-full">
                            <?php if (!empty($left_heading)): ?>
                                <<?php echo esc_attr($left_heading_tag); ?>
                                    id="<?php echo esc_attr($section_id); ?>-heading"
                                    class="text-3xl font-semibold tracking-normal leading-10 text-slate-950"
                                >
                                    <?php echo esc_html($left_heading); ?>
                                </<?php echo esc_attr($left_heading_tag); ?>>
                            <?php endif; ?>

                            <!-- Decorative Color Bar -->
                            <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        </div>
                    </header>

                    <!-- Left Description -->
                    <?php if (!empty($left_description)): ?>
                        <div class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 wp_editor">
                            <?php echo wp_kses_post($left_description); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Left Button -->
                    <?php if ($left_button && is_array($left_button) && isset($left_button['url'], $left_button['title'])): ?>
                        <a
                            href="<?php echo esc_url($left_button['url']); ?>"
                            class="box-border flex gap-2.5 justify-center items-center px-2 py-0 h-11 whitespace-nowrap transition-colors duration-300 cursor-pointer max-md:w-full bg-[#0A1119] max-sm:h-12 btn w-fit hover:bg-[#40BFF5] hover:text-black focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119]"
                            target="<?php echo esc_attr($left_button['target'] ?? '_self'); ?>"
                            aria-label="<?php echo esc_attr($left_button['title']); ?>"
                        >
                            <span class="text-sm font-semibold tracking-normal leading-6 text-slate-50">
                                <?php echo esc_html($left_button['title']); ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <!-- Calendly Shortcode -->
                    <?php if (!empty($calendly_shortcode)): ?>
                        <div class="flex overflow-hidden flex-col justify-center items-center w-full">
                            <?php echo do_shortcode($calendly_shortcode); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Right Column: About your property -->
            <div class="box-border flex flex-1 gap-12 items-start p-20 bg-gray-50 max-md:p-12 max-sm:p-6">
                <div class="flex overflow-hidden flex-col flex-1 gap-6 items-start">

                    <!-- Right Heading Section -->
                    <header class="flex flex-col gap-6 items-start w-full max-sm:gap-4">
                        <div class="flex flex-col gap-6 items-start w-full">
                            <?php if (!empty($right_heading)): ?>
                                <<?php echo esc_attr($right_heading_tag); ?>
                                    class="text-3xl font-semibold tracking-normal leading-10 text-slate-950"
                                >
                                    <?php echo esc_html($right_heading); ?>
                                </<?php echo esc_attr($right_heading_tag); ?>>
                            <?php endif; ?>

                            <!-- Decorative Color Bar -->
                            <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        </div>
                    </header>

                    <!-- Right Description -->
                    <?php if (!empty($right_description)): ?>
                        <div class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 wp_editor">
                            <?php echo wp_kses_post($right_description); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Right Button -->
                    <?php if ($right_button && is_array($right_button) && isset($right_button['url'], $right_button['title'])): ?>
                        <a
                            href="<?php echo esc_url($right_button['url']); ?>"
                            class="box-border flex gap-2.5 justify-center items-center px-2 py-0 h-11 whitespace-nowrap border-2 border-solid transition-colors duration-300 cursor-pointer border-slate-950 max-sm:h-12 btn w-fit hover:bg-slate-950 hover:text-white focus:ring-2 focus:ring-offset-2 focus:ring-slate-950"
                            target="<?php echo esc_attr($right_button['target'] ?? '_self'); ?>"
                            aria-label="<?php echo esc_attr($right_button['title']); ?>"
                        >
                            <span class="text-sm font-semibold tracking-normal leading-6 transition-colors duration-300 text-slate-950 hover:text-white">
                                <?php echo esc_html($right_button['title']); ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <!-- Video Content -->
                    <div class="flex overflow-hidden flex-col justify-center items-center w-full">
                        <?php if ($video_type === 'youtube' && !empty($youtube_url)): ?>
                            <!-- YouTube Video -->
                            <?php
                            // Extract YouTube video ID from URL
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches);
                            $youtube_id = isset($matches[1]) ? $matches[1] : '';
                            ?>
                            <?php if (!empty($youtube_id)): ?>
                                <div class="relative w-full h-0 pb-[56.25%]">
                                    <iframe
                                        class="absolute top-0 left-0 w-full h-full object-cover aspect-[536/380.31]"
                                        src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo esc_attr($youtube_id); ?>"
                                        title="Property video"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($video_type === 'local' && !empty($local_video)): ?>
                            <!-- Local Video -->
                            <?php
                            $video_url = wp_get_attachment_url($local_video);
                            $video_mime = get_post_mime_type($local_video);
                            ?>
                            <?php if ($video_url): ?>
                                <video
                                    class="object-cover w-full h-auto aspect-[536/380.31]"
                                    autoplay
                                    muted
                                    loop
                                    playsinline
                                    preload="metadata"
                                    aria-label="Property showcase video"
                                >
                                    <source src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($video_mime); ?>">
                                    <p>Your browser does not support the video tag. Please upgrade your browser to view this content.</p>
                                </video>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Fallback placeholder -->
                            <div class="flex justify-center items-center w-full h-64 bg-gray-200 rounded">
                                <p class="text-gray-500">Video content will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
