<?php
/**
 * Author Section – Design-matched, with Team featured image > Gravatar fallback
 */

if (!defined('ABSPATH')) exit;

$author_id      = (int) get_post_field('post_author', get_the_ID());
$author_name    = get_the_author_meta('display_name', $author_id);
$post_permalink = get_permalink();
$post_title     = get_the_title();

// Attempt to find Team post where Title === Author Display Name
$team_post      = null;
$team_link      = '';
$profile_img    = ''; // final URL we will render

$team_q = new WP_Query([
    'post_type'           => 'team',
    'posts_per_page'      => 1,
    'post_status'         => 'publish',
    'title'               => $author_name, // exact match param
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
]);

if ($team_q->have_posts()) {
    $team_q->the_post();
    $team_post = get_post(get_the_ID());
    $team_link = get_permalink($team_post->ID);

    // Try team featured image first
    $thumb_id = get_post_thumbnail_id($team_post->ID);
    if ($thumb_id) {
        $profile_img = wp_get_attachment_image_url($thumb_id, 'medium_large');
    }
}
wp_reset_postdata();

// Fallbacks
if (empty($team_link)) {
    $team_link = get_author_posts_url($author_id);
}
if (empty($profile_img)) {
    // If no team featured image (or no team match), use author avatar/gravatar
    $profile_img = get_avatar_url($author_id, ['size' => 200]);
}

// Last update date
$modified_ts   = get_post_modified_time('U', true);
$datetime_attr = gmdate('Y-m-d', $modified_ts);
$human_date    = date_i18n('j F Y', $modified_ts);

// Share URLs
$share_url   = rawurlencode($post_permalink);
$share_title = rawurlencode(wp_strip_all_tags($post_title));

$share_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url;
$share_twitter  = 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_title;
$share_linkedin = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $share_url;
?>

<section class="flex overflow-hidden relative bg-[#ededed]">
  <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
    <div class="flex flex-wrap gap-10 justify-between items-center w-full text-black">
      <figure class="flex gap-8 items-center min-w-60 max-md:max-w-full">
        <a href="<?php echo esc_url($team_link); ?>" class="block" aria-label="<?php echo esc_attr($author_name); ?>">
          <img
            src="<?php echo esc_url($profile_img); ?>"
            alt="<?php echo esc_attr('Profile photo of ' . $author_name); ?>"
            class="object-cover shrink-0 aspect-square rounded-full w-[140px] h-[140px]"
            loading="lazy"
            decoding="async"
          />
        </a>
        <div class="flex flex-col justify-center min-w-60">
          <span class="text-2xl font-semibold leading-none text-black text-seconary">
            <a href="<?php echo esc_url($team_link); ?>" class="hover:opacity-80">
              <?php echo esc_html(sprintf('by %s', $author_name)); ?>
            </a>
          </span>
          <time class="mt-2 text-lg leading-none text-black" datetime="<?php echo esc_attr($datetime_attr); ?>">
            <?php echo esc_html(sprintf(__('Last update: %s', 'matrix-starter'), $human_date)); ?>
          </time>
        </div>
      </figure>

      <div class="flex gap-4 items-center text-lg leading-none min-w-60" role="group" aria-label="<?php esc_attr_e('Share this post', 'matrix-starter'); ?>">
        <span class="text-black"><?php esc_html_e('Share this post', 'matrix-starter'); ?></span>

        <a
          href="<?php echo esc_url($share_facebook); ?>"
          class="whitespace-nowrap btn w-fit hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:opacity-70"
          aria-label="<?php esc_attr_e('Share on Facebook', 'matrix-starter'); ?>"
          target="_blank" rel="noopener"
        >
          <svg class="object-contain w-8 h-8 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M22 12.06C22 6.48 17.52 2 11.94 2 6.36 2 1.88 6.48 1.88 12.06c0 4.99 3.65 9.13 8.43 9.94v-7.03H7.93v-2.91h2.38V9.41c0-2.35 1.4-3.65 3.55-3.65 1.03 0 2.1.18 2.1.18v2.3h-1.18c-1.16 0-1.52.72-1.52 1.46v1.77h2.58l-.41 2.91h-2.17V22c4.78-.81 8.43-4.95 8.43-9.94Z"/>
          </svg>
        </a>

        <a
          href="<?php echo esc_url($share_twitter); ?>"
          class="whitespace-nowrap btn w-fit hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:opacity-70"
          aria-label="<?php esc_attr_e('Share on X (Twitter)', 'matrix-starter'); ?>"
          target="_blank" rel="noopener"
        >
<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 18 18" fill="none">
<mask id="mask0_895_324" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="2" y="2" width="14" height="14">
<path d="M15.25 2.75V15.25H2.75V2.75H15.25Z" fill="white" stroke="white"/>
</mask>
<g mask="url(#mask0_895_324)">
<path d="M13.8516 3.38257L10.0527 7.73608L9.78516 8.04175L10.0303 8.36597L14.7461 14.6169H11.8311L8.7168 10.5378L8.34473 10.0505L7.94238 10.5125L4.36133 14.6169H3.61523L7.72852 9.90112L7.99707 9.59351L7.75 9.26929L3.26074 3.38354H6.27246L9.06934 7.09253L9.44043 7.58472L9.8457 7.12085L13.1084 3.38257H13.8516ZM4.2666 4.36304L11.7559 14.1785L11.9062 14.3757H14.3047L13.7002 13.574L6.29395 3.75854L6.14453 3.5603H3.6543L4.2666 4.36304Z" fill="#020617" stroke="#0A1119"/>
</g>
</svg>
        </a>

        <a
          href="<?php echo esc_url($share_linkedin); ?>"
          class="whitespace-nowrap btn w-fit hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:opacity-70"
          aria-label="<?php esc_attr_e('Share on LinkedIn', 'matrix-starter'); ?>"
          target="_blank" rel="noopener"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 18 18" fill="none">
            <path d="M12.4502 7.33887C13.6967 7.33892 14.3319 7.67001 14.6934 8.17383C15.0861 8.72126 15.25 9.59212 15.25 10.8916V15.25H13.5498V11.4893C13.5498 10.7202 13.4606 10.0373 13.1992 9.53125C13.0641 9.26967 12.8763 9.04361 12.623 8.88477C12.3685 8.72512 12.0784 8.65332 11.7695 8.65332C10.9983 8.65332 10.3839 8.93703 9.9834 9.49316C9.60385 10.0204 9.4649 10.7268 9.46484 11.4883V15.25H7.76465V7.4541H9.27148V8.20312L10.2178 8.42773C10.4791 7.90806 11.2123 7.33887 12.4502 7.33887ZM4.83496 7.33887V15.1348H3.13477V7.33887H4.83496ZM3.98535 2.75C4.31409 2.75 4.62908 2.87965 4.86133 3.10938C5.09251 3.33816 5.22168 3.64829 5.22168 3.97168C5.22172 4.13164 5.18959 4.28991 5.12793 4.4375C5.06622 4.58518 4.97623 4.71962 4.8623 4.83203L4.86035 4.83301C4.74537 4.94738 4.60899 5.03796 4.45898 5.09961C4.30901 5.16123 4.14847 5.19284 3.98633 5.19238C3.69897 5.19174 3.42179 5.0922 3.2002 4.91309L3.1084 4.83203C2.99537 4.71919 2.90524 4.58492 2.84375 4.4375C2.78225 4.2899 2.75052 4.13158 2.75 3.97168C2.75 3.64829 2.87917 3.33816 3.11035 3.10938C3.3141 2.90763 3.58035 2.78345 3.86328 2.75586L3.98535 2.75Z" fill="#020617" stroke="#0A1119"/>
</svg>
        </a>
      </div>
    </div>
  </div>
</section>
