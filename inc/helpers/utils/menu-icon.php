<?php
/**
 * inc/helpers/utils/menu-icon.php
 *
 * Native (non-ACF) icon field for nav menu items + helpers used by the dropdown.
 * - Shows an image selector in Appearance → Menus (per menu item).
 * - Stores the attachment ID in post meta: _menu_item_icon_id
 * - Provides template helpers (id/desc/icon).
 */

if (!defined('ABSPATH')) exit;

/** Meta key for storing the attachment ID */
if (!defined('MYTHEME_MENU_ICON_META')) {
    define('MYTHEME_MENU_ICON_META', '_menu_item_icon_id');
}

/* ==========================================================================
 * Helpers (safe to call from templates)
 * ========================================================================== */

/**
 * Get a menu item ID regardless of object shape (Navi ->id/->ID or WP object).
 */
if (!function_exists('mytheme_menu_item_id')) {
    function mytheme_menu_item_id($obj): int {
        if (is_object($obj)) {
            if (isset($obj->id)) return (int) $obj->id;
            if (isset($obj->ID)) return (int) $obj->ID;
        }
        return 0;
    }
}

/**
 * Get the description for a WP nav menu item.
 * Uses the menu “Description” field if present; falls back to post_content.
 */
if (!function_exists('mytheme_menu_item_desc')) {
    function mytheme_menu_item_desc($menu_item, int $menu_item_id): string {
        if (!empty($menu_item->description)) {
            return trim((string) $menu_item->description);
        }
        $fallback = get_post_field('post_content', $menu_item_id);
        return trim((string) $fallback);
    }
}

/**
 * Get the icon for a menu item as an array: [id, url, alt, title].
 * Returns null if no icon is set.
 */
if (!function_exists('mytheme_menu_item_icon')) {
    function mytheme_menu_item_icon(int $menu_item_id): ?array {
        if ($menu_item_id <= 0) return null;

        $att_id = (int) get_post_meta($menu_item_id, MYTHEME_MENU_ICON_META, true);
        if (!$att_id) return null;

        // Prefer thumbnail size for menu UI; templates can swap to another if needed
        $src = wp_get_attachment_image_src($att_id, 'thumbnail');
        $url = $src ? $src[0] : '';
        if (!$url) return null;

        $alt   = get_post_meta($att_id, '_wp_attachment_image_alt', true);
        $title = get_the_title($att_id);

        return [
            'id'    => $att_id,
            'url'   => $url,
            'alt'   => $alt !== '' ? $alt : ($title ?: ''),
            'title' => $title ?: '',
        ];
    }
}

/* ==========================================================================
 * Admin UI: add/select/remove icon in Appearance → Menus
 * ========================================================================== */

/**
 * Add the custom field inside each menu item box.
 */
add_action('wp_nav_menu_item_custom_fields', function ($item_id, $item, $depth, $args) {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $att_id = (int) get_post_meta($item_id, MYTHEME_MENU_ICON_META, true);
    $thumb  = $att_id ? wp_get_attachment_image_src($att_id, 'thumbnail') : null;
    $url    = $thumb ? $thumb[0] : '';
    $alt    = $att_id ? get_post_meta($att_id, '_wp_attachment_image_alt', true) : '';
    $title  = $att_id ? get_the_title($att_id) : '';

    $field_id   = "edit-menu-item-icon-{$item_id}";
    $field_name = "menu_item_icon_id[{$item_id}]";
    ?>
    <div class="field-custom description-wide" style="margin:12px 0;">
        <span class="description"><?php esc_html_e('Menu Item Icon', 'mytheme'); ?></span>

        <div class="menu-item-icon-field" data-item-id="<?php echo esc_attr($item_id); ?>">
            <div class="menu-item-icon-preview" style="margin:6px 0;">
                <?php if ($url): ?>
                    <img src="<?php echo esc_url($url); ?>"
                         alt="<?php echo esc_attr($alt ?: $title ?: ''); ?>"
                         style="display:block;max-width:64px;height:auto;border:1px solid #ccd0d4;border-radius:2px;padding:2px;background:#fff;" />
                <?php else: ?>
                    <span class="no-icon" style="display:inline-block;color:#666;">
                        <?php esc_html_e('No icon selected', 'mytheme'); ?>
                    </span>
                <?php endif; ?>
            </div>

            <input type="hidden"
                   id="<?php echo esc_attr($field_id); ?>"
                   name="<?php echo esc_attr($field_name); ?>"
                   value="<?php echo esc_attr($att_id ?: 0); ?>" />

            <button type="button"
                    class="button select-menu-item-icon"
                    data-target="#<?php echo esc_attr($field_id); ?>">
                <?php echo $url ? esc_html__('Change Icon', 'mytheme') : esc_html__('Select Icon', 'mytheme'); ?>
            </button>

            <button type="button"
                    class="button link-delete remove-menu-item-icon"
                    data-target="#<?php echo esc_attr($field_id); ?>"
                    style="margin-left:6px;<?php echo $url ? '' : 'display:none;'; ?>">
                <?php esc_html_e('Remove', 'mytheme'); ?>
            </button>
        </div>
    </div>
    <?php
}, 10, 4);

/**
 * Save attachment ID when the menu is updated.
 */
add_action('wp_update_nav_menu_item', function ($menu_id, $menu_item_db_id, $args) {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    if (isset($_POST['menu_item_icon_id'][$menu_item_db_id])) {
        $att_id = (int) $_POST['menu_item_icon_id'][$menu_item_db_id];
        if ($att_id > 0) {
            update_post_meta($menu_item_db_id, MYTHEME_MENU_ICON_META, $att_id);
        } else {
            delete_post_meta($menu_item_db_id, MYTHEME_MENU_ICON_META);
        }
    }
}, 10, 3);

/**
 * Enqueue media frame & inline script on the Menus screen.
 * Use NOWDOC to avoid PHP trying to interpolate $ variables inside JS.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'nav-menus.php') return;

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    $js = <<<'JS'
(function($){
    function setupMenuIconField($wrap){
        var $hidden  = $wrap.find('input[type="hidden"]');
        var $preview = $wrap.find('.menu-item-icon-preview');
        var $select  = $wrap.find('.select-menu-item-icon');
        var $remove  = $wrap.find('.remove-menu-item-icon');
        var frame;

        $select.on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }

            frame = wp.media({
                title: 'Select Icon',
                button: { text: 'Use this icon' },
                multiple: false,
                library: { type: ['image'] }
            });

            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $hidden.val(attachment.id);
                var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
                $preview.html('<img src="'+url+'" style="display:block;max-width:64px;height:auto;border:1px solid #ccd0d4;border-radius:2px;padding:2px;background:#fff;" />');
                $remove.show();
                $select.text('Change Icon');
            });

            frame.open();
        });

        $remove.on('click', function(e){
            e.preventDefault();
            $hidden.val('0');
            $preview.html('<span class="no-icon" style="display:inline-block;color:#666;">No icon selected</span>');
            $remove.hide();
            $select.text('Select Icon');
        });
    }

    $(document).ready(function(){
        $('.menu-item-icon-field').each(function(){ setupMenuIconField($(this)); });

        // When WP adds menu items via AJAX, re-bind
        $(document).on('menu-item-added', function(e, menuItem){
            $(menuItem).find('.menu-item-icon-field').each(function(){
                setupMenuIconField($(this));
            });
        });
    });
})(jQuery);
JS;

    // Attach inline JS after 'jquery' so $ is defined.
    wp_add_inline_script('jquery', $js);
});
