<?php
$media_type             = get_sub_field('media_type') ?: 'image';
$image                  = get_sub_field('image');
$local_video            = get_sub_field('local_video');
$youtube_url            = get_sub_field('youtube_url');
$vimeo_url              = get_sub_field('vimeo_url');
$autoplay               = get_sub_field('autoplay');
$show_overlay           = get_sub_field('show_overlay');
$overlay_background_css = get_sub_field('overlay_background_css'); // <-- text field with color/gradient
$background_color       = get_sub_field('background_color') ?: '#EDEDED';

// Get image alt text
$image_alt = '';
if ($image) {
    $image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Media content';
}

// Extract YouTube ID
$youtube_id = '';
if ($youtube_url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $m);
    $youtube_id = $m[1] ?? '';
}

// Extract Vimeo ID
$vimeo_id = '';
if ($vimeo_url) {
    preg_match('/(?:vimeo\.com\/)([0-9]+)/', $vimeo_url, $m);
    $vimeo_id = $m[1] ?? '';
}

// Padding classes (apply to inner container)
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

$section_id = 'image-video-overlay-' . uniqid();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-label="Media content section"
>
    <div class="flex flex-col items-center w-full mx-auto  <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="box-border flex relative w-full">

            <?php if ($media_type === 'image' && $image): ?>
                <figure class="relative w-full">
                    <?php echo wp_get_attachment_image($image, 'full', false, [
                        'alt'     => esc_attr($image_alt),
                        'class'   => 'box-border flex object-cover w-full h-[480px] max-md:h-[360px] max-sm:h-60',
                        'loading' => 'lazy'
                    ]); ?>
                </figure>

            <?php elseif ($media_type === 'local_video' && $local_video): ?>
                <div class="relative w-full">
                    <video
                        class="box-border flex object-cover w-full h-[480px] max-md:h-[360px] max-sm:h-60"
                        <?php echo $autoplay ? 'autoplay muted loop playsinline' : 'controls'; ?>
                        preload="metadata"
                        aria-label="Video content"
                    >
                        <source src="<?php echo esc_url(wp_get_attachment_url($local_video)); ?>" type="video/mp4">
                        <p>Your browser does not support the video tag. <a href="<?php echo esc_url(wp_get_attachment_url($local_video)); ?>">Download the video</a>.</p>
                    </video>
                </div>

            <?php elseif ($media_type === 'youtube' && $youtube_id): ?>
                <div class="relative w-full">
                    <iframe
                        class="box-border flex w-full h-[480px] max-md:h-[360px] max-sm:h-60"
                        src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?><?php echo $autoplay ? '?autoplay=1&mute=1&loop=1&playlist=' . esc_attr($youtube_id) : ''; ?>"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>

            <?php elseif ($media_type === 'vimeo' && $vimeo_id): ?>
                <div class="relative w-full">
                    <iframe
                        class="box-border flex w-full h-[480px] max-md:h-[360px] max-sm:h-60"
                        src="https://player.vimeo.com/video/<?php echo esc_attr($vimeo_id); ?><?php echo $autoplay ? '?autoplay=1&muted=1&loop=1' : ''; ?>"
                        title="Vimeo video player"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>

            <?php else: ?>
                <div class="w-full relative bg-gray-300 flex items-center justify-center h-[480px] max-md:h-[360px] max-sm:h-60">
                    <p class="text-lg text-gray-600" role="status" aria-live="polite">No media content available</p>
                </div>
            <?php endif; ?>

            <?php if ($show_overlay && !empty($overlay_background_css)): ?>
                <!-- Pure color/gradient overlay -->
                <div
                    class="absolute inset-0"
                    style="background: <?php echo esc_attr($overlay_background_css); ?>;"
                    aria-hidden="true"
                ></div>
            <?php endif; ?>

        </div>
    </div>
</section>
