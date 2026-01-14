<?php
// Get all field values
$heading       = get_sub_field('heading');
$heading_tag   = get_sub_field('heading_tag');
$description   = get_sub_field('description');
$button        = get_sub_field('button');

// Background media settings
$background_type          = get_sub_field('background_type');
$background_image         = get_sub_field('background_image');
$background_video_type    = get_sub_field('background_video_type');
$background_video_file    = get_sub_field('background_video_file');
$background_video_youtube = get_sub_field('background_video_youtube');
$background_video_vimeo   = get_sub_field('background_video_vimeo');
$video_poster             = get_sub_field('video_poster');

// Design settings
$overlay_enabled          = get_sub_field('overlay_enabled');
$overlay_color            = get_sub_field('overlay_color');
$overlay_opacity          = get_sub_field('overlay_opacity');
$content_box_bg_color     = get_sub_field('content_box_bg_color');
$content_box_bg_opacity   = get_sub_field('content_box_bg_opacity');
$content_box_border_color = get_sub_field('content_box_border_color');
$content_box_border_width = get_sub_field('content_box_border_width');

// Layout settings
$content_box_position = get_sub_field('content_box_position');

// HEIGHT select ('500'|'665') -> fixed classes so Tailwind keeps them
$raw_max_height = get_sub_field('max_height');
if (empty($raw_max_height) && function_exists('get_sub_field_object')) {
    $obj = get_sub_field_object('max_height');
    if (!empty($obj['value'])) $raw_max_height = $obj['value'];
}
if (is_array($raw_max_height)) {
    $raw_max_height = isset($raw_max_height['value']) ? $raw_max_height['value'] : reset($raw_max_height);
}
$max_height_choice = (string) $raw_max_height;
if ($max_height_choice === '665px') $max_height_choice = '665';
if ($max_height_choice === '500px') $max_height_choice = '500';
if (!in_array($max_height_choice, ['500','665'], true)) $max_height_choice = '500';
$height_class = ($max_height_choice === '665') ? 'h-[665px]' : 'h-[500px]';

// Padding
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

// Unique ID
$section_id = 'hero_' . uniqid();

// ---- Background layers (desktop/tablet) ----
$video_background = '';
$image_desktop_bg = '';
$image_mobile     = '';

// For *md and up*, we render the bg as an absolute layer (not inline style).
if ($background_type === 'image' && $background_image) {
    $bg_url   = wp_get_attachment_image_url($background_image, 'full');
    $bg_alt   = get_post_meta($background_image, '_wp_attachment_image_alt', true) ?: 'Background image';
    // Desktop/tablet: absolute cover layer (hidden on mobile)
    $image_desktop_bg = '
        <div class="hidden absolute inset-0 md:block" aria-hidden="true">
            <div class="w-full h-full bg-center bg-cover" style="background-image:url(' . esc_url($bg_url) . ');"></div>
        </div>';
    // Mobile (<= md): real <img>, min-height 18.75rem (300px)
    $image_mobile = '
        <div class="relative z-20 w-full md:hidden">
            <img src="' . esc_url($bg_url) . '" alt="' . esc_attr($bg_alt) . '"
                 class="w-full h-full object-cover min-h-[18.75rem]" loading="eager" />
        </div>';
}

// Video backgrounds (md+ as absolute layer, ≤md inline container like before)
if ($background_type === 'video') {
    if ($background_video_type === 'local' && $background_video_file) {
        $video_url  = wp_get_attachment_url($background_video_file);
        $poster_url = $video_poster ? wp_get_attachment_image_url($video_poster, 'full') : '';
        $video_background = "
            <div class='hidden absolute inset-0 md:block' aria-hidden='true'>
                <video autoplay muted loop playsinline class='object-cover absolute inset-0 w-full h-full' " . ($poster_url ? "poster='" . esc_url($poster_url) . "'" : "") . ">
                    <source src='" . esc_url($video_url) . "' type='video/mp4'>
                </video>
            </div>";
        $image_mobile = '
            <div class="relative z-20 w-full md:hidden">
                <video autoplay muted loop playsinline class="object-cover w-full h-full min-h-[18.75rem]" ' . ($poster_url ? 'poster="' . esc_url($poster_url) . '"' : '') . '>
                    <source src="' . esc_url($video_url) . '" type="video/mp4">
                </video>
            </div>';
    } elseif ($background_video_type === 'youtube' && $background_video_youtube) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $background_video_youtube, $m);
        $youtube_id = $m[1] ?? '';
        if ($youtube_id) {
            $video_background = "
                <div class='hidden absolute inset-0 md:block' aria-hidden='true'>
                    <iframe src='https://www.youtube.com/embed/" . esc_attr($youtube_id) . "?autoplay=1&mute=1&loop=1&playlist=" . esc_attr($youtube_id) . "&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1'
                            class='object-cover absolute inset-0 w-full h-full' frameborder='0'
                            allow='autoplay; encrypted-media' allowfullscreen title='Background video'></iframe>
                </div>";
            $image_mobile = '
                <div class="relative z-20 w-full md:hidden">
                    <iframe src="https://www.youtube.com/embed/' . esc_attr($youtube_id) . '?autoplay=1&mute=1&loop=1&playlist=' . esc_attr($youtube_id) . '&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1"
                            class="w-full h-full min-h-[18.75rem]" frameborder="0"
                            allow="autoplay; encrypted-media" allowfullscreen title="Background video"></iframe>
                </div>';
        }
    } elseif ($background_video_type === 'vimeo' && $background_video_vimeo) {
        preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $background_video_vimeo, $m);
        $vimeo_id = $m[3] ?? '';
        if ($vimeo_id) {
            $video_background = "
                <div class='hidden absolute inset-0 md:block' aria-hidden='true'>
                    <iframe src='https://player.vimeo.com/video/" . esc_attr($vimeo_id) . "?autoplay=1&muted=1&loop=1&background=1&controls=0'
                            class='object-cover absolute inset-0 w-full h-full' frameborder='0'
                            allow='autoplay; fullscreen' allowfullscreen title='Background video'></iframe>
                </div>";
            $image_mobile = '
                <div class="relative z-20 w-full md:hidden">
                    <iframe src="https://player.vimeo.com/video/' . esc_attr($vimeo_id) . '?autoplay=1&muted=1&loop=1&background=1&controls=0"
                            class="w-full h-full min-h-[18.75rem]" frameborder="0"
                            allow="autoplay; fullscreen" allowfullscreen title="Background video"></iframe>
                </div>';
        }
    }
}

// Overlay (md+ only so mobile image/video stays clean)
$overlay_style = '';
if ($overlay_enabled && $overlay_color) {
    $alpha_hex     = sprintf('%02x', max(0, min(100, (int)$overlay_opacity)) * 255 / 100);
    $rgba_overlay  = $overlay_color . $alpha_hex;
    $overlay_style = "background-color: {$rgba_overlay};";
}

// Content box style
$content_box_style = '';
if ($content_box_bg_color) {
    $alpha_hex_bg = sprintf('%02x', max(0, min(100, (int)$content_box_bg_opacity)) * 255 / 100);
    $content_box_style .= "background-color: {$content_box_bg_color}{$alpha_hex_bg};";
}
if ($content_box_border_color && $content_box_border_width) {
    $content_box_style .= "border-color: {$content_box_border_color}; border-width: {$content_box_border_width}px;";
}

// Content position
$position_classes = match ($content_box_position) {
    'center' => 'justify-center items-center',
    'right'  => 'justify-end items-end',
    default  => 'justify-start items-end',
};
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="mt-[4.5rem] relative flex max-md:flex-col overflow-hidden bg-center bg-no-repeat bg-cover <?php echo esc_attr($height_class); ?> max-md:h-auto <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style=""
    role="banner"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <!-- Background layers -->
    <?php echo $image_desktop_bg; // md+ image layer ?>
    <?php echo $video_background; // md+ video layer ?>

    <?php if ($overlay_enabled && $overlay_style): ?>
        <div class="hidden absolute inset-0 z-10 md:block" style="<?php echo esc_attr($overlay_style); ?>" aria-hidden="true"></div>
    <?php endif; ?>

    <!-- Mobile media (<= md): if image selected, render real <img>; if video, render inline video/iframe -->
    <?php if (!empty($image_mobile)) : ?>
        <?php echo $image_mobile; ?>
    <?php endif; ?>

    <!-- Content -->
    <div class="relative z-20 flex max-w-container w-full mx-auto max-md:p-0 <?php echo esc_attr($position_classes); ?>">
        <div class="w-full">
            <div class="flex flex-col gap-6 items-start p-8 border-4 border-solid max-w-[425px] max-md:p-6 max-md:max-w-full max-sm:gap-5 max-sm:p-5 md:m-[2rem] pt-[1rem]"
                 style="<?php echo esc_attr($content_box_style); ?>">

                <?php if (!empty($heading)): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-[#0A1119] font-secondary text-[40px] font-semibold leading-[40px] tracking-[-0.16px] w-full relative top-[0.8rem]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <div class="flex relative left-[-3px] top-[0.8rem] justify-between items-start w-[71px] max-sm:w-[60px]" aria-hidden="true">
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

                <?php if (!empty($button['url']) && !empty($button['title'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>"
                       class="relative top-[5px] left-[-2px] flex gap-2.5 justify-center items-center self-stretch px-6 py-0 w-full h-11 whitespace-nowrap transition-all duration-200 ease-in-out cursor-pointer bg-slate-900 text-slate-50 hover:bg-slate-700 focus:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 max-sm:px-5 max-sm:h-12 btn"
                       target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                       aria-label="<?php echo esc_attr($button['title']); ?>">
                        <span class="text-sm font-semibold tracking-normal leading-6"><?php echo esc_html($button['title']); ?></span>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
