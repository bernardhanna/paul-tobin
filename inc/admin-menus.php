<?php
/**
 * Admin menu tweaks (sidebar).
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove the Comments top-level item from the admin sidebar.
 */
function matrix_starter_remove_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'matrix_starter_remove_comments_admin_menu', 999);
