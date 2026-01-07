<?php
// Get all field values
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$description = get_sub_field('description');
$button = get_sub_field('button');

// Background media settings
$background_type = get_sub_field('background_type');
$background_image = get_sub_field('background_image');
$background_video_type = get_sub_field('background_video_type');
$background_video_file = get_sub_field('background_video_file');
$background_video_youtube = get_sub_field('background_video_youtube');
$background_video_vimeo = get_sub_field('background_video_vimeo');
$video_poster = get_sub_field('video_poster');

// Design settings
$overlay_enabled = get_sub_field('overlay_enabled');
$overlay_color = get_sub_field('overlay_color');
$overlay_opacity = get_sub_field('overlay_opacity');
$content_box_bg_color = get_sub_field('content_box_bg_color');
$content_box_bg_opacity = get_sub_field('content_box_bg_opacity');
$content_box_border_color = get_sub_field('content_box_border_color');
$content_box_border_width = get_sub_field('content_box_border_width');

// Layout settings
$content_box_position = get_sub_field('content_box_position');

// Padding settings
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

// Generate unique ID for this section
$section_id = 'hero_' . uniqid();

// Prepare background styles
$background_style = '';
$video_background = '';

if ($background_type === 'image' && $background_image) {
    $background_url = wp_get_attachment_image_url($background_image, 'full');
    $background_style = "background-image: url('{$background_url}');";
} elseif ($background_type === 'video') {
    if ($background_video_type === 'local' && $background_video_file) {
        $video_url = wp_get_attachment_url($background_video_file);
        $poster_url = $video_poster ? wp_get_attachment_image_url($video_poster, 'full') : '';
        $video_background = "
            <video
                autoplay
                muted
                loop
                playsinline
                class='object-cover absolute inset-0 w-full h-full'
                " . ($poster_url ? "poster='{$poster_url}'" : "") . "
                aria-hidden='true'
            >
                <source src='{$video_url}' type='video/mp4'>
                Your browser does not support the video tag.
            </video>
        ";
    } elseif ($background_video_type === 'youtube' && $background_video_youtube) {
        // Extract YouTube video ID
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $background_video_youtube, $matches);
        $youtube_id = $matches[1] ?? '';
        if ($youtube_id) {
            $video_background = "
                <iframe
                    src='https://www.youtube.com/embed/{$youtube_id}?autoplay=1&mute=1&loop=1&playlist={$youtube_id}&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1'
                    class='object-cover absolute inset-0 w-full h-full'
                    frameborder='0'
                    allow='autoplay; encrypted-media'
                    allowfullscreen
                    aria-hidden='true'
                    title='Background video'
                ></iframe>
            ";
        }
    } elseif ($background_video_type === 'vimeo' && $background_video_vimeo) {
        // Extract Vimeo video ID
        preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $background_video_vimeo, $matches);
        $vimeo_id = $matches[3] ?? '';
        if ($vimeo_id) {
            $video_background = "
                <iframe
                    src='https://player.vimeo.com/video/{$vimeo_id}?autoplay=1&muted=1&loop=1&background=1&controls=0'
                    class='object-cover absolute inset-0 w-full h-full'
                    frameborder='0'
                    allow='autoplay; fullscreen'
                    allowfullscreen
                    aria-hidden='true'
                    title='Background video'
                ></iframe>
            ";
        }
    }
}

// Prepare overlay styles
$overlay_style = '';
if ($overlay_enabled && $overlay_color) {
    $rgba_overlay = $overlay_color . sprintf('%02x', round($overlay_opacity * 255 / 100));
    $overlay_style = "background-color: {$rgba_overlay};";
}

// Prepare content box styles
$content_box_style = '';
if ($content_box_bg_color) {
    $rgba_bg = $content_box_bg_color . sprintf('%02x', round($content_box_bg_opacity * 255 / 100));
    $content_box_style .= "background-color: {$rgba_bg};";
}
if ($content_box_border_color && $content_box_border_width) {
    $content_box_style .= "border-color: {$content_box_border_color}; border-width: {$content_box_border_width}px;";
}

// Content box positioning classes
$position_classes = '';
switch ($content_box_position) {
    case 'left':
        $position_classes = 'justify-start items-end';
        break;
    case 'center':
        $position_classes = 'justify-center items-center';
        break;
    case 'right':
        $position_classes = 'justify-end items-end';
        break;
    default:
        $position_classes = 'justify-start items-end';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="mt-[4.5rem] relative flex overflow-hidden bg-center bg-no-repeat bg-cover h-[665px] max-md:h-auto <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="<?php echo esc_attr($background_style); ?>"
    role="banner"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <?php if ($video_background): ?>
        <?php echo $video_background; ?>
    <?php endif; ?>

    <?php if ($overlay_enabled && $overlay_style): ?>
        <div
            class="absolute inset-0 z-10"
            style="<?php echo esc_attr($overlay_style); ?>"
            aria-hidden="true"
        ></div>
    <?php endif; ?>

    <div class="relative z-20 flex max-w-container w-full mx-auto max-md:p-0 <?php echo esc_attr($position_classes); ?>">
        <div class=w-full">
            <div class="flex flex-col gap-6 items-start p-8 border-4 border-solid max-w-[425px] max-md:p-6 max-md:max-w-full max-sm:gap-5 max-sm:p-5 m-[2rem] pt-[1rem]"
                style="<?php echo esc_attr($content_box_style); ?>">

                <?php if (!empty($heading)): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-[#0A1119] font-secondary text-[40px] font-semibold leading-[40px] tracking-[-0.16px] w-full relative top-[0.8rem]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <div
                    class="flex relative left-[-3px] top-[0.8rem] justify-between items-start w-[71px] max-sm:w-[60px]"
                    role="presentation"
                    aria-hidden="true"
                >
                    <div class="bg-[#EF7B10] flex-1 h-[5px]"></div>
                    <div class="bg-[#0098D8] flex-1 h-[5px]"></div>
                    <div class="bg-[#B6C0C0] flex-1 h-[5px]"></div>
                    <div class="bg-[#74AF27] flex-1 h-[5px]"></div>
                </div>

                <?php if (!empty($description)): ?>
                    <div class="font-primary w-full text-base tracking-normal leading-7 text-gray-700 max-sm:text-sm max-sm:leading-6 wp_editor relative top-[10px] left-[-3px]">
                        <?php echo wp_kses_post($description); ?>
                    </div>
                <?php endif; ?>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                    <a
                        href="<?php echo esc_url($button['url']); ?>"
                        class="relative top-[5px] left-[-2px] flex gap-2.5 justify-center items-center self-stretch px-6 py-0 w-full h-11 whitespace-nowrap transition-all duration-200 ease-in-out cursor-pointer bg-slate-900 text-slate-50 hover:bg-slate-700 focus:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 max-sm:px-5 max-sm:h-12 btn"
                        target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                        aria-label="<?php echo esc_attr($button['title']); ?>"
                    >
                        <span class="text-sm font-semibold tracking-normal leading-6">
                            <?php echo esc_html($button['title']); ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
