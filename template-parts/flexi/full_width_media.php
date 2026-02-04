<?php
/**
 * Frontend: Full Width Media
 * - Image OR Video (local/YouTube/Vimeo) with optional poster and fixed play overlay.
 * - Auto-pause on scroll for video.
 * - Background color via color picker (default white).
 * - Click-to-toggle and Start Muted options.
 * - Uses get_sub_field() exclusively.
 * - Random section ID.
 * - Standard wrapper + responsive padding repeater.
 */

// -------------------------
// Fields
// -------------------------
$media_type     = get_sub_field('media_type');            // image | video
$image          = get_sub_field('image');

$video_provider = get_sub_field('video_provider');        // local | youtube | vimeo
$video_file     = get_sub_field('video_file');            // local file array
$video_url      = get_sub_field('video_url');             // yt/vimeo url
$poster_image   = get_sub_field('poster_image');          // optional
$autopause      = (bool) get_sub_field('autopause_on_scroll');
$muted          = (bool) get_sub_field('muted');
$click_toggle   = (bool) get_sub_field('click_toggle_pause');

$height_xs      = (int) get_sub_field('height_xs');
$height_md      = (int) get_sub_field('height_md');
$height_xs      = $height_xs > 0 ? $height_xs : 400;
$height_md      = $height_md > 0 ? $height_md : 500;

// Background color (Design)
$bg_color_raw   = get_sub_field('background_color'); // hex
$bg_color       = function_exists('sanitize_hex_color') ? sanitize_hex_color($bg_color_raw) : $bg_color_raw;
$bg_class       = $bg_color ? ' bg-[' . $bg_color . ']' : ' bg-[#ffffff]';

// -------------------------
// Padding repeater => classes
// -------------------------
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if (!empty($screen_size) && $padding_top !== null && $padding_top !== '') {
            $padding_classes[] = $screen_size . ':pt-[' . floatval($padding_top) . 'rem]';
        }
        if (!empty($screen_size) && $padding_bottom !== null && $padding_bottom !== '') {
            $padding_classes[] = $screen_size . ':pb-[' . floatval($padding_bottom) . 'rem]';
        }
    }
}
$padding_class_string = !empty($padding_classes) ? ' ' . esc_attr(implode(' ', $padding_classes)) : '';

// -------------------------
// IDs
// -------------------------
$section_id    = 'section-' . wp_generate_uuid4();
$media_wrap_id = $section_id . '-media';
$video_id      = $section_id . '-video';
$iframe_id     = $section_id . '-iframe';
$overlay_id    = $section_id . '-overlay';
$play_btn_id   = $section_id . '-playbtn';

// -------------------------
// Height classes
// -------------------------
$height_classes = 'h-[' . $height_xs . 'px] md:h-[' . $height_md . 'px]';

// -------------------------
// Helpers (guarded to avoid redeclare fatals)
// -------------------------
if (!function_exists('matrix_media_alt_title')) {
    function matrix_media_alt_title($attachment) {
        $out = ['alt' => '', 'title' => ''];
        if (!is_array($attachment)) return $out;
        $id = isset($attachment['ID']) ? (int) $attachment['ID'] : 0;
        if ($id) {
            $out['alt']   = get_post_meta($id, '_wp_attachment_image_alt', true);
            $out['title'] = get_the_title($id);
        } else {
            $out['alt']   = isset($attachment['alt']) ? $attachment['alt'] : '';
            $out['title'] = isset($attachment['title']) ? $attachment['title'] : '';
        }
        if ($out['alt'] === '')   $out['alt']   = 'Media image';
        if ($out['title'] === '') $out['title'] = 'Media image';
        return $out;
    }
}
if (!function_exists('matrix_youtube_embed')) {
    function matrix_youtube_embed($url) {
        if (empty($url)) return '';
        $id = '';
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([\w-]{6,})~i', $url, $m)) {
            $id = $m[1];
        }
        if (!$id) return '';
        return 'https://www.youtube.com/embed/' . rawurlencode($id) . '?enablejsapi=1&playsinline=1&rel=0';
    }
}
if (!function_exists('matrix_vimeo_embed')) {
    function matrix_vimeo_embed($url) {
        if (empty($url)) return '';
        $id = '';
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            $id = $m[1];
        }
        if (!$id) return '';
        return 'https://player.vimeo.com/video/' . rawurlencode($id) . '?title=0&byline=0&portrait=0';
    }
}

// Poster
$poster_url   = '';
$poster_alt   = 'Poster image';
$poster_title = 'Poster image';
if (is_array($poster_image)) {
    $poster_id  = isset($poster_image['ID']) ? (int) $poster_image['ID'] : 0;
    $poster_url = $poster_id ? wp_get_attachment_image_url($poster_id, 'full') : (isset($poster_image['url']) ? $poster_image['url'] : '');
    $pt         = matrix_media_alt_title($poster_image);
    $poster_alt   = $pt['alt'];
    $poster_title = $pt['title'];
}

// Image
$image_url   = '';
$image_alt   = 'Image';
$image_title = 'Image';
if (is_array($image) && $media_type === 'image') {
    $img_id    = isset($image['ID']) ? (int) $image['ID'] : 0;
    $image_url = $img_id ? wp_get_attachment_image_url($img_id, 'full') : (isset($image['url']) ? $image['url'] : '');
    $it        = matrix_media_alt_title($image);
    $image_alt   = $it['alt'];
    $image_title = $it['title'];
}

// Local video URL (if any)
$file_url = '';
$file_mime = 'video/mp4';
if ($video_provider === 'local' && is_array($video_file)) {
    $file_id  = isset($video_file['ID']) ? (int) $video_file['ID'] : 0;
    $file_url = $file_id ? wp_get_attachment_url($file_id) : (isset($video_file['url']) ? $video_file['url'] : '');
    if (!empty($video_file['mime_type'])) $file_mime = $video_file['mime_type'];
}

// Iframe embed URL (+ mute param if needed)
$iframe_src = '';
if ($media_type === 'video' && $video_provider && $video_provider !== 'local' && !empty($video_url)) {
    if ($video_provider === 'youtube')  $iframe_src = matrix_youtube_embed($video_url);
    if ($video_provider === 'vimeo')    $iframe_src = matrix_vimeo_embed($video_url);

    if ($iframe_src && $muted) {
        $iframe_src .= (strpos($iframe_src, '?') !== false ? '&' : '?') . 'mute=1';
    }
}

// Play icon: fixed path (always)
$play_icon_url = content_url('uploads/2026/01/play-circle.png');

// Should we render a playable video?
$has_playable_video = ($media_type === 'video') && (
    ($video_provider === 'local' && $file_url) || ($video_provider !== 'local' && $iframe_src)
);
?>
<section id="<?php echo esc_attr($section_id); ?>" class="relative flex overflow-hidden<?php echo esc_attr($bg_class); ?>">
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-10 lg:py-20 max-lg:px-5<?php echo $padding_class_string; ?>">
        <div id="<?php echo esc_attr($media_wrap_id); ?>" class="relative w-full overflow-hidden <?php echo esc_attr($height_classes); ?>">
            <?php if ($media_type === 'image' && !empty($image_url)) : ?>
                <img
                    src="<?php echo esc_url($image_url); ?>"
                    alt="<?php echo esc_attr($image_alt); ?>"
                    title="<?php echo esc_attr($image_title); ?>"
                    class="object-cover w-full h-full"
                    loading="lazy"
                    decoding="async"
                    fetchpriority="low"
                />
            <?php elseif ($media_type === 'video') : ?>
                <div class="relative inset-0">
                    <?php if ($video_provider === 'local' && $file_url) : ?>
                        <video
                            id="<?php echo esc_attr($video_id); ?>"
                            class="object-cover w-full h-full"
                            preload="metadata"
                            playsinline
                            <?php echo $muted ? 'muted' : ''; ?>
                            <?php echo $poster_url ? 'poster="' . esc_url($poster_url) . '"' : ''; ?>
                        >
                            <source src="<?php echo esc_url($file_url); ?>" type="<?php echo esc_attr($file_mime); ?>" />
                            <?php echo esc_html__('Your browser does not support the video tag.', 'your-textdomain'); ?>
                        </video>
                    <?php elseif (!empty($iframe_src)) : ?>
                        <iframe
                            id="<?php echo esc_attr($iframe_id); ?>"
                            class="w-full h-full"
                            src="<?php echo esc_url($iframe_src); ?>"
                            title="<?php echo esc_attr__('Embedded video', 'your-textdomain'); ?>"
                            allow="autoplay; fullscreen; picture-in-picture"
                            referrerpolicy="no-referrer-when-downgrade"
                            loading="lazy"
                        ></iframe>
                    <?php else : ?>
                        <div class="flex justify-center items-center w-full h-full">
                            <p class="text-sm"><?php echo esc_html__('Please add a valid video.', 'your-textdomain'); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($has_playable_video) : ?>
                        <!-- Overlay (poster + fixed play button) -->
                        <button
                            id="<?php echo esc_attr($overlay_id); ?>"
                            type="button"
                            class="flex absolute inset-0 justify-center items-center focus:outline-none"
                            aria-label="<?php echo esc_attr__('Play video', 'your-textdomain'); ?>"
                            data-play-overlay
                        >
                            <?php if ($poster_url) : ?>
                                <img
                                    src="<?php echo esc_url($poster_url); ?>"
                                    alt="<?php echo esc_attr($poster_alt); ?>"
                                    title="<?php echo esc_attr($poster_title); ?>"
                                    class="object-cover absolute inset-0 w-full h-full"
                                    decoding="async"
                                    loading="lazy"
                                    fetchpriority="low"
                                />
                            <?php endif; ?>

                            <span id="<?php echo esc_attr($play_btn_id); ?>" class="inline-flex relative justify-center items-center">
                                <img src="<?php echo esc_url($play_icon_url); ?>" alt="<?php echo esc_attr__('Play', 'your-textdomain'); ?>" class="w-16 h-16 md:w-20 md:h-20" />
                            </span>
                        </button>
                    <?php endif; ?>
                </div>

<script>
(function(){
  var sectionId  = <?php echo json_encode($section_id); ?>;
  var wrapId     = <?php echo json_encode($media_wrap_id); ?>;
  var overlayId  = <?php echo json_encode($overlay_id); ?>;
  var videoId    = <?php echo json_encode($video_id); ?>;
  var iframeId   = <?php echo json_encode($iframe_id); ?>;
  var provider   = <?php echo json_encode($video_provider ?: ''); ?>;
  var autopause  = <?php echo json_encode($autopause); ?>;
  var startMuted = <?php echo json_encode($muted); ?>;
  var playBtnId  = <?php echo json_encode($play_btn_id); ?>;

  var sectionEl = document.getElementById(sectionId);
  if (!sectionEl || sectionEl.dataset.fwmInit === '1') return;
  sectionEl.dataset.fwmInit = '1';

  var wrap    = document.getElementById(wrapId);
  var overlay = document.getElementById(overlayId);
  var video   = videoId ? document.getElementById(videoId) : null;
  var iframe  = iframeId ? document.getElementById(iframeId) : null;
  var playBtn = document.getElementById(playBtnId);

  var started = false;
  var isPlaying = false;

  function yt(cmd){
    if (iframe && iframe.contentWindow) {
      iframe.contentWindow.postMessage(JSON.stringify({ event:'command', func:cmd, args:[] }), '*');
    }
  }
  function vimeo(method, value){
    if (iframe && iframe.contentWindow) {
      var msg = { method: method };
      if (typeof value !== 'undefined') msg.value = value;
      iframe.contentWindow.postMessage(JSON.stringify(msg), '*');
    }
  }
  function setPlayIconVisible(visible){
    if (playBtn) playBtn.classList.toggle('hidden', !visible);
  }

  function playVideo(){
    if (provider === 'local' && video) {
      try { video.play(); } catch(e){}
    } else if (provider === 'youtube') {
      yt('playVideo');
    } else if (provider === 'vimeo') {
      vimeo('play');
    }
    isPlaying = true;
    setPlayIconVisible(false);
  }

  function pauseVideo(){
    if (provider === 'local' && video) {
      try { video.pause(); } catch(e){}
    } else if (provider === 'youtube') {
      yt('pauseVideo');
    } else if (provider === 'vimeo') {
      vimeo('pause');
    }
    isPlaying = false;
    setPlayIconVisible(true);
  }

  function muteVideo(){
    if (provider === 'local' && video) {
      try { video.muted = true; } catch(e){}
    } else if (provider === 'youtube') {
      yt('mute');
    } else if (provider === 'vimeo') {
      vimeo('setVolume', 0);
    }
  }

  // Single unified handler: first click removes poster & optional mute, every click toggles play/pause
  function onOverlayClick(e){
    e.preventDefault(); e.stopPropagation();

    if (!started) {
      var poster = overlay ? overlay.querySelector('img') : null;
      if (poster) poster.style.display = 'none';  // hide poster
      if (overlay) overlay.style.background = 'transparent'; // keep overlay to catch toggles
      if (startMuted) muteVideo();
      started = true;
    }

    if (isPlaying) pauseVideo(); else playVideo();
  }

  if (overlay) overlay.addEventListener('click', onOverlayClick);

  // Auto-pause on scroll
  if (autopause && ('IntersectionObserver' in window)) {
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (!entry.isIntersecting && isPlaying) pauseVideo();
      });
    }, { root: null, threshold: 0.15 });
    if (wrap) io.observe(wrap);
  }
})();
</script>
            <?php endif; ?>
        </div>
    </div>
</section>
