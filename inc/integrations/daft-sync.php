<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'matrix_daft_register_admin_page');
add_action('admin_post_matrix_daft_sync', 'matrix_daft_handle_manual_sync');
add_action('init', 'matrix_daft_register_cron');
add_action('matrix_daft_daily_sync', 'matrix_daft_run_daily_sync');
add_filter('manage_property_posts_columns', 'matrix_daft_add_source_column');
add_action('manage_property_posts_custom_column', 'matrix_daft_render_source_column', 10, 2);
add_action('add_meta_boxes_property', 'matrix_daft_register_property_sync_metabox');
add_action('save_post_property', 'matrix_daft_save_property_sync_metabox', 10, 2);
add_filter('bulk_actions-edit-property', 'matrix_daft_register_bulk_actions');
add_filter('handle_bulk_actions-edit-property', 'matrix_daft_handle_bulk_actions', 10, 3);
add_action('admin_notices', 'matrix_daft_render_bulk_action_notice');

function matrix_daft_get_option(string $name, $default = '') {
    if (function_exists('get_field')) {
        $value = get_field($name, 'option');
        if ($value !== null && $value !== false && $value !== '') {
            return $value;
        }
    }

    return get_option($name, $default);
}

function matrix_daft_get_selected_soap_operations(): array {
    $raw = matrix_daft_get_option('daft_soap_operation', ['search_sale']);
    if (is_string($raw) && trim($raw) === 'search_sale') {
        $raw = ['search_sale', 'search_rental'];
    } elseif (is_string($raw) && $raw !== '') {
        $raw = [$raw];
    }
    if (!is_array($raw)) {
        $raw = ['search_sale'];
    }

    $allowed = [
        'search_sale',
        'search_rental',
        'search_commercial',
        'search_new_development',
        'search_shortterm',
        'search_sharing',
        'search_parking',
    ];

    $ops = [];
    foreach ($raw as $op) {
        $op = trim((string) $op);
        if ($op !== '' && in_array($op, $allowed, true)) {
            $ops[] = $op;
        }
    }

    if (empty($ops)) {
        $ops[] = 'search_sale';
    }

    return array_values(array_unique($ops));
}

function matrix_daft_merge_operation_item_groups(array $operation_item_groups): array {
    $merged = [];
    $group_count = count($operation_item_groups);
    if ($group_count === 0) {
        return $merged;
    }

    $indexes = array_fill(0, $group_count, 0);
    do {
        $added_in_pass = false;
        foreach ($operation_item_groups as $group_index => $group_items) {
            $item_index = $indexes[$group_index] ?? 0;
            if (!isset($group_items[$item_index])) {
                continue;
            }
            $merged[] = $group_items[$item_index];
            $indexes[$group_index] = $item_index + 1;
            $added_in_pass = true;
        }
    } while ($added_in_pass);

    return $merged;
}

function matrix_daft_register_admin_page(): void {
    add_submenu_page(
        'edit.php?post_type=property',
        'Daft Import',
        'Daft Import',
        'edit_posts',
        'matrix-daft-import',
        'matrix_daft_render_admin_page'
    );
}

function matrix_daft_register_bulk_actions(array $bulk_actions): array {
    $bulk_actions['matrix_daft_disable_sync'] = 'Disable Daft sync (selected)';
    $bulk_actions['matrix_daft_enable_sync'] = 'Enable Daft sync (selected)';
    $bulk_actions['matrix_daft_disable_image_sync'] = 'Disable Daft image sync (selected)';
    $bulk_actions['matrix_daft_enable_image_sync'] = 'Enable Daft image sync (selected)';
    return $bulk_actions;
}

function matrix_daft_handle_bulk_actions(string $redirect_to, string $doaction, array $post_ids): string {
    $supported = [
        'matrix_daft_disable_sync',
        'matrix_daft_enable_sync',
        'matrix_daft_disable_image_sync',
        'matrix_daft_enable_image_sync',
    ];
    if (!in_array($doaction, $supported, true)) {
        return $redirect_to;
    }

    $updated = 0;
    foreach ($post_ids as $post_id) {
        $post_id = (int) $post_id;
        if ($post_id <= 0 || get_post_type($post_id) !== 'property') {
            continue;
        }
        if (!current_user_can('edit_post', $post_id)) {
            continue;
        }

        if ($doaction === 'matrix_daft_disable_sync') {
            update_post_meta($post_id, '_matrix_daft_disable_sync', 1);
            $updated++;
        } elseif ($doaction === 'matrix_daft_enable_sync') {
            delete_post_meta($post_id, '_matrix_daft_disable_sync');
            $updated++;
        } elseif ($doaction === 'matrix_daft_disable_image_sync') {
            update_post_meta($post_id, '_matrix_daft_disable_image_sync', 1);
            $updated++;
        } elseif ($doaction === 'matrix_daft_enable_image_sync') {
            delete_post_meta($post_id, '_matrix_daft_disable_image_sync');
            $updated++;
        }
    }

    return add_query_arg([
        'matrix_daft_bulk_action' => $doaction,
        'matrix_daft_bulk_updated' => $updated,
    ], $redirect_to);
}

function matrix_daft_render_bulk_action_notice(): void {
    if (!is_admin()) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-property') {
        return;
    }
    $updated = isset($_GET['matrix_daft_bulk_updated']) ? (int) $_GET['matrix_daft_bulk_updated'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $action = isset($_GET['matrix_daft_bulk_action']) ? sanitize_text_field((string) $_GET['matrix_daft_bulk_action']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($updated <= 0 || $action === '') {
        return;
    }

    $label = 'Updated Daft sync settings';
    if ($action === 'matrix_daft_disable_sync') {
        $label = 'Disabled Daft sync';
    } elseif ($action === 'matrix_daft_enable_sync') {
        $label = 'Enabled Daft sync';
    } elseif ($action === 'matrix_daft_disable_image_sync') {
        $label = 'Disabled Daft image sync';
    } elseif ($action === 'matrix_daft_enable_image_sync') {
        $label = 'Enabled Daft image sync';
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($label . ' for ' . $updated . ' propert' . ($updated === 1 ? 'y' : 'ies') . '.') . '</p></div>';
}

function matrix_daft_render_admin_page(): void {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to access this page.');
    }

    $report = get_option('matrix_daft_last_report');
    $last_synced = !empty($report['synced_at']) ? $report['synced_at'] : '';
    $request_mode = (string) matrix_daft_get_option('daft_request_mode', 'mock');
    $configured_endpoint = (string) matrix_daft_get_option('daft_endpoint_url', '');
    $configured_method = strtoupper((string) matrix_daft_get_option('daft_http_method', 'GET'));
    $soap_wsdl_url = (string) matrix_daft_get_option('daft_soap_wsdl_url', 'http://api.daft.ie/v2/wsdl.xml');
    $soap_operations = matrix_daft_get_selected_soap_operations();
    $soap_operation = $soap_operations[0] ?? 'search_sale';
    $diagnostics = matrix_daft_get_connectivity_diagnostics($soap_wsdl_url);
    $known_ips_raw = (string) matrix_daft_get_option('daft_known_server_ips', "164.92.198.42");
    $known_ips = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $known_ips_raw))));
    $support_message = matrix_daft_build_support_message($diagnostics, $known_ips, $soap_wsdl_url);
    $properties_list_url = admin_url('edit.php?post_type=property');
    $theme_options_url = admin_url('admin.php?page=theme-options');
    ?>
    <div class="wrap">
        <h1>Daft Property Import</h1>
        <p>Sync Daft listings into your <strong>Property</strong> posts and manage per-property sync controls.</p>

        <?php if (!empty($_GET['daft_sync'])) : ?>
            <div class="notice notice-<?php echo esc_attr(!empty($_GET['success']) ? 'success' : 'error'); ?> is-dismissible">
                <p><?php echo esc_html(wp_unslash($_GET['message'] ?? 'Sync finished.')); ?></p>
            </div>
        <?php endif; ?>

        <div style="background:#fff;border:1px solid #dcdcde;padding:14px 16px;margin:12px 0 16px 0;max-width:980px;">
            <p style="margin:0 0 8px 0;"><strong>Current Request Mode:</strong>
                <?php if ($request_mode === 'mock') : ?>
                    Mock payload mode (local development)
                <?php elseif ($request_mode === 'soap') : ?>
                    SOAP `<?php echo esc_html(implode(', ', $soap_operations)); ?>` via <?php echo esc_html($soap_wsdl_url); ?>
                <?php else : ?>
                    REST <?php echo $configured_endpoint !== '' ? esc_html($configured_endpoint) : 'Not set'; ?> (<?php echo esc_html($configured_method); ?>)
                <?php endif; ?>
            </p>
            <p style="margin:0;">
                <a class="button button-secondary" href="<?php echo esc_url($properties_list_url); ?>">Open Properties</a>
                <a class="button button-secondary" href="<?php echo esc_url($theme_options_url); ?>" style="margin-left:6px;">Open Theme Options (Daft tab)</a>
            </p>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="matrix_daft_sync">
            <?php wp_nonce_field('matrix_daft_sync_now', 'matrix_daft_nonce'); ?>
            <?php submit_button('Sync Daft Listings Now', 'primary', 'submit', false); ?>
        </form>

        <details style="max-width:980px;margin:0 0 14px 0;">
            <summary style="cursor:pointer;font-weight:600;">Connection Diagnostics</summary>
            <div style="background:#fff;border:1px solid #dcdcde;border-top:none;padding:14px 16px;">
                <p>Use this to share exact connectivity details with Daft support for allowlisting.</p>
                <p><strong>Detected Outbound Public IP:</strong> <?php echo esc_html($diagnostics['public_ip'] ?: 'Unavailable'); ?></p>
                <p><strong>Known Server IPs:</strong> <?php echo !empty($known_ips) ? esc_html(implode(', ', $known_ips)) : 'None set'; ?></p>
                <p><strong>WSDL Check:</strong> HTTP <?php echo esc_html((string) $diagnostics['wsdl_status']); ?><?php if (!empty($diagnostics['wsdl_cf_mitigated'])) : ?> (cf-mitigated: <?php echo esc_html($diagnostics['wsdl_cf_mitigated']); ?>)<?php endif; ?></p>
                <p><strong>Docs Check:</strong> HTTP <?php echo esc_html((string) $diagnostics['doc_status']); ?><?php if (!empty($diagnostics['doc_cf_mitigated'])) : ?> (cf-mitigated: <?php echo esc_html($diagnostics['doc_cf_mitigated']); ?>)<?php endif; ?></p>
                <p><strong>Suggested Next Step:</strong> Ask Daft to allowlist your staging/prod/live server IPs. Local machine IPs are typically dynamic, so route local testing through an allowlisted server or tunnel/proxy with a stable egress IP.</p>
                <p><strong>Support Message Template:</strong></p>
                <textarea readonly rows="9" style="width:100%;max-width:920px;"><?php echo esc_textarea($support_message); ?></textarea>
            </div>
        </details>

        <details style="max-width:980px;" <?php echo !empty($last_synced) ? 'open' : ''; ?>>
            <summary style="cursor:pointer;font-weight:600;">Last Sync Report</summary>
            <div style="background:#fff;border:1px solid #dcdcde;border-top:none;padding:14px 16px;">
                <?php if (empty($last_synced)) : ?>
                    <p style="margin:0;">No sync has been run yet.</p>
                <?php else : ?>
                    <p><strong>Synced At:</strong> <?php echo esc_html($last_synced); ?></p>
                    <p><strong>Trigger:</strong> <?php echo esc_html($report['trigger'] ?? 'manual'); ?></p>
                    <p><strong>Imported:</strong> <?php echo esc_html((string) ($report['imported'] ?? 0)); ?></p>
                    <p><strong>Updated:</strong> <?php echo esc_html((string) ($report['updated'] ?? 0)); ?></p>
                    <p><strong>Skipped:</strong> <?php echo esc_html((string) ($report['skipped'] ?? 0)); ?></p>
                    <?php if (!empty($report['items']) && is_array($report['items'])) : ?>
                        <p><strong>Last Synced Items:</strong></p>
                        <ul style="list-style:disc;margin-left:1.25rem;">
                            <?php foreach ($report['items'] as $item) : ?>
                                <li>
                                    <?php
                                    $item_action = (string) ($item['action'] ?? 'updated');
                                    $item_title = (string) ($item['title'] ?? '(untitled)');
                                    $item_id = (int) ($item['post_id'] ?? 0);
                                    $item_daft_id = (string) ($item['daft_id'] ?? '');
                                    $status_map = [
                                        'source_operation' => 'Source Operation',
                                        'media_sync' => 'Media',
                                        'media_enrich' => 'Media Enrich',
                                        'media_image_count' => 'Media Image Count',
                                        'media_call_shape' => 'Media Call Shape',
                                        'media_ad_type' => 'Media Ad Type',
                                        'content_one_sync' => 'Content One',
                                        'gallery_sync' => 'Gallery',
                                        'content_one_followup_sync' => 'Content One Follow-up',
                                        'gallery_carousel_sync' => 'Gallery Carousel',
                                        'media_secondary_sync' => 'Media Secondary',
                                        'cta_sync' => 'CTA',
                                    ];
                                    $badges = [];
                                    foreach ($status_map as $status_key => $status_label) {
                                        $status_value = (string) ($item[$status_key] ?? '');
                                        if ($status_value === '') {
                                            continue;
                                        }
                                        $badges[] = matrix_daft_render_status_badge($status_label, $status_value);
                                    }
                                    echo esc_html(ucfirst($item_action) . ': ');
                                    if ($item_id > 0) {
                                        $edit_url = get_edit_post_link($item_id, '');
                                        $view_url = get_permalink($item_id);
                                        echo '<a href="' . esc_url($edit_url ?: '#') . '">' . esc_html($item_title) . '</a>';
                                        echo ' (Post ID ' . esc_html((string) $item_id) . ')';
                                        if (!empty($view_url)) {
                                            echo ' - <a href="' . esc_url($view_url) . '" target="_blank" rel="noopener">View</a>';
                                        }
                                    } else {
                                        echo esc_html($item_title);
                                    }
                                    if ($item_daft_id !== '') {
                                        echo ' <span style="color:#646970;">(' . esc_html('Daft ID ' . $item_daft_id) . ')</span>';
                                    }
                                    if (!empty($badges)) {
                                        echo '<div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">' . wp_kses(
                                            implode('', $badges),
                                            [
                                                'span' => [
                                                    'style' => [],
                                                ],
                                            ]
                                        ) . '</div>';
                                    }
                                    if (!empty($item['media_error'])) {
                                        $media_error = (string) $item['media_error'];
                                        if (function_exists('mb_substr')) {
                                            $media_error = mb_substr($media_error, 0, 400);
                                        } else {
                                            $media_error = substr($media_error, 0, 400);
                                        }
                                        echo '<details style="margin-top:6px;"><summary style="cursor:pointer;">Media debug</summary><pre style="margin-top:6px;padding:8px;background:#f6f7f7;border:1px solid #dcdcde;white-space:pre-wrap;">' . esc_html($media_error) . '</pre></details>';
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($report['error'])) : ?>
                        <p><strong>Error:</strong> <?php echo esc_html($report['error']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($report['debug']) && is_array($report['debug'])) : ?>
                        <details style="margin-top:8px;">
                            <summary style="cursor:pointer;"><?php echo !empty($report['error']) ? 'Technical Error Details' : 'Sync Diagnostics'; ?></summary>
                            <pre style="margin-top:8px;padding:10px;background:#f6f7f7;border:1px solid #dcdcde;white-space:pre-wrap;word-break:break-word;"><?php echo esc_html(wp_json_encode($report['debug'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </details>
    </div>
    <?php
}

function matrix_daft_render_status_badge(string $label, string $status): string {
    $status_lc = strtolower($status);

    $bg = '#eef2ff';
    $fg = '#1e3a8a';
    if (str_contains($status_lc, 'added') || str_contains($status_lc, 'updated') || str_contains($status_lc, 'imported')) {
        $bg = '#ecfdf3';
        $fg = '#166534';
    } elseif (str_contains($status_lc, 'skipped') || str_contains($status_lc, 'removed') || str_contains($status_lc, 'disabled')) {
        $bg = '#fffbeb';
        $fg = '#92400e';
    } elseif (str_contains($status_lc, 'error') || str_contains($status_lc, 'failed')) {
        $bg = '#fef2f2';
        $fg = '#991b1b';
    }

    return sprintf(
        '<span style="display:inline-block;background:%s;color:%s;border:1px solid rgba(0,0,0,.08);border-radius:999px;padding:2px 8px;font-size:11px;line-height:16px;">%s: %s</span>',
        esc_attr($bg),
        esc_attr($fg),
        esc_html($label),
        esc_html($status)
    );
}

function matrix_daft_get_connectivity_diagnostics(string $wsdl_url): array {
    $cache_key = 'matrix_daft_connectivity_diag';
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $diag = [
        'public_ip' => '',
        'wsdl_status' => 0,
        'wsdl_cf_mitigated' => '',
        'doc_status' => 0,
        'doc_cf_mitigated' => '',
    ];

    $ip_resp = wp_remote_get('https://api.ipify.org?format=json', ['timeout' => 10]);
    if (!is_wp_error($ip_resp) && (int) wp_remote_retrieve_response_code($ip_resp) === 200) {
        $ip_body = json_decode((string) wp_remote_retrieve_body($ip_resp), true);
        if (is_array($ip_body) && !empty($ip_body['ip'])) {
            $diag['public_ip'] = sanitize_text_field((string) $ip_body['ip']);
        }
    }

    $wsdl_resp = wp_remote_get($wsdl_url, ['timeout' => 15]);
    if (!is_wp_error($wsdl_resp)) {
        $diag['wsdl_status'] = (int) wp_remote_retrieve_response_code($wsdl_resp);
        $headers = wp_remote_retrieve_headers($wsdl_resp);
        $diag['wsdl_cf_mitigated'] = sanitize_text_field((string) ($headers['cf-mitigated'] ?? ''));
    }

    $doc_resp = wp_remote_get('https://api.daft.ie/doc/v3/', ['timeout' => 15]);
    if (!is_wp_error($doc_resp)) {
        $diag['doc_status'] = (int) wp_remote_retrieve_response_code($doc_resp);
        $headers = wp_remote_retrieve_headers($doc_resp);
        $diag['doc_cf_mitigated'] = sanitize_text_field((string) ($headers['cf-mitigated'] ?? ''));
    }

    set_transient($cache_key, $diag, 5 * MINUTE_IN_SECONDS);
    return $diag;
}

function matrix_daft_build_support_message(array $diagnostics, array $known_ips, string $wsdl_url): string {
    $outbound = !empty($diagnostics['public_ip']) ? $diagnostics['public_ip'] : 'unknown';
    $ip_list = !empty($known_ips) ? implode(', ', $known_ips) : 'none provided yet';

    return sprintf(
        "Hi Daft API support,\n\nWe are integrating Daft API into a WordPress backend and need server-to-server access enabled.\n\nObserved issue:\n- WSDL URL: %s\n- WSDL HTTP status from our server: %d\n- docs endpoint HTTP status from our server: %d\n- Cloudflare mitigation header: %s\n\nPlease allowlist the following server IPs:\n- Current known server IPs: %s\n- Current detected outbound IP: %s\n- We will also provide our final live server IP for allowlisting before launch.\n\nNote: we also develop locally, but local outbound IPs are not stable. We can route local testing via an allowlisted server/tunnel if required.\n\nThanks.",
        $wsdl_url,
        (int) ($diagnostics['wsdl_status'] ?? 0),
        (int) ($diagnostics['doc_status'] ?? 0),
        ($diagnostics['wsdl_cf_mitigated'] ?: 'not present'),
        $ip_list,
        $outbound
    );
}

function matrix_daft_handle_manual_sync(): void {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to run this sync.');
    }

    check_admin_referer('matrix_daft_sync_now', 'matrix_daft_nonce');
    $result = matrix_daft_run_import('manual');

    $redirect = add_query_arg([
        'post_type' => 'property',
        'page' => 'matrix-daft-import',
        'daft_sync' => 1,
        'success' => $result['success'] ? 1 : 0,
        'message' => $result['message'],
    ], admin_url('edit.php'));

    wp_safe_redirect($redirect);
    exit;
}

function matrix_daft_register_cron(): void {
    $enabled = (bool) matrix_daft_get_option('daft_auto_sync_daily', false);
    $hook = 'matrix_daft_daily_sync';
    $scheduled = wp_next_scheduled($hook);

    if ($enabled && !$scheduled) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', $hook);
    }

    if (!$enabled && $scheduled) {
        wp_clear_scheduled_hook($hook);
    }
}

function matrix_daft_run_daily_sync(): void {
    matrix_daft_run_import('daily_cron');
}

function matrix_daft_run_import(string $trigger = 'manual'): array {
    $request_mode = trim((string) matrix_daft_get_option('daft_request_mode', 'mock'));
    if (!in_array($request_mode, ['mock', 'soap', 'rest'], true)) {
        $request_mode = 'mock';
    }
    $api_key = trim((string) matrix_daft_get_option('daft_api_key', ''));
    $endpoint = trim((string) matrix_daft_get_option('daft_endpoint_url', ''));
    $http_method = strtoupper(trim((string) matrix_daft_get_option('daft_http_method', 'GET')));
    if (!in_array($http_method, ['GET', 'POST'], true)) {
        $http_method = 'GET';
    }
    $body_json = trim((string) matrix_daft_get_option('daft_request_body_json', ''));
    $soap_wsdl_url = trim((string) matrix_daft_get_option('daft_soap_wsdl_url', 'http://api.daft.ie/v2/wsdl.xml'));
    $soap_operations = matrix_daft_get_selected_soap_operations();
    $soap_operation = $soap_operations[0] ?? 'search_sale';
    $soap_query_json = trim((string) matrix_daft_get_option('daft_soap_query_json', ''));
    $mock_payload_json = trim((string) matrix_daft_get_option('daft_mock_payload_json', ''));
    $mock_gallery_placeholders = (bool) matrix_daft_get_option('daft_mock_gallery_placeholders', true);
    $batch_size = (int) matrix_daft_get_option('daft_sync_batch_size', 10);
    $time_budget_seconds = (int) matrix_daft_get_option('daft_sync_time_budget_seconds', 20);
    $header_name = trim((string) matrix_daft_get_option('daft_api_key_header', 'x-api-key'));
    $header_prefix = trim((string) matrix_daft_get_option('daft_api_key_prefix', ''));
    $default_status = trim((string) matrix_daft_get_option('daft_status_default', 'For Sale'));
    $default_type = trim((string) matrix_daft_get_option('daft_type_default', 'House'));
    $update_images = (bool) matrix_daft_get_option('daft_update_images', false);

    if ($api_key === '') {
        $message = 'Daft API key is missing. Update Theme Options first.';
        matrix_daft_store_report($trigger, 0, 0, 0, $message);
        return ['success' => false, 'message' => $message];
    }

    $payload = [];
    $items = [];
    $report_debug = [];
    if ($request_mode === 'mock') {
        $mock_result = matrix_daft_request_mock_data($mock_payload_json);
        if (!$mock_result['success']) {
            matrix_daft_store_report($trigger, 0, 0, 0, $mock_result['message']);
            return ['success' => false, 'message' => $mock_result['message']];
        }

        $payload = $mock_result['payload'];
        $items = matrix_daft_extract_listing_items($payload);
    } elseif ($request_mode === 'soap') {
        if ($soap_wsdl_url === '' || empty($soap_operations)) {
            $message = 'SOAP WSDL URL or SOAP operation is missing.';
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }

        $report_debug = [
            'request_mode' => 'soap',
            'soap_operations_selected' => array_values($soap_operations),
            'soap_operations' => [],
        ];
        $operation_item_groups = [];
        foreach ($soap_operations as $soap_operation_name) {
            $soap_result = matrix_daft_request_soap($soap_wsdl_url, $soap_operation_name, $api_key, $soap_query_json);
            if (!$soap_result['success']) {
                matrix_daft_store_report($trigger, 0, 0, 0, $soap_result['message'], [], (array) ($soap_result['debug'] ?? []));
                return ['success' => false, 'message' => $soap_result['message']];
            }

            $payload = $soap_result['payload'];
            $operation_items = matrix_daft_extract_listing_items($payload);
            $soap_debug = is_array($soap_result['debug'] ?? null) ? $soap_result['debug'] : [];
            $report_debug['soap_operations'][$soap_operation_name] = [
                'items_extracted' => count($operation_items),
                'pages_fetched' => (int) ($soap_debug['pages_fetched'] ?? 1),
                'num_pages' => (int) ($soap_debug['num_pages'] ?? 1),
                'total_results' => (int) ($soap_debug['total_results'] ?? count($operation_items)),
                'perpage' => (int) ($soap_debug['perpage'] ?? 0),
                'page_fetch_error' => (string) ($soap_debug['page_fetch_error'] ?? ''),
                'page_fetch_error_page' => (int) ($soap_debug['page_fetch_error_page'] ?? 0),
                'query_keys' => array_values((array) ($soap_debug['query_keys'] ?? [])),
            ];
            foreach ($operation_items as $operation_item) {
                if (!is_array($operation_item)) {
                    continue;
                }
                $operation_item['_matrix_daft_source_operation'] = $soap_operation_name;
                $operation_item_groups[$soap_operation_name][] = $operation_item;
            }
        }
        $items = matrix_daft_merge_operation_item_groups(array_values($operation_item_groups));
        $report_debug['items_merged_total'] = count($items);
    } else {
        if ($endpoint === '') {
            $message = 'REST endpoint URL is missing.';
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }

        $auth_value = $header_prefix !== '' ? ($header_prefix . ' ' . $api_key) : $api_key;
        $request_result = matrix_daft_request_endpoint($endpoint, $http_method, $auth_value, $header_name, $body_json);
        $response = $request_result['response'];
        $effective_endpoint = $request_result['endpoint'];
        $attempted_endpoints = $request_result['attempted_endpoints'];

        if (is_wp_error($response)) {
            $message = sprintf(
                'Request failed (%s %s): %s',
                $http_method,
                $effective_endpoint,
                $response->get_error_message()
            );
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        if ($status_code < 200 || $status_code >= 300) {
            $message = matrix_daft_format_http_error($status_code, $http_method, $effective_endpoint, $body, $attempted_endpoints);
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            $message = 'Daft API did not return valid JSON.';
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }
        $items = matrix_daft_extract_listing_items($payload);
    }
    if (empty($items)) {
        $message = 'No listing items were found in the Daft response.';
        update_option('matrix_daft_sync_cursor', 0, false);
        matrix_daft_store_report($trigger, 0, 0, 0, $message);
        return ['success' => false, 'message' => $message];
    }

    $total_items = count($items);
    $batch_size = $batch_size > 0 ? $batch_size : $total_items;
    $time_budget_seconds = $time_budget_seconds >= 5 ? $time_budget_seconds : 20;
    $cursor = (int) get_option('matrix_daft_sync_cursor', 0);
    if ($cursor < 0 || $cursor >= $total_items) {
        $cursor = 0;
    }

    $items_to_process = $items;
    $cursor_start = 0;
    if ($total_items > $batch_size) {
        $items_to_process = array_slice($items, $cursor, $batch_size);
        $cursor_start = $cursor;
    }

    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $source_label = $request_mode === 'mock' ? 'daft_mock' : 'daft';
    $synced_items = [];
    $processed_count = 0;
    $started_at = microtime(true);
    $hit_time_budget = false;

    foreach ($items_to_process as $raw_item) {
        if ((microtime(true) - $started_at) >= $time_budget_seconds) {
            $hit_time_budget = true;
            break;
        }
        $processed_count++;
        if (!is_array($raw_item)) {
            $skipped++;
            continue;
        }

        $listing = matrix_daft_normalize_listing($raw_item);
        $source_operation = (string) ($raw_item['_matrix_daft_source_operation'] ?? $soap_operation);
        if ($request_mode === 'soap' && empty($listing['ad_type'])) {
            $listing['ad_type'] = matrix_daft_map_soap_operation_to_ad_type($source_operation);
        }
        if ($request_mode === 'soap') {
            $listing = matrix_daft_enrich_listing_media_from_soap($listing, $soap_wsdl_url, $api_key);
        }
        if ($listing['daft_id'] === '' && $listing['title'] === '' && $listing['address'] === '') {
            $skipped++;
            continue;
        }

        if ($listing['daft_id'] === '') {
            $listing['daft_id'] = md5(wp_json_encode([$listing['title'], $listing['address']]));
        }

        if ($listing['status'] === '') {
            $listing['status'] = ($request_mode === 'soap')
                ? matrix_daft_map_soap_operation_to_default_status($source_operation)
                : '';
        }
        if ($listing['status'] === '') {
            $listing['status'] = $default_status;
        }
        if ($listing['type'] === '') {
            $listing['type'] = $default_type;
        }

        $existing_id = matrix_daft_find_post_by_daft_id($listing['daft_id']);
        if ($existing_id > 0) {
            $existing_controls = matrix_daft_get_property_sync_controls($existing_id);
            if (!empty($existing_controls['disable_sync'])) {
                $skipped++;
                if (count($synced_items) < 12) {
                    $synced_items[] = [
                        'action' => 'skipped',
                        'post_id' => $existing_id,
                        'title' => get_the_title($existing_id),
                        'daft_id' => (string) $listing['daft_id'],
                        'content_one_sync' => 'sync-disabled-for-property',
                    ];
                }
                continue;
            }
        }
        $post_id = matrix_daft_upsert_property_post($listing, $existing_id);
        if (!$post_id) {
            $skipped++;
            continue;
        }

        if ($existing_id) {
            $updated++;
            $action = 'updated';
        } else {
            $imported++;
            $action = 'imported';
        }
        matrix_daft_sync_property_meta($post_id, $listing, $source_label);
        matrix_daft_sync_taxonomies($post_id, $listing);
        matrix_daft_ensure_large_hero_block($post_id);
        matrix_daft_sync_property_data_block($post_id, $listing);
        $controls = matrix_daft_get_property_sync_controls($post_id);
        $media_sync = !empty($controls['disable_image_sync'])
            ? ['status' => 'skipped-image-sync-disabled']
            : matrix_daft_sync_full_width_media_block($post_id, $listing);
        $content_one_sync = matrix_daft_sync_content_one_block($post_id);
        $gallery_sync = (!empty($controls['disable_primary_gallery']) || !empty($controls['disable_image_sync']))
            ? ['status' => 'skipped-disabled-by-property-setting']
            : matrix_daft_sync_gallery_block(
                $post_id,
                $listing,
                ($request_mode === 'mock' && $mock_gallery_placeholders)
            );
        $content_one_followup_sync = !empty($controls['disable_followup_content'])
            ? ['status' => 'skipped-disabled-by-property-setting']
            : matrix_daft_sync_content_one_followup_block($post_id);
        $gallery_carousel_sync = (!empty($controls['disable_carousel_gallery']) || !empty($controls['disable_image_sync']))
            ? ['status' => 'skipped-disabled-by-property-setting']
            : matrix_daft_sync_gallery_carousel_block($post_id, $listing);
        $media_secondary_sync = !empty($controls['disable_secondary_media'])
            ? ['status' => 'skipped-disabled-by-property-setting']
            : matrix_daft_sync_secondary_full_width_media_block($post_id, $listing, empty($controls['disable_image_sync']));
        $cta_sync = !empty($controls['disable_cta_section'])
            ? ['status' => 'skipped-disabled-by-property-setting']
            : matrix_daft_sync_cta_block($post_id);

        if (!empty($listing['image_urls']) && is_array($listing['image_urls']) && empty($controls['disable_image_sync'])) {
            matrix_daft_sync_featured_image($post_id, $listing['image_urls'], $update_images);
        }

        if (count($synced_items) < 12) {
            $synced_items[] = [
                'action' => $action,
                'post_id' => $post_id,
                'title' => get_the_title($post_id),
                'daft_id' => (string) $listing['daft_id'],
                'media_sync' => (string) ($media_sync['status'] ?? ''),
                'content_one_sync' => (string) ($content_one_sync['status'] ?? ''),
                'gallery_sync' => (string) ($gallery_sync['status'] ?? ''),
                'content_one_followup_sync' => (string) ($content_one_followup_sync['status'] ?? ''),
                'gallery_carousel_sync' => (string) ($gallery_carousel_sync['status'] ?? ''),
                'media_secondary_sync' => (string) ($media_secondary_sync['status'] ?? ''),
                'cta_sync' => (string) ($cta_sync['status'] ?? ''),
                'media_enrich' => (string) (($listing['_media_debug']['media_enrich'] ?? '')),
                'media_image_count' => (string) (($listing['_media_debug']['media_image_count'] ?? '')),
                'media_call_shape' => (string) (($listing['_media_debug']['media_call_shape'] ?? '')),
                'media_ad_type' => (string) (($listing['_media_debug']['media_ad_type'] ?? '')),
                'media_error' => (string) (($listing['_media_debug']['media_error'] ?? '')),
                'source_operation' => $source_operation,
            ];
        }
    }

    if ($total_items > $batch_size) {
        $next_cursor = $cursor_start + $processed_count;
        if ($next_cursor >= $total_items) {
            $next_cursor = 0;
        }
        update_option('matrix_daft_sync_cursor', $next_cursor, false);
    } else {
        update_option('matrix_daft_sync_cursor', 0, false);
    }

    $message = sprintf(
        'Daft sync complete. Imported: %d, Updated: %d, Skipped: %d.',
        $imported,
        $updated,
        $skipped
    );
    if ($total_items > $batch_size || $hit_time_budget) {
        $window_start = $cursor_start + 1;
        $window_end = $cursor_start + max(0, $processed_count);
        $message .= sprintf(
            ' Processed %d/%d listings this run (window %d-%d). Run sync again to continue.',
            $processed_count,
            $total_items,
            $window_start,
            max($window_start, $window_end)
        );
        if ($hit_time_budget) {
            $message .= sprintf(' Stopped at %ds time budget.', $time_budget_seconds);
        }
    }

    matrix_daft_store_report($trigger, $imported, $updated, $skipped, '', $synced_items, $report_debug);
    return ['success' => true, 'message' => $message];
}

function matrix_daft_request_mock_data(string $mock_payload_json = ''): array {
    if ($mock_payload_json !== '') {
        $payload = json_decode($mock_payload_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            return [
                'success' => false,
                'message' => 'Mock payload JSON is invalid. Please provide valid JSON.',
                'payload' => [],
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
        ];
    }

    $sample_path = get_template_directory() . '/inc/integrations/mock-daft-response.json';
    if (!file_exists($sample_path)) {
        return [
            'success' => false,
            'message' => 'Bundled mock payload file was not found.',
            'payload' => [],
        ];
    }

    $contents = file_get_contents($sample_path);
    if ($contents === false || trim($contents) === '') {
        return [
            'success' => false,
            'message' => 'Bundled mock payload file is empty or unreadable.',
            'payload' => [],
        ];
    }

    $payload = json_decode($contents, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        return [
            'success' => false,
            'message' => 'Bundled mock payload file contains invalid JSON.',
            'payload' => [],
        ];
    }

    return [
        'success' => true,
        'message' => '',
        'payload' => $payload,
    ];
}

function matrix_daft_extract_listing_items(array $payload): array {
    $preferred_keys = ['listings', 'results', 'items', 'properties', 'searchResults', 'search_results', 'data', 'ads'];

    foreach ($preferred_keys as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) {
            $candidate = matrix_daft_extract_listing_items_from_node($payload[$key], 0);
            if (!empty($candidate)) {
                return $candidate;
            }
        }
    }

    return matrix_daft_extract_listing_items_from_node($payload, 0);
}

function matrix_daft_extract_listing_items_from_node($node, int $depth): array {
    if ($depth > 6 || !is_array($node)) {
        return [];
    }

    if (array_is_list($node) && !empty($node)) {
        $first = $node[0];
        if (is_array($first) && matrix_daft_looks_like_listing($first)) {
            return $node;
        }

        foreach ($node as $child) {
            $found = matrix_daft_extract_listing_items_from_node($child, $depth + 1);
            if (!empty($found)) {
                return $found;
            }
        }
    }

    foreach ($node as $child) {
        $found = matrix_daft_extract_listing_items_from_node($child, $depth + 1);
        if (!empty($found)) {
            return $found;
        }
    }

    return [];
}

function matrix_daft_looks_like_listing(array $item): bool {
    $needles = ['id', 'listingId', 'listing_id', 'adId', 'title', 'address', 'displayAddress', 'full_address', 'small_thumbnail_url'];
    foreach ($needles as $needle) {
        if (array_key_exists($needle, $item)) {
            return true;
        }
    }
    return false;
}

function matrix_daft_normalize_listing(array $item): array {
    $title = (string) matrix_daft_pick($item, [
        'title',
        'headline',
        'displayAddress',
        'display_address',
        'address.full',
        'full_address',
    ], '');

    $address = matrix_daft_pick($item, [
        'address.full',
        'displayAddress',
        'display_address',
        'address',
        'location.address',
        'full_address',
    ], '');
    if (is_array($address)) {
        $address = implode(', ', array_filter(array_map('strval', $address)));
    }

    $description = (string) matrix_daft_pick($item, [
        'description',
        'propertyDescription',
        'seoDescription',
        'summary',
    ], '');

    $status = matrix_daft_normalize_status((string) matrix_daft_pick($item, [
        'status',
        'saleStatus',
        'listingStatus',
        'channel',
        'sale_type',
        'sale_agreed',
        'isLet',
    ], ''));

    $type = matrix_daft_normalize_type((string) matrix_daft_pick($item, [
        'propertyType',
        'property_type',
        'type',
        'category',
        'property.type',
        'property_type.value',
    ], ''));

    $price = matrix_daft_pick($item, [
        'displayPrice',
        'price',
        'rent',
        'pricing.display',
        'pricing.price',
        'price_s',
    ], '');
    if (is_array($price)) {
        $price = (string) ($price['display'] ?? '');
    }

    $image_urls = matrix_daft_extract_image_urls($item);
    $image_url = !empty($image_urls[0]) ? (string) $image_urls[0] : '';
    $video_urls = matrix_daft_extract_video_urls($item);
    $video_url = !empty($video_urls[0]) ? (string) $video_urls[0] : '';
    $url = (string) matrix_daft_pick($item, ['url', 'seoUrl', 'permalink', 'daftUrl', 'daft_url'], '');
    if ($url !== '' && str_starts_with($url, '/')) {
        $url = 'https://www.daft.ie' . $url;
    }

    return [
        'daft_id' => (string) matrix_daft_pick($item, ['id', 'listingId', 'listing_id', 'adId', 'ad_id', 'daftId'], ''),
        'ad_type' => (string) matrix_daft_pick($item, ['ad_type', 'adType', 'type'], ''),
        'title' => $title,
        'address' => (string) $address,
        'description' => $description,
        'excerpt' => wp_trim_words(wp_strip_all_tags($description), 32, '...'),
        'status' => $status,
        'type' => $type,
        'bedrooms' => matrix_daft_pick($item, ['bedrooms', 'numBedrooms', 'details.bedrooms', 'property.bedrooms'], ''),
        'bathrooms' => matrix_daft_pick($item, ['bathrooms', 'numBathrooms', 'details.bathrooms', 'property.bathrooms'], ''),
        'area' => matrix_daft_pick($item, ['area', 'floorArea', 'details.floorArea', 'size', 'dimensions'], ''),
        'price' => is_scalar($price) ? (string) $price : '',
        'url' => $url,
        'image_url' => $image_url,
        'image_urls' => $image_urls,
        'video_url' => $video_url,
        'video_urls' => $video_urls,
    ];
}

function matrix_daft_map_soap_operation_to_ad_type(string $operation): string {
    $map = [
        'search_sale' => 'sale',
        'search_rental' => 'rental',
        'search_commercial' => 'commercial',
        'search_new_development' => 'new_development',
        'search_shortterm' => 'shortterm',
        'search_sharing' => 'sharing',
        'search_parking' => 'parking',
    ];
    return (string) ($map[$operation] ?? '');
}

function matrix_daft_map_soap_operation_to_default_status(string $operation): string {
    $map = [
        'search_sale' => 'For Sale',
        'search_rental' => 'For Rent',
        'search_shortterm' => 'For Rent',
        'search_sharing' => 'For Rent',
    ];
    return (string) ($map[$operation] ?? '');
}

function matrix_daft_pick(array $source, array $paths, $default = '') {
    foreach ($paths as $path) {
        $value = matrix_daft_get_by_path($source, $path);
        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }
    }

    return $default;
}

function matrix_daft_get_by_path(array $source, string $path) {
    if (array_key_exists($path, $source)) {
        return $source[$path];
    }

    $segments = explode('.', $path);
    $value = $source;
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }

    return $value;
}

function matrix_daft_extract_image_urls(array $item): array {
    $urls = [];
    $candidates = matrix_daft_pick($item, ['images', 'photos', 'media.images', 'media.photos', 'photoUrls'], []);

    foreach (['large_thumbnail_url', 'small_thumbnail_url', 'thumbnail_url', 'image_url'] as $thumb_key) {
        $thumb_value = matrix_daft_pick($item, [$thumb_key], '');
        if (is_string($thumb_value) && filter_var($thumb_value, FILTER_VALIDATE_URL)) {
            $urls[] = $thumb_value;
        }
    }

    if (is_array($candidates)) {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
                $urls[] = $candidate;
                continue;
            }

            if (is_array($candidate)) {
                foreach (['url', 'src', 'large', 'small', 'thumbnail', 'image_url'] as $key) {
                    if (!empty($candidate[$key]) && is_string($candidate[$key]) && filter_var($candidate[$key], FILTER_VALIDATE_URL)) {
                        $urls[] = $candidate[$key];
                        break;
                    }
                }
            }
        }
    }

    // Fallback: recursively scan common media branches for any URL-like values.
    $media_branches = [];
    foreach (['images', 'photos', 'media', 'photoUrls', 'advertisement', 'ad'] as $branch_key) {
        if (isset($item[$branch_key])) {
            $media_branches[] = $item[$branch_key];
        }
    }
    if (empty($media_branches)) {
        $media_branches[] = $item;
    }

    foreach ($media_branches as $branch) {
        matrix_daft_collect_urls($branch, $urls);
    }

    $urls = array_values(array_unique(array_filter($urls)));
    usort($urls, static function ($a, $b): int {
        return matrix_daft_score_image_url((string) $b) <=> matrix_daft_score_image_url((string) $a);
    });

    return $urls;
}

function matrix_daft_score_image_url(string $url): int {
    $score = 0;
    $url_lc = strtolower($url);

    if (str_contains($url_lc, 'large') || str_contains($url_lc, 'xl') || str_contains($url_lc, 'full')) {
        $score += 30;
    }
    if (str_contains($url_lc, 'small') || str_contains($url_lc, 'thumb') || str_contains($url_lc, 'thumbnail')) {
        $score -= 25;
    }

    $parts = wp_parse_url($url);
    if (is_array($parts) && !empty($parts['query'])) {
        parse_str((string) $parts['query'], $q);
        foreach (['w', 'width'] as $k) {
            if (!empty($q[$k]) && is_numeric($q[$k])) {
                $score += (int) min(40, floor(((int) $q[$k]) / 100));
                break;
            }
        }
        foreach (['h', 'height'] as $k) {
            if (!empty($q[$k]) && is_numeric($q[$k])) {
                $score += (int) min(20, floor(((int) $q[$k]) / 100));
                break;
            }
        }
    }

    return $score;
}

function matrix_daft_extract_video_urls(array $item): array {
    $urls = [];

    $direct = matrix_daft_pick($item, [
        'video_url',
        'videoUrl',
        'video.url',
        'media.video_url',
        'media.video.url',
        'virtual_tour_url',
        'virtualTourUrl',
        'youtube_url',
        'vimeo_url',
    ], '');
    if (is_string($direct) && filter_var($direct, FILTER_VALIDATE_URL)) {
        $urls[] = $direct;
    }

    $candidates = matrix_daft_pick($item, ['videos', 'media.videos', 'video', 'media.video'], []);
    if (is_array($candidates)) {
        matrix_daft_collect_video_urls($candidates, $urls);
    }

    matrix_daft_collect_video_urls($item, $urls);
    return array_values(array_unique(array_filter($urls)));
}

function matrix_daft_enrich_listing_media_from_soap(array $listing, string $wsdl_url, string $api_key): array {
    $daft_id = (string) ($listing['daft_id'] ?? '');
    $ad_type = trim((string) ($listing['ad_type'] ?? ''));
    if ($daft_id === '' || !is_numeric($daft_id) || $ad_type === '') {
        return $listing;
    }

    $media = matrix_daft_request_soap_media($wsdl_url, $api_key, (int) $daft_id, $ad_type);
    if (!empty($media['debug']) && is_array($media['debug'])) {
        $listing['_media_debug'] = $media['debug'];
    }
    if (empty($media['image_urls']) && empty($media['video_urls'])) {
        return $listing;
    }

    $current_images = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $current_videos = is_array($listing['video_urls'] ?? null) ? $listing['video_urls'] : [];

    $merged_images = array_values(array_unique(array_filter(array_merge($media['image_urls'], $current_images))));
    $merged_videos = array_values(array_unique(array_filter(array_merge($media['video_urls'], $current_videos))));

    usort($merged_images, static function ($a, $b): int {
        return matrix_daft_score_image_url((string) $b) <=> matrix_daft_score_image_url((string) $a);
    });

    $listing['image_urls'] = $merged_images;
    if (!empty($merged_images)) {
        $listing['image_url'] = (string) $merged_images[0];
    }
    $listing['video_urls'] = $merged_videos;
    if (!empty($merged_videos)) {
        $listing['video_url'] = (string) $merged_videos[0];
    }

    return $listing;
}

function matrix_daft_request_soap_media(string $wsdl_url, string $api_key, int $ad_id, string $ad_type): array {
    if ($ad_id <= 0 || $ad_type === '' || !class_exists('SoapClient')) {
        return ['image_urls' => [], 'video_urls' => [], 'debug' => ['media_enrich' => 'skipped-invalid-ad-id-or-type']];
    }

    try {
        $client = new SoapClient($wsdl_url, [
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 20,
            'trace' => false,
        ]);
        $attempts = [
            [
                'name' => 'object-int-adid',
                'args' => [(object) [
                    'api_key' => $api_key,
                    'ad_type' => $ad_type,
                    'ad_id' => (int) $ad_id,
                ]],
            ],
            [
                'name' => 'array-int-adid',
                'args' => [[
                    'api_key' => $api_key,
                    'ad_type' => $ad_type,
                    'ad_id' => (int) $ad_id,
                ]],
            ],
            [
                'name' => 'object-string-adid',
                'args' => [(object) [
                    'api_key' => $api_key,
                    'ad_type' => $ad_type,
                    'ad_id' => (string) $ad_id,
                ]],
            ],
            [
                'name' => 'array-string-adid',
                'args' => [[
                    'api_key' => $api_key,
                    'ad_type' => $ad_type,
                    'ad_id' => (string) $ad_id,
                ]],
            ],
        ];

        $errors = [];
        foreach ($attempts as $attempt) {
            try {
                $result = $client->__soapCall('media', $attempt['args']);
                $payload = json_decode(wp_json_encode($result), true);
                if (!is_array($payload)) {
                    $errors[] = $attempt['name'] . ': invalid payload';
                    continue;
                }

                $image_urls = [];
                $video_urls = [];
                matrix_daft_collect_media_image_fields($payload, $image_urls);
                matrix_daft_collect_media_video_urls($payload, $video_urls);
                $image_urls = array_values(array_unique(array_filter($image_urls)));
                $video_urls = array_values(array_unique(array_filter($video_urls)));

                if (empty($image_urls) && empty($video_urls)) {
                    $errors[] = $attempt['name'] . ': no media in response';
                    continue;
                }

                return [
                    'image_urls' => $image_urls,
                    'video_urls' => $video_urls,
                    'debug' => [
                        'media_enrich' => 'ok',
                        'media_call_shape' => $attempt['name'],
                        'media_ad_type' => $ad_type,
                        'media_image_count' => count($image_urls),
                        'media_video_count' => count($video_urls),
                    ],
                ];
            } catch (Throwable $inner) {
                $errors[] = $attempt['name'] . ': ' . $inner->getMessage();
            }
        }

        return [
            'image_urls' => [],
            'video_urls' => [],
            'debug' => [
                'media_enrich' => 'soap-error',
                'media_ad_type' => $ad_type,
                'media_error' => implode(' | ', $errors),
            ],
        ];
    } catch (Throwable $e) {
        return [
            'image_urls' => [],
            'video_urls' => [],
            'debug' => [
                'media_enrich' => 'soap-error',
                'media_error' => $e->getMessage(),
            ],
        ];
    }
}

function matrix_daft_collect_media_image_fields($node, array &$image_urls): void {
    if (!is_array($node)) {
        return;
    }

    $preferred_keys = ['large_url', 'ipad_url', 'ipad_gallery_url', 'medium_url', 'iphone_url', 'ipad_search_url', 'small_url', 'url'];
    $chosen_url = '';
    foreach ($preferred_keys as $key) {
        if (!empty($node[$key]) && is_string($node[$key])) {
            $normalized = matrix_daft_normalize_remote_url($node[$key]);
            if ($normalized !== '' && filter_var($normalized, FILTER_VALIDATE_URL)) {
                $chosen_url = $normalized;
                break;
            }
        }
    }

    if ($chosen_url !== '') {
        $image_urls[] = $chosen_url;
        return;
    }

    foreach ($node as $value) {
        if (is_array($value)) {
            matrix_daft_collect_media_image_fields($value, $image_urls);
        }
    }
}

function matrix_daft_collect_media_video_urls($node, array &$video_urls): void {
    if (is_string($node)) {
        $normalized = matrix_daft_normalize_remote_url($node);
        if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_URL)) {
            return;
        }
        $host = strtolower((string) wp_parse_url($normalized, PHP_URL_HOST));
        $path = strtolower((string) wp_parse_url($normalized, PHP_URL_PATH));
        if (
            str_contains($host, 'youtube.com')
            || str_contains($host, 'youtu.be')
            || str_contains($host, 'vimeo.com')
            || str_contains($host, 'player.vimeo.com')
            || str_contains($path, '.mp4')
            || str_contains($path, '.webm')
        ) {
            $video_urls[] = $normalized;
        }
        return;
    }

    if (!is_array($node)) {
        return;
    }

    foreach ($node as $value) {
        if (is_array($value) || is_string($value)) {
            matrix_daft_collect_media_video_urls($value, $video_urls);
        }
    }
}

function matrix_daft_normalize_remote_url(string $url): string {
    $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5));
    if ($url === '') {
        return '';
    }
    if (str_starts_with($url, '//')) {
        return 'https:' . $url;
    }
    if (str_starts_with($url, '/')) {
        return 'https://www.daft.ie' . $url;
    }
    return $url;
}

function matrix_daft_media_compare_key(string $url): string {
    $normalized = matrix_daft_normalize_remote_url($url);
    if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_URL)) {
        return '';
    }
    $parts = wp_parse_url($normalized);
    if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
        return rtrim($normalized, '/');
    }
    $scheme = !empty($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
    $host = strtolower((string) $parts['host']);
    $path = (string) $parts['path'];
    return rtrim($scheme . '://' . $host . $path, '/');
}

function matrix_daft_collect_video_urls($node, array &$urls): void {
    if (is_string($node) && filter_var($node, FILTER_VALIDATE_URL)) {
        $host = strtolower((string) wp_parse_url($node, PHP_URL_HOST));
        $path = strtolower((string) wp_parse_url($node, PHP_URL_PATH));
        if (
            str_contains($host, 'youtube.com')
            || str_contains($host, 'youtu.be')
            || str_contains($host, 'vimeo.com')
            || str_contains($host, 'player.vimeo.com')
            || str_contains($path, '.mp4')
            || str_contains($path, '.webm')
        ) {
            $urls[] = $node;
        }
        return;
    }

    if (!is_array($node)) {
        return;
    }

    foreach ($node as $key => $value) {
        $key_lc = is_string($key) ? strtolower($key) : '';
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            if (
                str_contains($key_lc, 'video')
                || str_contains($key_lc, 'tour')
                || str_contains($key_lc, 'youtube')
                || str_contains($key_lc, 'vimeo')
            ) {
                $urls[] = $value;
                continue;
            }
        }
        if (is_array($value)) {
            matrix_daft_collect_video_urls($value, $urls);
        }
    }
}

function matrix_daft_collect_urls($node, array &$urls): void {
    if (is_string($node) && filter_var($node, FILTER_VALIDATE_URL)) {
        $urls[] = $node;
        return;
    }

    if (!is_array($node)) {
        return;
    }

    foreach ($node as $key => $value) {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            // Prioritize URL-ish keys when present.
            if (
                is_string($key)
                && (
                    str_contains(strtolower($key), 'url')
                    || str_contains(strtolower($key), 'image')
                    || str_contains(strtolower($key), 'photo')
                    || str_contains(strtolower($key), 'thumbnail')
                )
            ) {
                $urls[] = $value;
                continue;
            }
            $urls[] = $value;
            continue;
        }

        if (is_array($value)) {
            matrix_daft_collect_urls($value, $urls);
        }
    }
}

function matrix_daft_normalize_status(string $status): string {
    $value = strtolower(trim($status));
    if ($value === '') {
        return '';
    }

    if (str_contains($value, 'sold')) {
        return 'Sold';
    }
    if (str_contains($value, 'let')) {
        return 'Let Agreed';
    }
    if (str_contains($value, 'rent')) {
        return 'For Rent';
    }
    if (str_contains($value, 'sale')) {
        return 'For Sale';
    }

    return ucwords($value);
}

function matrix_daft_normalize_type(string $type): string {
    $value = trim($type);
    if ($value === '') {
        return '';
    }

    return ucwords(strtolower($value));
}

function matrix_daft_format_price_display(string $price): string {
    $price = trim($price);
    if ($price === '') {
        return '';
    }

    if (str_contains($price, '€') || stripos($price, 'eur') !== false) {
        return $price;
    }

    $numeric_candidate = preg_replace('/[^\d.]/', '', $price);
    if ($numeric_candidate !== '' && is_numeric($numeric_candidate)) {
        $value = (float) $numeric_candidate;
        if ((int) $value === (float) $value) {
            return '€' . number_format((int) $value);
        }
        return '€' . number_format($value, 2);
    }

    return $price;
}

function matrix_daft_format_description_html(string $description): string {
    $description = trim(str_replace(["\r\n", "\r"], "\n", $description));
    if ($description === '') {
        return '';
    }

    $paragraphs = preg_split("/\n\s*\n+/", $description);
    if (!is_array($paragraphs)) {
        $paragraphs = [$description];
    }

    $html_parts = [];
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim((string) $paragraph);
        if ($paragraph === '') {
            continue;
        }
        $paragraph = preg_replace('/\s+/', ' ', $paragraph);
        $html_parts[] = '<p>' . esc_html((string) $paragraph) . '</p>';
    }

    return implode("\n", $html_parts);
}

function matrix_daft_parse_soap_query_payload(string $query_json = ''): array {
    $query_payload = [];
    if ($query_json !== '') {
        $query = json_decode($query_json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($query)) {
            $query_payload = array_filter($query, static function ($value) {
                return $value !== null && $value !== '' && $value !== [];
            });
        }
    }

    return $query_payload;
}

function matrix_daft_build_soap_parameters(string $api_key, array $query_payload): array {
    return [
        'api_key' => $api_key,
        'query' => !empty($query_payload) ? json_decode(wp_json_encode($query_payload)) : new stdClass(),
    ];
}

function matrix_daft_perform_soap_operation(SoapClient $client, string $operation, string $api_key, array $query_payload, string $wsdl_url): array {
    try {
        $parameters = matrix_daft_build_soap_parameters($api_key, $query_payload);
        $result = $client->__soapCall($operation, [$parameters]);
        if (!is_array($result) && !is_object($result)) {
            $result = (array) $result;
        }

        $payload = json_decode(wp_json_encode($result), true);
        if (!is_array($payload)) {
            return [
                'success' => false,
                'message' => 'SOAP response could not be converted to an array.',
                'payload' => [],
                'debug' => [
                    'soap_mode' => 'wrapped_query',
                    'operation' => $operation,
                    'wsdl' => $wsdl_url,
                    'query_keys' => array_keys($query_payload),
                ],
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
            'debug' => [
                'soap_mode' => 'wrapped_query',
                'operation' => $operation,
                'wsdl' => $wsdl_url,
                'query_keys' => array_keys($query_payload),
            ],
        ];
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $debug = [
            'soap_mode' => 'wrapped_query',
            'operation' => $operation,
            'wsdl' => $wsdl_url,
            'query_keys' => array_keys($query_payload),
            'error' => $message,
        ];

        $query_property_error = (bool) preg_match('/has no\s+[\'"]?query[\'"]?\s+property/i', $message);
        if (!empty($query_payload) && $query_property_error) {
            try {
                $flat_parameters = array_merge(['api_key' => $api_key], $query_payload);
                $retry_result = $client->__soapCall($operation, [$flat_parameters]);
                $retry_payload = json_decode(wp_json_encode($retry_result), true);
                if (is_array($retry_payload)) {
                    return [
                        'success' => true,
                        'message' => '',
                        'payload' => $retry_payload,
                        'debug' => [
                            'soap_mode' => 'flat_query_retry',
                            'operation' => $operation,
                            'wsdl' => $wsdl_url,
                            'query_keys' => array_keys($query_payload),
                            'retry_reason' => 'query property not accepted',
                        ],
                    ];
                }
            } catch (Throwable $retry_error) {
                $message = $retry_error->getMessage();
                $debug['flat_retry_error'] = $message;
            }
        }

        return [
            'success' => false,
            'message' => sprintf('SOAP request failed (%s %s): %s', $operation, $wsdl_url, $message),
            'payload' => [],
            'debug' => $debug,
        ];
    }
}

function matrix_daft_paginate_soap_search_results(SoapClient $client, string $operation, string $api_key, array $query_payload, array $payload, string $wsdl_url): array {
    if (!str_starts_with($operation, 'search_')) {
        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
            'debug' => [
                'pages_fetched' => 1,
            ],
        ];
    }

    $pagination = is_array($payload['pagination'] ?? null) ? $payload['pagination'] : [];
    $num_pages = (int) ($pagination['num_pages'] ?? 1);
    if ($num_pages <= 1) {
        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
            'debug' => [
                'pages_fetched' => 1,
                'num_pages' => $num_pages,
                'total_results' => (int) ($pagination['total_results'] ?? 0),
            ],
        ];
    }

    $merged_payload = $payload;
    $merged_ads = is_array($payload['ads'] ?? null) ? $payload['ads'] : [];
    $pages_fetched = 1;
    $max_pages = min($num_pages, 25);

    for ($page = 2; $page <= $max_pages; $page++) {
        $page_query = $query_payload;
        $page_query['page'] = $page;
        $page_result = matrix_daft_perform_soap_operation($client, $operation, $api_key, $page_query, $wsdl_url);
        if (!$page_result['success']) {
            return [
                'success' => true,
                'message' => '',
                'payload' => $merged_payload,
                'debug' => [
                    'pages_fetched' => $pages_fetched,
                    'num_pages' => $num_pages,
                    'total_results' => (int) ($pagination['total_results'] ?? 0),
                    'page_fetch_error' => $page_result['message'],
                    'page_fetch_error_page' => $page,
                ],
            ];
        }

        $page_payload = is_array($page_result['payload'] ?? null) ? $page_result['payload'] : [];
        $page_ads = is_array($page_payload['ads'] ?? null) ? $page_payload['ads'] : [];
        if (!empty($page_ads)) {
            $merged_ads = array_merge($merged_ads, $page_ads);
        }
        $pages_fetched++;
    }

    $merged_payload['ads'] = $merged_ads;

    return [
        'success' => true,
        'message' => '',
        'payload' => $merged_payload,
        'debug' => [
            'pages_fetched' => $pages_fetched,
            'num_pages' => $num_pages,
            'total_results' => (int) ($pagination['total_results'] ?? count($merged_ads)),
        ],
    ];
}

function matrix_daft_request_soap(string $wsdl_url, string $operation, string $api_key, string $query_json = ''): array {
    if (!class_exists('SoapClient')) {
        return [
            'success' => false,
            'message' => 'PHP SOAP extension is not available on this server.',
            'payload' => [],
        ];
    }

    $preflight = wp_remote_get($wsdl_url, [
        'timeout' => 20,
        'headers' => [
            'Accept' => 'application/xml,text/xml,*/*',
        ],
    ]);

    if (is_wp_error($preflight)) {
        return [
            'success' => false,
            'message' => sprintf('SOAP WSDL preflight failed (%s): %s', $wsdl_url, $preflight->get_error_message()),
            'payload' => [],
        ];
    }

    $preflight_status = (int) wp_remote_retrieve_response_code($preflight);
    if ($preflight_status >= 400) {
        $headers = wp_remote_retrieve_headers($preflight);
        $cf_mitigated = strtolower((string) ($headers['cf-mitigated'] ?? ''));
        $server = strtolower((string) ($headers['server'] ?? ''));
        $details = sprintf('HTTP %d from %s.', $preflight_status, $wsdl_url);

        if ($cf_mitigated === 'challenge' || str_contains($server, 'cloudflare')) {
            $details .= ' Daft is protected by a Cloudflare challenge for this request. Your server must be allowlisted by Daft (or Daft must provide a direct API endpoint without browser challenge).';
        }

        return [
            'success' => false,
            'message' => 'SOAP WSDL is not reachable from this server. ' . $details,
            'payload' => [],
        ];
    }

    $query_payload = matrix_daft_parse_soap_query_payload($query_json);
    if (str_starts_with($operation, 'search_')) {
        if (empty($query_payload['perpage'])) {
            $query_payload['perpage'] = 100;
        }
        if (empty($query_payload['page'])) {
            $query_payload['page'] = 1;
        }
    }

    try {
        $client = new SoapClient($wsdl_url, [
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'trace' => false,
        ]);

        $functions = [];
        try {
            $functions = $client->__getFunctions();
        } catch (Throwable $e) {
            $functions = [];
        }

        if (!empty($functions) && is_array($functions)) {
            $available = false;
            foreach ($functions as $signature) {
                if (is_string($signature) && preg_match('/\b' . preg_quote($operation, '/') . '\s*\(/', $signature)) {
                    $available = true;
                    break;
                }
            }
            if (!$available) {
                return [
                    'success' => false,
                    'message' => sprintf('SOAP operation "%s" is not available on %s.', $operation, $wsdl_url),
                    'payload' => [],
                ];
            }
        }

        $result = matrix_daft_perform_soap_operation($client, $operation, $api_key, $query_payload, $wsdl_url);
        if (!$result['success']) {
            return $result;
        }

        $payload = is_array($result['payload'] ?? null) ? $result['payload'] : [];
        $pagination_result = matrix_daft_paginate_soap_search_results($client, $operation, $api_key, $query_payload, $payload, $wsdl_url);
        $payload = is_array($pagination_result['payload'] ?? null) ? $pagination_result['payload'] : $payload;
        $pagination_debug = is_array($pagination_result['debug'] ?? null) ? $pagination_result['debug'] : [];

        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
            'debug' => [
                'soap_mode' => (string) (($result['debug']['soap_mode'] ?? 'wrapped_query')),
                'operation' => $operation,
                'wsdl' => $wsdl_url,
                'query_keys' => array_keys($query_payload),
                'functions_count' => is_array($functions) ? count($functions) : 0,
                'perpage' => (int) ($query_payload['perpage'] ?? 0),
                'page' => (int) ($query_payload['page'] ?? 1),
                'pages_fetched' => (int) ($pagination_debug['pages_fetched'] ?? 1),
                'num_pages' => (int) ($pagination_debug['num_pages'] ?? 1),
                'total_results' => (int) ($pagination_debug['total_results'] ?? 0),
                'page_fetch_error' => (string) ($pagination_debug['page_fetch_error'] ?? ''),
                'page_fetch_error_page' => (int) ($pagination_debug['page_fetch_error_page'] ?? 0),
            ],
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'message' => sprintf('SOAP request failed (%s %s): %s', $operation, $wsdl_url, $e->getMessage()),
            'payload' => [],
            'debug' => [
                'soap_mode' => 'request_soap_outer',
                'operation' => $operation,
                'wsdl' => $wsdl_url,
                'query_keys' => array_keys($query_payload),
                'error' => $e->getMessage(),
            ],
        ];
    }
}

function matrix_daft_find_post_by_daft_id(string $daft_id): int {
    $query = new WP_Query([
        'post_type' => 'property',
        'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_matrix_daft_id',
        'meta_value' => $daft_id,
        'no_found_rows' => true,
    ]);

    if (!empty($query->posts[0])) {
        return (int) $query->posts[0];
    }

    return 0;
}

function matrix_daft_upsert_property_post(array $listing, int $existing_id = 0): int {
    $title = trim($listing['title']) !== '' ? trim($listing['title']) : trim($listing['address']);
    if ($title === '') {
        $title = 'Property ' . $listing['daft_id'];
    }

    $postarr = [
        'post_type' => 'property',
        'post_title' => wp_strip_all_tags($title),
        'post_content' => '',
        'post_excerpt' => sanitize_text_field((string) $listing['excerpt']),
        'post_status' => 'publish',
    ];

    if ($existing_id > 0) {
        $postarr['ID'] = $existing_id;
        $result = wp_update_post($postarr, true);
    } else {
        $result = wp_insert_post($postarr, true);
    }

    if (is_wp_error($result) || !$result) {
        return 0;
    }

    return (int) $result;
}

function matrix_daft_sync_property_meta(int $post_id, array $listing, string $source_label = 'daft'): void {
    update_post_meta($post_id, '_matrix_daft_id', (string) $listing['daft_id']);
    update_post_meta($post_id, '_matrix_property_source', sanitize_key($source_label));
    update_post_meta($post_id, 'daft_url', esc_url_raw((string) $listing['url']));
    update_post_meta($post_id, 'daft_address', sanitize_text_field((string) $listing['address']));
    update_post_meta($post_id, 'price', sanitize_text_field((string) $listing['price']));
    update_post_meta($post_id, 'bedrooms', sanitize_text_field((string) $listing['bedrooms']));
    update_post_meta($post_id, 'bathrooms', sanitize_text_field((string) $listing['bathrooms']));
    update_post_meta($post_id, 'area', sanitize_text_field((string) $listing['area']));
}

function matrix_daft_sync_property_data_block(int $post_id, array $listing): void {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return;
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $property_type = trim((string) ($listing['type'] ?? ''));
    if ($property_type === '') {
        $property_type = 'Residential';
    }

    $area = trim((string) ($listing['area'] ?? ''));
    $bedrooms = trim((string) ($listing['bedrooms'] ?? ''));
    $bathrooms = trim((string) ($listing['bathrooms'] ?? ''));
    $status = trim((string) ($listing['status'] ?? ''));
    $price = trim((string) ($listing['price'] ?? ''));
    $price_display = matrix_daft_format_price_display($price);
    $address = trim((string) ($listing['address'] ?? ''));
    $description = trim((string) ($listing['description'] ?? ''));
    $description_html = matrix_daft_format_description_html($description);

    $right_text_parts = [];
    if ($address !== '') {
        $right_text_parts[] = '<h2>' . esc_html($address) . '</h2>';
    }
    if ($description_html !== '') {
        $description_html = preg_replace('/<p>/', '<p style="padding-top:0.75rem;">', $description_html, 1);
        $right_text_parts[] = $description_html;
    }
    $right_text = implode("\n", $right_text_parts);

    $extra_rows = [];
    if ($price_display !== '') {
        $extra_rows[] = [
            'label' => 'Price',
            'value' => '<p>' . esc_html($price_display) . '</p>',
            'uppercase_value' => 0,
        ];
    }
    if ($bedrooms !== '') {
        $extra_rows[] = [
            'label' => 'Bedrooms',
            'value' => '<p>' . esc_html($bedrooms) . '</p>',
            'uppercase_value' => 0,
        ];
    }
    if ($bathrooms !== '') {
        $extra_rows[] = [
            'label' => 'Bathrooms',
            'value' => '<p>' . esc_html($bathrooms) . '</p>',
            'uppercase_value' => 0,
        ];
    }
    if ($status !== '') {
        $extra_rows[] = [
            'label' => 'Status',
            'value' => '<p>' . esc_html($status) . '</p>',
            'uppercase_value' => 0,
        ];
    }
    if ($area !== '') {
        $extra_rows[] = [
            'label' => 'Area',
            'value' => '<p>' . esc_html($area) . '</p>',
            'uppercase_value' => 0,
        ];
    }

    $property_data_row = [
        'acf_fc_layout' => 'property_data',
        'sector' => $property_type,
        'size' => '',
        'extra_rows' => $extra_rows,
        'right_text' => wp_kses_post($right_text),
        'read_more_label' => 'Read more',
        'read_less_label' => 'Read less',
    ];

    $found = false;
    foreach ($rows as $index => $row) {
        if (!is_array($row) || (($row['acf_fc_layout'] ?? '') !== 'property_data')) {
            continue;
        }

        $rows[$index] = array_merge($row, $property_data_row);
        $found = true;
        break;
    }

    if (!$found) {
        array_unshift($rows, $property_data_row);
    }

    update_field('flexible_content_blocks', $rows, $post_id);
}

function matrix_daft_sync_full_width_media_block(int $post_id, array $listing): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $image_urls = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $primary_hero_url = matrix_daft_media_compare_key((string) ($listing['image_url'] ?? ''));
    $featured_url = '';
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $featured_src = wp_get_attachment_image_url($thumb_id, 'full');
        if (is_string($featured_src)) {
            $featured_url = matrix_daft_media_compare_key($featured_src);
        }
    }

    $hero_urls = array_values(array_filter(array_unique([$primary_hero_url, $featured_url])));

    $property_data_index = -1;
    $media_index = -1;
    $existing_media_url = '';
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'property_data' && $property_data_index === -1) {
            $property_data_index = $i;
        }
        if ($layout === 'full_width_media' && $media_index === -1) {
            $media_index = $i;
            $existing_image = $row['image'] ?? null;
            $existing_id = is_array($existing_image) ? (int) ($existing_image['ID'] ?? 0) : (int) $existing_image;
            if ($existing_id > 0) {
                $existing_src = wp_get_attachment_image_url($existing_id, 'full');
                if (is_string($existing_src)) {
                    $existing_media_url = matrix_daft_media_compare_key($existing_src);
                }
            }
        }
    }

    // IMPORTANT: Never duplicate the hero/featured image in full_width_media.
    $media_url = '';
    $attachment_id = 0;
    foreach ($image_urls as $candidate) {
        if (!is_string($candidate)) {
            continue;
        }
        $candidate_key = matrix_daft_media_compare_key($candidate);
        if ($candidate_key === '') {
            continue;
        }
        if (in_array($candidate_key, $hero_urls, true)) {
            continue;
        }
        $candidate_url = matrix_daft_normalize_remote_url($candidate);
        if ($candidate_url === '') {
            continue;
        }
        $candidate_attachment_id = matrix_daft_resolve_media_attachment_for_post(
            $post_id,
            $candidate_url,
            '_matrix_daft_full_width_media'
        );
        if (is_wp_error($candidate_attachment_id) || !is_numeric($candidate_attachment_id)) {
            continue;
        }
        $candidate_attachment_id = (int) $candidate_attachment_id;
        if (matrix_daft_attachment_is_portrait($candidate_attachment_id)) {
            continue;
        }
        $media_url = $candidate_url;
        $attachment_id = $candidate_attachment_id;
        break;
    }

    if ($media_url === '') {
        // If existing full_width_media duplicates hero, remove it.
        if ($media_index >= 0 && $existing_media_url !== '' && in_array($existing_media_url, $hero_urls, true)) {
            array_splice($rows, $media_index, 1);
            update_field('flexible_content_blocks', $rows, $post_id);
            return ['status' => 'removed-duplicate-hero'];
        }
        return ['status' => 'skipped-no-distinct-secondary-image'];
    }

    $media_row = [
        'acf_fc_layout' => 'full_width_media',
        'media_type' => 'image',
        'image' => (int) $attachment_id,
        'height_xs' => 400,
        'height_md' => 500,
    ];

    if ($media_index >= 0) {
        $existing = is_array($rows[$media_index]) ? $rows[$media_index] : [];
        $rows[$media_index] = array_merge($existing, $media_row);
        $media_row = $rows[$media_index];
        array_splice($rows, $media_index, 1);
    }

    $insert_at = $property_data_index >= 0 ? $property_data_index + 1 : count($rows);
    array_splice($rows, $insert_at, 0, [$media_row]);

    update_field('flexible_content_blocks', $rows, $post_id);
    return ['status' => $media_index >= 0 ? 'updated' : 'added'];
}

function matrix_daft_sync_content_one_block(int $post_id): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $property_data_index = -1;
    $full_width_media_index = -1;
    $content_one_index = -1;

    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'property_data' && $property_data_index === -1) {
            $property_data_index = $i;
        }
        if ($layout === 'full_width_media' && $full_width_media_index === -1) {
            $full_width_media_index = $i;
        }
        if ($layout === 'content_one' && $content_one_index === -1) {
            $content_one_index = $i;
        }
    }

    $base_row = [
        'acf_fc_layout' => 'content_one',
        'heading' => 'Thinking About This Property?',
        'heading_tag' => 'h2',
        'description' => '<p>Interested in this property? Contact our team today for viewing details and next steps.</p>',
        'left_description' => '<p>We can help with enquiries, scheduling viewings, and guidance through the process.</p>',
    ];

    if ($content_one_index >= 0) {
        $existing = is_array($rows[$content_one_index]) ? $rows[$content_one_index] : [];

        // Do not overwrite deliberate custom content. Fill only empty essentials.
        $merged = $existing;
        if (empty(trim((string) ($existing['heading'] ?? '')))) {
            $merged['heading'] = $base_row['heading'];
        }
        if (empty(trim((string) ($existing['heading_tag'] ?? '')))) {
            $merged['heading_tag'] = $base_row['heading_tag'];
        }
        if (empty(trim(wp_strip_all_tags((string) ($existing['description'] ?? ''))))) {
            $merged['description'] = $base_row['description'];
        }
        if (empty(trim(wp_strip_all_tags((string) ($existing['left_description'] ?? ''))))) {
            $merged['left_description'] = $base_row['left_description'];
        }
        $rows[$content_one_index] = $merged;

        // Reposition to required order (after full_width_media, else after property_data).
        $content_one_row = $rows[$content_one_index];
        array_splice($rows, $content_one_index, 1);
        $insert_at = ($full_width_media_index >= 0 ? $full_width_media_index + 1 : ($property_data_index >= 0 ? $property_data_index + 1 : count($rows)));
        if ($content_one_index < $insert_at) {
            $insert_at--;
        }
        array_splice($rows, max(0, $insert_at), 0, [$content_one_row]);

        update_field('flexible_content_blocks', $rows, $post_id);
        return ['status' => 'updated'];
    }

    $insert_at = ($full_width_media_index >= 0 ? $full_width_media_index + 1 : ($property_data_index >= 0 ? $property_data_index + 1 : count($rows)));
    array_splice($rows, $insert_at, 0, [$base_row]);
    update_field('flexible_content_blocks', $rows, $post_id);
    return ['status' => 'added'];
}

function matrix_daft_sync_gallery_block(int $post_id, array $listing, bool $allow_placeholders = false): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $image_urls = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $hero_url = matrix_daft_media_compare_key((string) ($listing['image_url'] ?? ''));
    $full_width_media_url = matrix_daft_media_compare_key((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));

    // Use the next 4 images after hero/full-width selections.
    $gallery_urls = [];
    $gallery_keys = [];
    foreach ($image_urls as $candidate) {
        $candidate_key = matrix_daft_media_compare_key((string) $candidate);
        if ($candidate_key === '') {
            continue;
        }
        if ($hero_url !== '' && $candidate_key === $hero_url) {
            continue;
        }
        if ($full_width_media_url !== '' && $candidate_key === $full_width_media_url) {
            continue;
        }
        if (in_array($candidate_key, $gallery_keys, true)) {
            continue;
        }
        $gallery_urls[] = matrix_daft_normalize_remote_url((string) $candidate);
        $gallery_keys[] = $candidate_key;
        if (count($gallery_urls) >= 4) {
            break;
        }
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $content_one_index = -1;
    $gallery_index = -1;
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content_one' && $content_one_index === -1) {
            $content_one_index = $i;
        }
        if ($layout === 'gallery_001' && $gallery_index === -1) {
            $gallery_index = $i;
        }
    }

    $gallery_images = [];
    foreach ($gallery_urls as $idx => $url) {
        $attachment_id = matrix_daft_resolve_media_attachment_for_post(
            $post_id,
            $url,
            '_matrix_daft_gallery_' . ($idx + 1)
        );
        if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
            continue;
        }
        $gallery_images[] = [
            'image' => (int) $attachment_id,
        ];
    }

    if (empty($gallery_images) && $allow_placeholders) {
        $placeholder_urls = [
            'https://via.placeholder.com/1600x1000/e5e7eb/6b7280?text=Gallery+Image+1',
            'https://via.placeholder.com/1600x1000/dbeafe/374151?text=Gallery+Image+2',
            'https://via.placeholder.com/1600x1000/fef3c7/374151?text=Gallery+Image+3',
            'https://via.placeholder.com/1600x1000/d1fae5/374151?text=Gallery+Image+4',
        ];

        foreach ($placeholder_urls as $idx => $url) {
            $attachment_id = matrix_daft_resolve_media_attachment_for_post(
                $post_id,
                $url,
                '_matrix_daft_gallery_placeholder_' . ($idx + 1)
            );
            if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
                continue;
            }
            $gallery_images[] = [
                'image' => (int) $attachment_id,
            ];
        }
    }

    if (empty($gallery_images)) {
        if ($allow_placeholders) {
            $existing_display_mode = '';
            if ($gallery_index >= 0 && is_array($rows[$gallery_index])) {
                $existing_display_mode = (string) ($rows[$gallery_index]['display_mode'] ?? '');
            }
            if ($existing_display_mode === '') {
                $existing_display_mode = 'grid';
            }

            $gallery_row = [
                'acf_fc_layout' => 'gallery_001',
                'display_mode' => $existing_display_mode,
                'gallery_images' => [],
            ];

            $existing_had_gallery = $gallery_index >= 0;
            if ($gallery_index >= 0) {
                $existing = is_array($rows[$gallery_index]) ? $rows[$gallery_index] : [];
                $rows[$gallery_index] = array_merge($existing, $gallery_row);
                $gallery_row = $rows[$gallery_index];
                array_splice($rows, $gallery_index, 1);
                if ($gallery_index < $content_one_index) {
                    $content_one_index--;
                }
            }

            $insert_at = $content_one_index >= 0 ? $content_one_index + 1 : count($rows);
            array_splice($rows, max(0, $insert_at), 0, [$gallery_row]);
            update_field('flexible_content_blocks', $rows, $post_id);
            update_post_meta($post_id, '_matrix_daft_gallery_primary_urls', wp_json_encode([]));
            return ['status' => $existing_had_gallery ? 'updated-placeholders' : 'added-placeholders'];
        }

        if ($gallery_index >= 0) {
            array_splice($rows, $gallery_index, 1);
            update_field('flexible_content_blocks', $rows, $post_id);
            delete_post_meta($post_id, '_matrix_daft_gallery_primary_urls');
            return ['status' => 'removed-no-gallery-images'];
        }
        delete_post_meta($post_id, '_matrix_daft_gallery_primary_urls');
        return ['status' => 'skipped-no-gallery-images'];
    }

    $existing_display_mode = '';
    if ($gallery_index >= 0 && is_array($rows[$gallery_index])) {
        $existing_display_mode = (string) ($rows[$gallery_index]['display_mode'] ?? '');
    }
    if ($existing_display_mode === '') {
        $existing_display_mode = 'grid';
    }

    $gallery_row = [
        'acf_fc_layout' => 'gallery_001',
        // Preserve editor-set toggle when row already exists.
        'display_mode' => $existing_display_mode,
        'gallery_images' => $gallery_images,
    ];

    $existing_had_gallery = $gallery_index >= 0;
    if ($gallery_index >= 0) {
        $existing = is_array($rows[$gallery_index]) ? $rows[$gallery_index] : [];
        $rows[$gallery_index] = array_merge($existing, $gallery_row);
        $gallery_row = $rows[$gallery_index];
        array_splice($rows, $gallery_index, 1);
        if ($gallery_index < $content_one_index) {
            $content_one_index--;
        }
    }

    $insert_at = $content_one_index >= 0 ? $content_one_index + 1 : count($rows);
    array_splice($rows, max(0, $insert_at), 0, [$gallery_row]);
    update_field('flexible_content_blocks', $rows, $post_id);

    $used_placeholders = false;
    if ($allow_placeholders) {
        $first_id = (int) ($gallery_images[0]['image'] ?? 0);
        if ($first_id > 0) {
            $source_url = (string) wp_get_attachment_url($first_id);
            $used_placeholders = str_contains($source_url, 'via.placeholder.com');
        }
    }

    if ($used_placeholders) {
        update_post_meta($post_id, '_matrix_daft_gallery_primary_urls', wp_json_encode([]));
        return ['status' => $existing_had_gallery ? 'updated-placeholders' : 'added-placeholders'];
    }

    update_post_meta($post_id, '_matrix_daft_gallery_primary_urls', wp_json_encode(array_values($gallery_urls)));

    return ['status' => $existing_had_gallery ? 'updated' : 'added'];
}

function matrix_daft_build_consultation_link(int $post_id, string $query_type = 'request_a_call', string $query_type_label = 'Request a call'): array {
    $property_title = $post_id ? get_the_title($post_id) : '';
    $property_address = $post_id ? ((string) get_post_meta($post_id, 'daft_address', true)) : '';
    if ($property_address === '') {
        $property_address = $property_title;
    }

    $property_type_terms = $post_id ? get_the_terms($post_id, 'property_type') : [];
    $property_type_name = (!empty($property_type_terms) && !is_wp_error($property_type_terms)) ? (string) $property_type_terms[0]->name : '';
    $bedrooms = $post_id ? (string) get_post_meta($post_id, 'bedrooms', true) : '';
    $bathrooms = $post_id ? (string) get_post_meta($post_id, 'bathrooms', true) : '';
    $property_url = $post_id ? get_permalink($post_id) : '';

    $url = add_query_arg([
        'from_property' => 1,
        'query_type' => $query_type,
        'query_type_label' => $query_type_label,
        'property_id' => $post_id ?: '',
        'property_url' => $property_url,
        'property_address' => $property_address,
        'property_type' => $property_type_name,
        'bedrooms' => $bedrooms,
        'bathrooms' => $bathrooms,
    ], home_url('/book-a-consultation/'));

    return [
        'url' => $url,
        'target' => '_self',
    ];
}

function matrix_daft_sync_content_one_followup_block(int $post_id): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $first_content_one_index = -1;
    $gallery_index = -1;
    $followup_index = -1;

    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content_one' && $first_content_one_index === -1) {
            $first_content_one_index = $i;
            continue;
        }
        if ($layout === 'gallery_001' && $gallery_index === -1) {
            $gallery_index = $i;
            continue;
        }
    }

    // Follow-up content_one is the first content_one after the primary one.
    foreach ($rows as $i => $row) {
        if (!is_array($row) || (($row['acf_fc_layout'] ?? '') !== 'content_one')) {
            continue;
        }
        if ($i > $first_content_one_index) {
            $followup_index = $i;
            break;
        }
    }

    $link = matrix_daft_build_consultation_link($post_id, 'book_consultation', 'Book a consultation');
    $base_row = [
        'acf_fc_layout' => 'content_one',
        'heading' => 'Ready To Talk Through Your Options?',
        'heading_tag' => 'h2',
        'description' => '<p>Book a consultation with our team and we will walk you through next steps for this property.</p>',
        'left_description' => '<p>We can answer questions on viewings, timelines, and the buying process.</p>',
        'button_link' => [
            'title' => 'Book a consultation',
            'url' => esc_url_raw((string) ($link['url'] ?? home_url('/book-a-consultation/'))),
            'target' => '_self',
        ],
    ];

    if ($followup_index >= 0) {
        $existing = is_array($rows[$followup_index]) ? $rows[$followup_index] : [];
        $merged = $existing;
        if (empty(trim((string) ($existing['heading'] ?? '')))) {
            $merged['heading'] = $base_row['heading'];
        }
        if (empty(trim((string) ($existing['heading_tag'] ?? '')))) {
            $merged['heading_tag'] = $base_row['heading_tag'];
        }
        if (empty(trim(wp_strip_all_tags((string) ($existing['description'] ?? ''))))) {
            $merged['description'] = $base_row['description'];
        }
        if (empty(trim(wp_strip_all_tags((string) ($existing['left_description'] ?? ''))))) {
            $merged['left_description'] = $base_row['left_description'];
        }
        if (empty($existing['button_link']) || !is_array($existing['button_link']) || empty($existing['button_link']['url'])) {
            $merged['button_link'] = $base_row['button_link'];
        }
        $rows[$followup_index] = $merged;

        $followup_row = $rows[$followup_index];
        array_splice($rows, $followup_index, 1);
        $insert_at = $gallery_index >= 0 ? $gallery_index + 1 : ($first_content_one_index >= 0 ? $first_content_one_index + 1 : count($rows));
        if ($followup_index < $insert_at) {
            $insert_at--;
        }
        array_splice($rows, max(0, $insert_at), 0, [$followup_row]);
        update_field('flexible_content_blocks', $rows, $post_id);
        return ['status' => 'updated'];
    }

    $insert_at = $gallery_index >= 0 ? $gallery_index + 1 : ($first_content_one_index >= 0 ? $first_content_one_index + 1 : count($rows));
    array_splice($rows, max(0, $insert_at), 0, [$base_row]);
    update_field('flexible_content_blocks', $rows, $post_id);
    return ['status' => 'added'];
}

function matrix_daft_sync_gallery_carousel_block(int $post_id, array $listing): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $content_one_indices = [];
    $gallery_indices = [];
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content_one') {
            $content_one_indices[] = $i;
        } elseif ($layout === 'gallery_001') {
            $gallery_indices[] = $i;
        }
    }

    $followup_content_one_index = isset($content_one_indices[1]) ? (int) $content_one_indices[1] : -1;
    if ($followup_content_one_index < 0) {
        return ['status' => 'skipped-missing-followup-content-one'];
    }

    $carousel_index = -1;
    foreach ($gallery_indices as $idx) {
        if ($idx > $followup_content_one_index) {
            $carousel_index = (int) $idx;
            break;
        }
    }

    $image_urls = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $hero_url = matrix_daft_media_compare_key((string) ($listing['image_url'] ?? ''));
    $first_media_url = matrix_daft_media_compare_key((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));
    $primary_gallery_urls = json_decode((string) get_post_meta($post_id, '_matrix_daft_gallery_primary_urls', true), true);
    if (!is_array($primary_gallery_urls)) {
        $primary_gallery_urls = [];
    }
    $primary_gallery_urls = array_values(array_filter(array_map('matrix_daft_media_compare_key', $primary_gallery_urls)));

    $exclude = array_values(array_filter(array_unique(array_merge([$hero_url, $first_media_url], $primary_gallery_urls))));
    $carousel_urls = [];
    $carousel_keys = [];
    foreach ($image_urls as $candidate) {
        $candidate_key = matrix_daft_media_compare_key((string) $candidate);
        if ($candidate_key === '' || in_array($candidate_key, $exclude, true) || in_array($candidate_key, $carousel_keys, true)) {
            continue;
        }
        $carousel_urls[] = matrix_daft_normalize_remote_url((string) $candidate);
        $carousel_keys[] = $candidate_key;
        if (count($carousel_urls) >= 4) {
            break;
        }
    }

    if (empty($carousel_urls)) {
        if ($carousel_index >= 0) {
            array_splice($rows, $carousel_index, 1);
            update_field('flexible_content_blocks', $rows, $post_id);
            delete_post_meta($post_id, '_matrix_daft_gallery_carousel_urls');
            return ['status' => 'removed-no-carousel-images'];
        }
        delete_post_meta($post_id, '_matrix_daft_gallery_carousel_urls');
        return ['status' => 'skipped-no-carousel-images'];
    }

    $gallery_images = [];
    foreach ($carousel_urls as $idx => $url) {
        $attachment_id = matrix_daft_resolve_media_attachment_for_post($post_id, $url, '_matrix_daft_gallery_carousel_' . ($idx + 1));
        if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
            continue;
        }
        $gallery_images[] = ['image' => (int) $attachment_id];
    }

    if (empty($gallery_images)) {
        return ['status' => 'skipped-carousel-download-failed'];
    }

    $carousel_row = [
        'acf_fc_layout' => 'gallery_001',
        'display_mode' => 'carousel',
        'gallery_images' => $gallery_images,
    ];

    $existing_had = $carousel_index >= 0;
    if ($carousel_index >= 0) {
        $existing = is_array($rows[$carousel_index]) ? $rows[$carousel_index] : [];
        $rows[$carousel_index] = array_merge($existing, $carousel_row);
        $carousel_row = $rows[$carousel_index];
        array_splice($rows, $carousel_index, 1);
        if ($carousel_index < $followup_content_one_index) {
            $followup_content_one_index--;
        }
    }

    $insert_at = $followup_content_one_index + 1;
    array_splice($rows, max(0, $insert_at), 0, [$carousel_row]);
    update_field('flexible_content_blocks', $rows, $post_id);
    update_post_meta($post_id, '_matrix_daft_gallery_carousel_urls', wp_json_encode(array_values($carousel_urls)));

    return ['status' => $existing_had ? 'updated' : 'added'];
}

function matrix_daft_sync_secondary_full_width_media_block(int $post_id, array $listing, bool $allow_image_fallback = true): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $content_one_indices = [];
    $gallery_indices = [];
    $full_width_media_indices = [];
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content_one') {
            $content_one_indices[] = $i;
        } elseif ($layout === 'gallery_001') {
            $gallery_indices[] = $i;
        } elseif ($layout === 'full_width_media') {
            $full_width_media_indices[] = $i;
        }
    }

    $followup_content_one_index = isset($content_one_indices[1]) ? (int) $content_one_indices[1] : -1;
    $carousel_gallery_index = -1;
    foreach ($gallery_indices as $idx) {
        if ($idx > $followup_content_one_index) {
            $carousel_gallery_index = (int) $idx;
            break;
        }
    }
    if ($carousel_gallery_index < 0) {
        return ['status' => 'skipped-missing-carousel-gallery'];
    }

    $secondary_media_index = -1;
    foreach ($full_width_media_indices as $idx) {
        if ($idx > $carousel_gallery_index) {
            $secondary_media_index = (int) $idx;
            break;
        }
    }

    $video_url = '';
    $video_provider = '';
    $video_candidates = is_array($listing['video_urls'] ?? null) ? $listing['video_urls'] : [];
    foreach ($video_candidates as $candidate) {
        if (!is_string($candidate) || !filter_var($candidate, FILTER_VALIDATE_URL)) {
            continue;
        }
        $host = strtolower((string) wp_parse_url($candidate, PHP_URL_HOST));
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            $video_provider = 'youtube';
            $video_url = $candidate;
            break;
        }
        if (str_contains($host, 'vimeo.com') || str_contains($host, 'player.vimeo.com')) {
            $video_provider = 'vimeo';
            $video_url = $candidate;
            break;
        }
    }

    $image_urls = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $hero_url = matrix_daft_media_compare_key((string) ($listing['image_url'] ?? ''));
    $first_media_url = matrix_daft_media_compare_key((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));
    $primary_gallery_urls = json_decode((string) get_post_meta($post_id, '_matrix_daft_gallery_primary_urls', true), true);
    $carousel_gallery_urls = json_decode((string) get_post_meta($post_id, '_matrix_daft_gallery_carousel_urls', true), true);
    if (!is_array($primary_gallery_urls)) {
        $primary_gallery_urls = [];
    }
    if (!is_array($carousel_gallery_urls)) {
        $carousel_gallery_urls = [];
    }

    $exclude = array_values(array_filter(array_unique(array_merge(
        [$hero_url, $first_media_url],
        array_map('matrix_daft_media_compare_key', $primary_gallery_urls),
        array_map('matrix_daft_media_compare_key', $carousel_gallery_urls)
    ))));

    $secondary_image_url = '';
    $secondary_attachment_id = 0;
    foreach ($image_urls as $candidate) {
        $candidate_key = matrix_daft_media_compare_key((string) $candidate);
        if ($candidate_key === '' || in_array($candidate_key, $exclude, true)) {
            continue;
        }
        $candidate_url = matrix_daft_normalize_remote_url((string) $candidate);
        if ($candidate_url === '') {
            continue;
        }
        $candidate_attachment_id = matrix_daft_resolve_media_attachment_for_post($post_id, $candidate_url, '_matrix_daft_full_width_media_secondary');
        if (is_wp_error($candidate_attachment_id) || !is_numeric($candidate_attachment_id)) {
            continue;
        }
        $candidate_attachment_id = (int) $candidate_attachment_id;
        if (matrix_daft_attachment_is_portrait($candidate_attachment_id)) {
            continue;
        }
        $secondary_image_url = $candidate_url;
        $secondary_attachment_id = $candidate_attachment_id;
        break;
    }

    $media_row = null;
    if ($video_url !== '' && $video_provider !== '') {
        $media_row = [
            'acf_fc_layout' => 'full_width_media',
            'media_type' => 'video',
            'video_provider' => $video_provider,
            'video_url' => esc_url_raw($video_url),
            'height_xs' => 400,
            'height_md' => 500,
            'autopause_on_scroll' => 1,
            'muted' => 0,
            'click_toggle_pause' => 1,
        ];
    } elseif ($allow_image_fallback && $secondary_image_url !== '') {
        $media_row = [
            'acf_fc_layout' => 'full_width_media',
            'media_type' => 'image',
            'image' => (int) $secondary_attachment_id,
            'height_xs' => 400,
            'height_md' => 500,
        ];
    }

    if ($media_row === null) {
        if ($secondary_media_index >= 0) {
            array_splice($rows, $secondary_media_index, 1);
            update_field('flexible_content_blocks', $rows, $post_id);
            return ['status' => 'removed-no-secondary-media'];
        }
        return ['status' => 'skipped-no-secondary-media'];
    }

    $existing_had = $secondary_media_index >= 0;
    if ($secondary_media_index >= 0) {
        $existing = is_array($rows[$secondary_media_index]) ? $rows[$secondary_media_index] : [];
        $rows[$secondary_media_index] = array_merge($existing, $media_row);
        $media_row = $rows[$secondary_media_index];
        array_splice($rows, $secondary_media_index, 1);
        if ($secondary_media_index < $carousel_gallery_index) {
            $carousel_gallery_index--;
        }
    }

    $insert_at = $carousel_gallery_index + 1;
    array_splice($rows, max(0, $insert_at), 0, [$media_row]);
    update_field('flexible_content_blocks', $rows, $post_id);
    return ['status' => $existing_had ? 'updated' : 'added'];
}

function matrix_daft_sync_cta_block(int $post_id): array {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return ['status' => 'skipped-no-acf'];
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $content_one_indices = [];
    $gallery_indices = [];
    $full_width_media_indices = [];
    $cta_indices = [];
    foreach ($rows as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content_one') {
            $content_one_indices[] = $i;
        } elseif ($layout === 'gallery_001') {
            $gallery_indices[] = $i;
        } elseif ($layout === 'full_width_media') {
            $full_width_media_indices[] = $i;
        } elseif ($layout === 'cta_001') {
            $cta_indices[] = $i;
        }
    }

    $followup_content_one_index = isset($content_one_indices[1]) ? (int) $content_one_indices[1] : -1;
    $carousel_gallery_index = -1;
    foreach ($gallery_indices as $idx) {
        if ($idx > $followup_content_one_index) {
            $carousel_gallery_index = (int) $idx;
            break;
        }
    }
    $secondary_media_index = -1;
    foreach ($full_width_media_indices as $idx) {
        if ($idx > $carousel_gallery_index) {
            $secondary_media_index = (int) $idx;
            break;
        }
    }
    $cta_index = -1;
    foreach ($cta_indices as $idx) {
        if ($secondary_media_index >= 0 && $idx > $secondary_media_index) {
            $cta_index = (int) $idx;
            break;
        }
    }
    if ($cta_index < 0 && !empty($cta_indices)) {
        $cta_index = (int) $cta_indices[0];
    }

    $link = matrix_daft_build_consultation_link($post_id, 'arrange_viewing', 'Arrange a viewing');
    $base_row = [
        'acf_fc_layout' => 'cta_001',
        'heading' => 'Want To Arrange A Viewing?',
        'heading_tag' => 'h2',
        'description' => '<p>Speak with our team to book a viewing and discuss this property in more detail.</p>',
        'background_color' => '#020617',
        'button' => [
            'title' => 'Arrange a viewing',
            'url' => esc_url_raw((string) ($link['url'] ?? home_url('/book-a-consultation/'))),
            'target' => '_self',
        ],
    ];

    if ($cta_index >= 0) {
        $existing = is_array($rows[$cta_index]) ? $rows[$cta_index] : [];
        $merged = $existing;
        if (empty(trim((string) ($existing['heading'] ?? '')))) {
            $merged['heading'] = $base_row['heading'];
        }
        if (empty(trim((string) ($existing['heading_tag'] ?? '')))) {
            $merged['heading_tag'] = $base_row['heading_tag'];
        }
        if (empty(trim(wp_strip_all_tags((string) ($existing['description'] ?? ''))))) {
            $merged['description'] = $base_row['description'];
        }
        if (empty($existing['button']) || !is_array($existing['button']) || empty($existing['button']['url'])) {
            $merged['button'] = $base_row['button'];
        }
        if (empty(trim((string) ($existing['background_color'] ?? '')))) {
            $merged['background_color'] = $base_row['background_color'];
        }
        $rows[$cta_index] = $merged;

        $cta_row = $rows[$cta_index];
        array_splice($rows, $cta_index, 1);
        $insert_at = $secondary_media_index >= 0
            ? $secondary_media_index + 1
            : ($carousel_gallery_index >= 0 ? $carousel_gallery_index + 1 : ($followup_content_one_index >= 0 ? $followup_content_one_index + 1 : count($rows)));
        if ($cta_index < $insert_at) {
            $insert_at--;
        }
        array_splice($rows, max(0, $insert_at), 0, [$cta_row]);
        update_field('flexible_content_blocks', $rows, $post_id);
        return ['status' => 'updated'];
    }

    $insert_at = $secondary_media_index >= 0
        ? $secondary_media_index + 1
        : ($carousel_gallery_index >= 0 ? $carousel_gallery_index + 1 : ($followup_content_one_index >= 0 ? $followup_content_one_index + 1 : count($rows)));
    array_splice($rows, max(0, $insert_at), 0, [$base_row]);
    update_field('flexible_content_blocks', $rows, $post_id);
    return ['status' => 'added'];
}

function matrix_daft_ensure_large_hero_block(int $post_id): void {
    if (!function_exists('get_field') || !function_exists('update_field')) {
        return;
    }

    $rows = get_field('hero_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $hero_row = null;
    $other_rows = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        if (($row['acf_fc_layout'] ?? '') === 'large_hero_image' && $hero_row === null) {
            $hero_row = $row;
            continue;
        }

        $other_rows[] = $row;
    }

    if ($hero_row === null) {
        $hero_row = ['acf_fc_layout' => 'large_hero_image'];
    }

    // Keep consistent defaults for this hero layout.
    if (empty($hero_row['height_xs'])) {
        $hero_row['height_xs'] = 500;
    }
    if (empty($hero_row['height_md'])) {
        $hero_row['height_md'] = 665;
    }

    // Ensure large_hero_image exists and is first.
    $new_rows = array_merge([$hero_row], $other_rows);
    update_field('hero_content_blocks', $new_rows, $post_id);
}

function matrix_daft_sync_taxonomies(int $post_id, array $listing): void {
    if (!empty($listing['status'])) {
        $term = term_exists($listing['status'], 'property_status');
        if (!$term) {
            $term = wp_insert_term($listing['status'], 'property_status');
        }
        if (!is_wp_error($term) && !empty($term['term_id'])) {
            wp_set_post_terms($post_id, [(int) $term['term_id']], 'property_status', false);
        }
    }

    if (!empty($listing['type'])) {
        $term = term_exists($listing['type'], 'property_type');
        if (!$term) {
            $term = wp_insert_term($listing['type'], 'property_type');
        }
        if (!is_wp_error($term) && !empty($term['term_id'])) {
            wp_set_post_terms($post_id, [(int) $term['term_id']], 'property_type', false);
        }
    }
}

function matrix_daft_sync_featured_image(int $post_id, array $image_urls, bool $force_update = false): void {
    $image_urls = array_values(array_filter(array_map('strval', $image_urls)));
    if (empty($image_urls)) {
        return;
    }

    if (!$force_update && has_post_thumbnail($post_id)) {
        $existing_thumb_id = (int) get_post_thumbnail_id($post_id);
        if ($existing_thumb_id > 0 && !matrix_daft_attachment_is_too_small($existing_thumb_id)) {
            return;
        }
    }

    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    foreach ($image_urls as $image_url) {
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            continue;
        }
        $attachment_id = matrix_daft_sideload_image_from_url($post_id, $image_url);
        if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
            continue;
        }

        $attachment_id = (int) $attachment_id;
        if (matrix_daft_attachment_is_too_small($attachment_id)) {
            continue;
        }

        set_post_thumbnail($post_id, $attachment_id);
        return;
    }
}

function matrix_daft_attachment_is_too_small(int $attachment_id): bool {
    $meta = wp_get_attachment_metadata($attachment_id);
    if (!is_array($meta)) {
        return false;
    }
    $width = (int) ($meta['width'] ?? 0);
    $height = (int) ($meta['height'] ?? 0);
    if ($width <= 0 || $height <= 0) {
        return false;
    }
    // Reject tiny thumbnails as featured images.
    return ($width < 700 || $height < 450);
}

function matrix_daft_attachment_is_portrait(int $attachment_id): bool {
    $meta = wp_get_attachment_metadata($attachment_id);
    if (!is_array($meta)) {
        return false;
    }
    $width = (int) ($meta['width'] ?? 0);
    $height = (int) ($meta['height'] ?? 0);
    if ($width <= 0 || $height <= 0) {
        return false;
    }
    return $height > $width;
}

function matrix_daft_resolve_media_attachment_for_post(int $post_id, string $image_url, string $meta_prefix) {
    $url_key = $meta_prefix . '_url';
    $id_key = $meta_prefix . '_id';

    $existing_url = (string) get_post_meta($post_id, $url_key, true);
    $existing_id = (int) get_post_meta($post_id, $id_key, true);
    if ($existing_url === $image_url && $existing_id > 0) {
        return $existing_id;
    }

    $attachment_id = matrix_daft_sideload_image_from_url($post_id, $image_url);
    if (!is_wp_error($attachment_id) && is_numeric($attachment_id)) {
        update_post_meta($post_id, $url_key, esc_url_raw($image_url));
        update_post_meta($post_id, $id_key, (int) $attachment_id);
    }

    return $attachment_id;
}

function matrix_daft_sideload_image_from_url(int $post_id, string $image_url) {
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');
    if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
        $attachment_id = matrix_daft_sideload_image_fallback($post_id, $image_url);
    }

    return $attachment_id;
}

function matrix_daft_sideload_image_fallback(int $post_id, string $image_url) {
    if (!function_exists('download_url') || !function_exists('media_handle_sideload')) {
        return new WP_Error('daft_missing_helpers', 'WordPress media sideload helpers are unavailable.');
    }

    $tmp_file = download_url($image_url, 30);
    if (is_wp_error($tmp_file)) {
        $tmp_file = matrix_daft_download_image_with_headers($image_url);
        if (is_wp_error($tmp_file)) {
            return $tmp_file;
        }
    }

    $mime_type = '';
    if (function_exists('mime_content_type')) {
        $detected = @mime_content_type($tmp_file);
        if (is_string($detected)) {
            $mime_type = $detected;
        }
    }

    $ext = 'jpg';
    if (str_contains($mime_type, 'png')) {
        $ext = 'png';
    } elseif (str_contains($mime_type, 'webp')) {
        $ext = 'webp';
    } elseif (str_contains($mime_type, 'gif')) {
        $ext = 'gif';
    } elseif (str_contains($mime_type, 'jpeg') || str_contains($mime_type, 'jpg')) {
        $ext = 'jpg';
    }

    $file_array = [
        'name' => 'daft-' . md5($image_url) . '.' . $ext,
        'tmp_name' => $tmp_file,
    ];

    $attachment_id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp_file);
    }

    return $attachment_id;
}

function matrix_daft_download_image_with_headers(string $image_url) {
    $response = wp_remote_get($image_url, [
        'timeout' => 30,
        'redirection' => 5,
        'headers' => [
            'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/'),
            'Referer' => home_url('/'),
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    if ($status < 200 || $status >= 300) {
        return new WP_Error('daft_image_http_error', sprintf('Image download failed with HTTP %d.', $status));
    }

    $body = (string) wp_remote_retrieve_body($response);
    if ($body === '') {
        return new WP_Error('daft_image_empty_body', 'Image download returned an empty body.');
    }

    $tmp_file = wp_tempnam('daft-image');
    if (!$tmp_file) {
        return new WP_Error('daft_temp_file_failed', 'Unable to create temporary file for image download.');
    }

    $written = @file_put_contents($tmp_file, $body);
    if ($written === false || $written <= 0) {
        @unlink($tmp_file);
        return new WP_Error('daft_temp_write_failed', 'Unable to write downloaded image to temporary file.');
    }

    return $tmp_file;
}

function matrix_daft_store_report(
    string $trigger,
    int $imported,
    int $updated,
    int $skipped,
    string $error = '',
    array $items = [],
    array $debug = []
): void {
    update_option('matrix_daft_last_report', [
        'synced_at' => current_time('mysql'),
        'trigger' => $trigger,
        'imported' => $imported,
        'updated' => $updated,
        'skipped' => $skipped,
        'error' => $error,
        'items' => array_values($items),
        'debug' => $debug,
    ], false);
}

function matrix_daft_add_source_column(array $columns): array {
    $inserted = [];
    foreach ($columns as $key => $label) {
        $inserted[$key] = $label;
        if ($key === 'title') {
            $inserted['matrix_property_source'] = 'Source';
        }
    }

    if (!isset($inserted['matrix_property_source'])) {
        $inserted['matrix_property_source'] = 'Source';
    }

    return $inserted;
}

function matrix_daft_render_source_column(string $column, int $post_id): void {
    if ($column !== 'matrix_property_source') {
        return;
    }

    $source = (string) get_post_meta($post_id, '_matrix_property_source', true);
    $legacy_daft_id = (string) get_post_meta($post_id, '_matrix_daft_id', true);

    if ($source === 'daft' || $source === 'daft_mock' || $legacy_daft_id !== '') {
        echo 'Daft';
        return;
    }

    echo 'Manual';
}

function matrix_daft_register_property_sync_metabox(): void {
    add_meta_box(
        'matrix-daft-sync-controls',
        'Daft Sync Controls',
        'matrix_daft_render_property_sync_metabox',
        'property',
        'side',
        'default'
    );
}

function matrix_daft_render_property_sync_metabox(WP_Post $post): void {
    if (!current_user_can('edit_post', $post->ID)) {
        return;
    }
    wp_nonce_field('matrix_daft_property_sync_controls', 'matrix_daft_property_sync_nonce');

    $fields = [
        '_matrix_daft_disable_sync' => 'Disable all Daft updates for this property',
        '_matrix_daft_disable_image_sync' => 'Disable Daft image sync (featured + galleries)',
        '_matrix_daft_disable_primary_gallery' => 'Disable first gallery section (grid/modal)',
        '_matrix_daft_disable_carousel_gallery' => 'Disable carousel gallery section',
        '_matrix_daft_disable_secondary_media' => 'Disable secondary full width media section',
        '_matrix_daft_disable_followup_content' => 'Disable follow-up Content One section',
        '_matrix_daft_disable_cta_section' => 'Disable CTA section',
    ];

    echo '<p style="margin-bottom:10px;">Control what Daft can overwrite on future sync runs.</p>';
    foreach ($fields as $meta_key => $label) {
        $checked = (bool) get_post_meta($post->ID, $meta_key, true);
        echo '<p style="margin:0 0 8px 0;">';
        echo '<label>';
        echo '<input type="checkbox" name="matrix_daft_sync_controls[' . esc_attr($meta_key) . ']" value="1" ' . checked($checked, true, false) . ' /> ';
        echo esc_html($label);
        echo '</label>';
        echo '</p>';
    }
}

function matrix_daft_save_property_sync_metabox(int $post_id, WP_Post $post): void {
    if ($post->post_type !== 'property') {
        return;
    }
    if (!isset($_POST['matrix_daft_property_sync_nonce']) || !wp_verify_nonce((string) $_POST['matrix_daft_property_sync_nonce'], 'matrix_daft_property_sync_controls')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $posted = isset($_POST['matrix_daft_sync_controls']) && is_array($_POST['matrix_daft_sync_controls'])
        ? $_POST['matrix_daft_sync_controls']
        : [];

    $meta_keys = [
        '_matrix_daft_disable_sync',
        '_matrix_daft_disable_image_sync',
        '_matrix_daft_disable_primary_gallery',
        '_matrix_daft_disable_carousel_gallery',
        '_matrix_daft_disable_secondary_media',
        '_matrix_daft_disable_followup_content',
        '_matrix_daft_disable_cta_section',
    ];

    foreach ($meta_keys as $meta_key) {
        $enabled = !empty($posted[$meta_key]);
        if ($enabled) {
            update_post_meta($post_id, $meta_key, 1);
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }
}

function matrix_daft_get_property_sync_controls(int $post_id): array {
    return [
        'disable_sync' => (bool) get_post_meta($post_id, '_matrix_daft_disable_sync', true),
        'disable_image_sync' => (bool) get_post_meta($post_id, '_matrix_daft_disable_image_sync', true),
        'disable_primary_gallery' => (bool) get_post_meta($post_id, '_matrix_daft_disable_primary_gallery', true),
        'disable_carousel_gallery' => (bool) get_post_meta($post_id, '_matrix_daft_disable_carousel_gallery', true),
        'disable_secondary_media' => (bool) get_post_meta($post_id, '_matrix_daft_disable_secondary_media', true),
        'disable_followup_content' => (bool) get_post_meta($post_id, '_matrix_daft_disable_followup_content', true),
        'disable_cta_section' => (bool) get_post_meta($post_id, '_matrix_daft_disable_cta_section', true),
    ];
}

function matrix_daft_request_endpoint(
    string $endpoint,
    string $method,
    string $auth_value,
    string $header_name,
    string $body_json = ''
): array {
    $args = [
        'timeout' => 30,
        'method' => $method,
        'headers' => [
            $header_name => $auth_value,
            'Accept' => 'application/json',
        ],
    ];

    if ($method === 'POST' && $body_json !== '') {
        $decoded = json_decode($body_json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $args['body'] = wp_json_encode($decoded);
            $args['headers']['Content-Type'] = 'application/json';
        }
    }

    $candidates = matrix_daft_endpoint_candidates($endpoint);
    $attempted = [];
    $final_response = null;
    $final_endpoint = $endpoint;

    foreach ($candidates as $candidate) {
        $attempted[] = $candidate;
        $response = wp_remote_request($candidate, $args);
        $final_response = $response;
        $final_endpoint = $candidate;

        if (is_wp_error($response)) {
            break;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code !== 404) {
            break;
        }
    }

    return [
        'response' => $final_response,
        'endpoint' => $final_endpoint,
        'attempted_endpoints' => $attempted,
    ];
}

function matrix_daft_endpoint_candidates(string $endpoint): array {
    $candidates = [$endpoint];

    $trimmed = rtrim($endpoint, '/');
    if ($trimmed !== $endpoint) {
        $candidates[] = $trimmed;
    } else {
        $candidates[] = $endpoint . '/';
    }

    $parts = wp_parse_url($trimmed);
    $path = $parts['path'] ?? '';
    $scheme = $parts['scheme'] ?? 'https';
    $host = $parts['host'] ?? '';
    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';

    if ($host !== '' && str_starts_with($path, '/v')) {
        $candidates[] = $scheme . '://' . $host . '/api' . $path . $query;
    }

    if ($host !== '' && str_starts_with($path, '/api/v')) {
        $candidates[] = $scheme . '://' . $host . substr($path, 4) . $query;
    }

    return array_values(array_unique(array_filter($candidates)));
}

function matrix_daft_format_http_error(
    int $status_code,
    string $method,
    string $endpoint,
    string $body,
    array $attempted_endpoints = []
): string {
    $snippet = trim(wp_strip_all_tags((string) $body));
    if ($snippet !== '') {
        $snippet = preg_replace('/\s+/', ' ', $snippet);
        $snippet = function_exists('mb_substr') ? mb_substr($snippet, 0, 240) : substr($snippet, 0, 240);
    }

    $base = sprintf('Daft API returned HTTP %d (%s %s).', $status_code, $method, $endpoint);

    if ($status_code === 404) {
        $base .= ' The endpoint path is not valid for your Daft API access.';
    }

    if (!empty($attempted_endpoints)) {
        $base .= ' Tried: ' . implode(' | ', array_map('esc_url_raw', $attempted_endpoints)) . '.';
    }

    if ($snippet !== '') {
        $base .= ' Response: ' . $snippet;
    }

    return $base;
}
