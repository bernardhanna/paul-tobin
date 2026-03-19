<?php
declare(strict_types=1);

$token = isset($_GET['token']) ? (string) $_GET['token'] : '';
if ($token !== 'matrix-find-locations') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__, 3) . '/wp-load.php';

$pages = get_posts([
    'post_type'      => 'page',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
]);

header('Content-Type: text/plain; charset=utf-8');

foreach ($pages as $page_id) {
    $slug = get_post_field('post_name', $page_id);
    $title = get_the_title($page_id);

    $has_flexi = have_rows('flexible_content_blocks', $page_id);
    if (!$has_flexi) {
        continue;
    }

    $layouts = get_field('flexible_content_blocks', $page_id);
    if (!is_array($layouts)) {
        continue;
    }

    $found = [];
    foreach ($layouts as $row) {
        $layout = isset($row['acf_fc_layout']) ? (string) $row['acf_fc_layout'] : '';
        if ($layout === 'booking_form' || $layout === 'locations_find_us') {
            $found[] = $layout;
        }
    }

    if (!empty($found)) {
        echo "ID {$page_id} | /{$slug}/ | {$title} | " . implode(',', array_unique($found)) . "\n";
    }
}
