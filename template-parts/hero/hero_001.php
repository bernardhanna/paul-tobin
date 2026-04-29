<?php
// Get all field values
$heading       = get_sub_field('heading');
$heading_tag   = get_sub_field('heading_tag');
$description   = get_sub_field('description');
$button        = get_sub_field('button');

// Whitelist heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

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

// HEIGHT select ('500'|'665'); inner pages use min-height so the section can grow with content
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

// Front page keeps the original fixed-height / bottom-aligned box; inner pages use min-height + header clearance.
$is_front_hero = is_front_page();
if ($is_front_hero) {
    $height_class = ($max_height_choice === '665') ? 'h-[665px]' : 'h-[500px]';
} else {
    $height_class = ($max_height_choice === '665') ? 'min-h-[665px] h-auto' : 'min-h-[500px] h-auto';
}

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

// Sell With Us hero: keep face visible and avoid aggressive crop on xl+.
$is_sell_with_us = is_page('sell-with-us');
$desktop_image_class = $is_sell_with_us
    ? 'w-full h-full object-cover object-top xxl:object-contain'
    : 'w-full h-full object-cover';
$mobile_image_class = $is_sell_with_us
    ? 'w-full h-full object-cover object-top min-h-[18.75rem]'
    : 'w-full h-full object-cover min-h-[18.75rem]';
$mobile_media_wrapper_class = $is_sell_with_us
    ? 'relative z-20 w-full md:hidden mt-[5rem]'
    : 'relative z-20 w-full md:hidden';
$desktop_media_wrapper_class = $is_sell_with_us
    ? 'hidden absolute inset-x-0 top-[5rem] bottom-0 md:block'
    : 'hidden absolute inset-0 md:block';

// ---- Background layers (desktop/tablet) ----
$video_background = '';
$image_desktop_bg = '';
$image_mobile     = '';

// For *md and up*, we render the bg as an absolute layer (not inline style).
if ($background_type === 'image' && $background_image) {
    $bg_alt   = get_post_meta($background_image, '_wp_attachment_image_alt', true) ?: 'Background image';
    $desktop_bg_img = wp_get_attachment_image(
        $background_image,
        '2048x2048',
        false,
        [
            'class' => $desktop_image_class,
            'alt' => '',
            'loading' => 'eager',
            'fetchpriority' => 'high',
            'decoding' => 'sync',
            'sizes' => '100vw',
        ]
    );
    $mobile_bg_img = wp_get_attachment_image(
        $background_image,
        'large',
        false,
        [
            'class' => $mobile_image_class,
            'alt' => $bg_alt,
            'loading' => 'lazy',
            'decoding' => 'async',
            'sizes' => '100vw',
        ]
    );
    // Desktop/tablet: absolute cover layer (hidden on mobile)
    $image_desktop_bg = '
        <div class="' . esc_attr($desktop_media_wrapper_class) . '" aria-hidden="true">
            ' . $desktop_bg_img . '
        </div>';
    // Mobile (<= md): real <img>, min-height 18.75rem (300px)
    $image_mobile = '
        <div class="' . esc_attr($mobile_media_wrapper_class) . '">
            ' . $mobile_bg_img . '
        </div>';
}

// Video backgrounds (md+ as absolute layer, ≤md inline container)
if ($background_type === 'video') {
    if ($background_video_type === 'local' && $background_video_file) {
        $video_id = 0;
        $video_url = '';
        if (is_array($background_video_file)) {
            if (!empty($background_video_file['ID'])) {
                $video_id = (int) $background_video_file['ID'];
            }
            if (!empty($background_video_file['url']) && is_string($background_video_file['url'])) {
                $video_url = $background_video_file['url'];
            }
        } elseif (is_numeric($background_video_file)) {
            $video_id = (int) $background_video_file;
        } elseif (is_string($background_video_file) && preg_match('#^https?://#', $background_video_file)) {
            $video_url = $background_video_file;
        }
        if ($video_url === '' && $video_id > 0) {
            $video_url = (string) wp_get_attachment_url($video_id);
        }
        $video_mime = 'video/mp4';
        if ($video_id > 0) {
            $mime = get_post_mime_type($video_id);
            if (is_string($mime) && strpos($mime, 'video/') === 0) {
                $video_mime = $mime;
            }
        } elseif (is_string($video_url) && $video_url !== '') {
            $check = wp_check_filetype($video_url);
            if (!empty($check['type']) && is_string($check['type']) && strpos($check['type'], 'video/') === 0) {
                $video_mime = $check['type'];
            }
        }
        $poster_url = $video_poster ? wp_get_attachment_image_url($video_poster, 'full') : '';
        $poster_attr = $poster_url ? " poster='" . esc_url($poster_url) . "'" : '';
        if ($video_url !== '') {
            $video_background = "
                <div class='hidden absolute inset-0 md:block' aria-hidden='true'>
                    <video autoplay muted loop playsinline webkit-playsinline preload='metadata' class='object-cover absolute inset-0 w-full h-full matrix-hero-bg-video'" . $poster_attr . ">
                        <source src='" . esc_url($video_url) . "' type='" . esc_attr($video_mime) . "'>
                    </video>
                </div>";
            $image_mobile = '
                <div class="relative z-20 w-full md:hidden">
                    <video autoplay muted loop playsinline webkit-playsinline preload="metadata" class="object-cover w-full h-full min-h-[18.75rem] matrix-hero-bg-video" ' . ($poster_url ? 'poster="' . esc_url($poster_url) . '"' : '') . '>
                        <source src="' . esc_url($video_url) . '" type="' . esc_attr($video_mime) . '">
                    </video>
                </div>';
        }
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

// Content position: inner pages align from top + grow down; front page keeps bottom-aligned box
if ($is_front_hero) {
    $position_classes = match ($content_box_position) {
        'center' => 'justify-center items-center',
        'right'  => 'justify-end items-end',
        default  => 'justify-start items-end',
    };
} else {
    $position_classes = match ($content_box_position) {
        'center' => 'justify-center items-center',
        'right'  => 'justify-end items-start',
        default  => 'justify-start items-start',
    };
}

if ($is_front_hero) {
    $hero_inner_row_class = 'relative z-20 flex max-w-container w-full mx-auto max-md:p-0';
    $hero_box_class       = 'flex flex-col gap-6 items-start p-8 border-4 border-solid max-w-[425px] max-md:p-6 max-md:max-w-full max-sm:gap-5 max-sm:p-5 md:m-[2rem] pt-[1rem]';
    $hero_heading_class   = 'text-[#0A1119] font-secondary text-[38px] font-semibold leading-[40px] tracking-[-0.16px] w-full relative top-[0.8rem]';
    $hero_divider_class   = 'flex relative left-[-3px] top-[0.8rem] justify-between items-start w-[71px] max-sm:w-[60px]';
    $hero_desc_class      = 'font-primary w-full text-base tracking-normal leading-7 text-[#434B53] max-sm:text-sm max-sm:leading-6 wp_editor relative top-[10px] left-[-3px]';
    $hero_btn_class       = 'relative top-[5px] left-[-2px] flex gap-2.5 justify-center items-center self-stretch px-6 py-0 w-full h-11 whitespace-nowrap transition-all duration-200 ease-in-out cursor-pointer bg-[#0A1119] text-slate-50 hover:bg-[#40BFF5] hover:text-black  focus:bg-[#40BFF5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119] max-sm:px-5 max-sm:h-12 btn';
} else {
    $hero_inner_row_class = 'relative z-20 flex max-w-container w-full mx-auto max-md:p-0 md:py-8';
    $hero_box_class       = 'flex flex-col gap-6 items-start p-8 border-4 border-solid max-w-[425px] max-md:p-6 max-md:max-w-full max-sm:gap-5 max-sm:p-5 md:mx-[2rem] md:mb-[2rem] md:mt-[7rem]';
    $hero_heading_class   = 'text-[#0A1119] font-secondary text-[38px] font-semibold leading-[40px] tracking-[-0.16px] w-full';
    $hero_divider_class   = 'flex relative left-[-3px] justify-between items-start w-[71px] max-sm:w-[60px]';
    $hero_desc_class      = 'font-primary w-full text-base tracking-normal leading-7 text-[#434B53] max-sm:text-sm max-sm:leading-6 wp_editor';
    $hero_btn_class       = 'flex gap-2.5 justify-center items-center self-stretch px-6 py-0 w-full h-11 whitespace-nowrap transition-all duration-200 ease-in-out cursor-pointer bg-[#0A1119] text-slate-50 hover:bg-[#40BFF5] hover:text-black  focus:bg-[#40BFF5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119] max-sm:px-5 max-sm:h-12 btn';
}

// Content checks
$has_heading     = !empty($heading);
$has_description = !empty($description);
$has_button      = !empty($button['url']) && !empty($button['title']);
$has_content     = $has_heading || $has_description || $has_button;

// If an inner-page hero has only background media (no text/button content),
// drop the mobile/tablet min-height so we don't leave a large blank block.
$mobile_min_height_reset_class = (!$is_front_hero && !$has_content) ? 'max-md:min-h-0' : '';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex max-md:flex-col overflow-hidden bg-center bg-no-repeat bg-cover <?php echo esc_attr($height_class); ?> max-md:h-auto <?php echo esc_attr($mobile_min_height_reset_class); ?> <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    role="region"
    <?php if ($has_heading): ?>
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
    <?php else: ?>
    aria-label="<?php echo esc_attr__('Page hero', 'matrix-starter'); ?>"
    <?php endif; ?>
>
    <!-- Background layers -->
    <?php echo $image_desktop_bg; // md+ image layer ?>
    <?php echo $video_background; // md+ video layer ?>

    <?php if ($overlay_enabled && $overlay_style): ?>
        <div class="hidden absolute inset-0 z-10 md:block" style="<?php echo esc_attr($overlay_style); ?>" aria-hidden="true"></div>
    <?php endif; ?>

    <!-- Mobile media (<= md) -->
    <?php if (!empty($image_mobile)) : ?>
        <?php echo $image_mobile; ?>
    <?php endif; ?>

    <?php if ($has_content): ?>
    <!-- Content -->
    <div class="<?php echo esc_attr($hero_inner_row_class . ' ' . $position_classes); ?>">
        <div class="w-full">
            <div class="<?php echo esc_attr($hero_box_class); ?>"
                 style="<?php echo esc_attr($content_box_style); ?>">

                <?php if ($has_heading): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="<?php echo esc_attr($hero_heading_class); ?>"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <?php if ($has_heading || $has_description || $has_button): ?>
                    <div class="<?php echo esc_attr($hero_divider_class); ?>" aria-hidden="true">
                        <div class="bg-[#EF7B10] flex-1 h-[5px]"></div>
                        <div class="bg-[#0098D8] flex-1 h-[5px]"></div>
                        <div class="bg-[#B6C0C0] flex-1 h-[5px]"></div>
                        <div class="bg-[#74AF27] flex-1 h-[5px]"></div>
                    </div>
                <?php endif; ?>

                <?php if ($has_description): ?>
                    <div class="<?php echo esc_attr($hero_desc_class); ?>">
                        <?php echo wp_kses_post($description); ?>
                    </div>
                <?php endif; ?>

                <?php if ($has_button): ?>
                    <a href="<?php echo esc_url($button['url']); ?>"
                       class="<?php echo esc_attr($hero_btn_class); ?>"
                       target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                       aria-label="<?php echo esc_attr($button['title']); ?>">
                        <span class="text-sm font-semibold tracking-normal leading-6"><?php echo esc_html($button['title']); ?></span>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php endif; // $has_content ?>
</section>
<script>
(function () {
    var root = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!root) return;

    var attemptPlay = function (video) {
        if (!video) return;
        video.muted = true;
        video.defaultMuted = true;
        video.loop = true;
        video.setAttribute('muted', '');
        video.setAttribute('autoplay', '');
        video.setAttribute('loop', '');
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');

        var promise = video.play();
        if (promise && typeof promise.catch === 'function') {
            promise.catch(function () {});
        }
    };

    var videos = root.querySelectorAll('.matrix-hero-bg-video');
    videos.forEach(function (video) {
        if (!video || video.dataset.matrixHeroInit === '1') return;
        video.dataset.matrixHeroInit = '1';
        attemptPlay(video);
        video.addEventListener('loadeddata', function () { attemptPlay(video); }, { once: true });
        video.addEventListener('canplay', function () { attemptPlay(video); }, { once: true });
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState !== 'visible') return;
        videos.forEach(function (video) { attemptPlay(video); });
    });
    window.addEventListener('pageshow', function () {
        videos.forEach(function (video) { attemptPlay(video); });
    });
})();
</script>
