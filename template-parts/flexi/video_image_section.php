<?php
$media_type = get_sub_field('media_type');
$video_file = get_sub_field('video_file');
$youtube_url = get_sub_field('youtube_url');
$poster_image = get_sub_field('poster_image');
$image = get_sub_field('image');
$background_color = get_sub_field('background_color');
$show_play_button = get_sub_field('show_play_button');

// Generate unique ID for this section
$section_id = 'video-image-' . wp_rand(1000, 9999);

// Handle padding settings
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

// Get image alt text
$image_alt = '';
$poster_alt = '';
if ($image) {
    $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Media content';
}
if ($poster_image) {
    $poster_alt = get_post_meta($poster_image, '_wp_attachment_image_alt', true) ?: 'Video poster';
}

// Extract YouTube video ID if YouTube URL is provided
$youtube_id = '';
if ($youtube_url && $media_type === 'youtube') {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches);
    $youtube_id = isset($matches[1]) ? $matches[1] : '';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-label="Media content section"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="box-border flex gap-12 items-start px-20 py-0 w-full max-md:gap-8 max-md:px-12 max-md:py-0 max-sm:gap-6 max-sm:px-4 max-sm:py-0">
            <div class="flex overflow-hidden relative flex-col items-start flex-1 h-[500px] max-md:h-[400px] max-sm:h-[300px]">

                <?php if ($media_type === 'video' && $video_file): ?>
                    <!-- Local Video -->
                    <figure class="relative w-full h-full">
                        <video
                            class="object-cover w-full h-full rounded-lg"
                            <?php if ($poster_image): ?>
                                poster="<?php echo esc_url(wp_get_attachment_url($poster_image)); ?>"
                            <?php endif; ?>
                            controls
                            preload="metadata"
                            aria-label="Video content"
                        >
                            <source src="<?php echo esc_url(wp_get_attachment_url($video_file)); ?>" type="video/mp4">
                            <p>Your browser does not support the video element. <a href="<?php echo esc_url(wp_get_attachment_url($video_file)); ?>" class="text-blue-600 underline">Download the video</a> instead.</p>
                        </video>

                        <?php if ($show_play_button): ?>
                            <div class="flex absolute inset-0 justify-center items-center pointer-events-none">
                                <div class="p-4 bg-black bg-opacity-30 rounded-full">
                                    <svg
                                        width="64"
                                        height="64"
                                        viewBox="0 0 64 64"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="text-white"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M31.9997 58.6666C46.7273 58.6666 58.6663 46.7276 58.6663 32C58.6663 17.2724 46.7273 5.33331 31.9997 5.33331C17.2721 5.33331 5.33301 17.2724 5.33301 32C5.33301 46.7276 17.2721 58.6666 31.9997 58.6666Z"
                                            stroke="currentColor"
                                            stroke-width="4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M26.667 21.3333L42.667 32L26.667 42.6666V21.3333Z"
                                            stroke="currentColor"
                                            stroke-width="4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </div>
                        <?php endif; ?>
                    </figure>

                <?php elseif ($media_type === 'youtube' && $youtube_id): ?>
                    <!-- YouTube Video -->
                    <figure class="relative w-full h-full">
                        <div class="overflow-hidden relative w-full h-full bg-gray-900 rounded-lg">
                            <iframe
                                class="w-full h-full"
                                src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>"
                                title="YouTube video player"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </div>
                    </figure>

                <?php elseif ($media_type === 'image' && $image): ?>
                    <!-- Static Image -->
                    <figure class="relative w-full h-full">
                        <?php echo wp_get_attachment_image($image, 'full', false, [
                            'alt' => esc_attr($image_alt),
                            'class' => 'w-full h-full object-cover rounded-lg',
                            'loading' => 'lazy'
                        ]); ?>
                    </figure>

                <?php else: ?>
                    <!-- Placeholder/Default State -->
                    <figure class="flex relative justify-center items-center w-full h-full bg-gray-300 rounded-lg">
                        <div class="text-center text-gray-600">
                            <svg
                                width="64"
                                height="64"
                                viewBox="0 0 64 64"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="mx-auto mb-4 text-gray-400"
                                aria-hidden="true"
                            >
                                <path
                                    d="M31.9997 58.6666C46.7273 58.6666 58.6663 46.7276 58.6663 32C58.6663 17.2724 46.7273 5.33331 31.9997 5.33331C17.2721 5.33331 5.33301 17.2724 5.33301 32C5.33301 46.7276 17.2721 58.6666 31.9997 58.6666Z"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M26.667 21.3333L42.667 32L26.667 42.6666V21.3333Z"
                                    stroke="currentColor"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p class="text-sm">No media selected</p>
                        </div>
                    </figure>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
