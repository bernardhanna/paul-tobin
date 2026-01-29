<?php
/**
 * Frontend: Large Hero Image
 * Notes:
 * - No design options per request.
 * - Uses get_sub_field() exclusively.
 * - Includes required section/div structure and padding repeater controls.
 * - Image alt/title pulled from media with fallbacks.
 * - Random section ID on wrapper.
 */

// -------------------------
// Fetch fields (get_sub_field ONLY)
// -------------------------
$hero_image   = get_sub_field('hero_image');
$height_xs    = (int) get_sub_field('height_xs');
$height_md    = (int) get_sub_field('height_md');

// Fallback heights (if not set)
if ($height_xs <= 0) {
    $height_xs = 500;
}
if ($height_md <= 0) {
    $height_md = 665;
}

// -------------------------
// Image attributes & fallbacks
// -------------------------
$image_url   = '';
$image_id    = 0;
$image_alt   = '';
$image_title = '';

if (is_array($hero_image)) {
    $image_id  = isset($hero_image['ID']) ? (int) $hero_image['ID'] : 0;
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : (isset($hero_image['url']) ? $hero_image['url'] : '');
}

if ($image_id > 0) {
    $image_alt   = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $image_title = get_the_title($image_id);
}

if (empty($image_alt)) {
    $image_alt = 'Hero image';
}
if (empty($image_title)) {
    $image_title = 'Hero image';
}

// -------------------------
// Random section ID
// -------------------------
$section_id = 'section-' . wp_generate_uuid4();

// -------------------------
// Height classes (Tailwind arbitrary values)
// -------------------------
$height_classes = 'h-[' . $height_xs . 'px] md:h-[' . $height_md . 'px]';

// -------------------------
// Output
// -------------------------
?>
<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative w-full">
        <?php if (!empty($image_url)) : ?>
            <img
                src="<?php echo esc_url($image_url); ?>"
                alt="<?php echo esc_attr($image_alt); ?>"
                title="<?php echo esc_attr($image_title); ?>"
                class="w-full <?php echo esc_attr($height_classes); ?> object-cover"
                decoding="async"
                loading="lazy"
                fetchpriority="low"
            />
        <?php endif; ?>
</section>
