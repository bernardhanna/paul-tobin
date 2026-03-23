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
    $soap_operation = (string) matrix_daft_get_option('daft_soap_operation', 'search_sale');
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
                    SOAP `<?php echo esc_html($soap_operation); ?>` via <?php echo esc_html($soap_wsdl_url); ?>
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
                                        'media_sync' => 'Media',
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
                                        echo ' <span style="color:#646970;">(' . esc_html('Daft ID ' . $item_daft_id . ')</span>');
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
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($report['error'])) : ?>
                        <p><strong>Error:</strong> <?php echo esc_html($report['error']); ?></p>
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
    $soap_operation = trim((string) matrix_daft_get_option('daft_soap_operation', 'search_sale'));
    $soap_query_json = trim((string) matrix_daft_get_option('daft_soap_query_json', ''));
    $mock_payload_json = trim((string) matrix_daft_get_option('daft_mock_payload_json', ''));
    $mock_gallery_placeholders = (bool) matrix_daft_get_option('daft_mock_gallery_placeholders', true);
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
    if ($request_mode === 'mock') {
        $mock_result = matrix_daft_request_mock_data($mock_payload_json);
        if (!$mock_result['success']) {
            matrix_daft_store_report($trigger, 0, 0, 0, $mock_result['message']);
            return ['success' => false, 'message' => $mock_result['message']];
        }

        $payload = $mock_result['payload'];
    } elseif ($request_mode === 'soap') {
        if ($soap_wsdl_url === '' || $soap_operation === '') {
            $message = 'SOAP WSDL URL or SOAP operation is missing.';
            matrix_daft_store_report($trigger, 0, 0, 0, $message);
            return ['success' => false, 'message' => $message];
        }

        $soap_result = matrix_daft_request_soap($soap_wsdl_url, $soap_operation, $api_key, $soap_query_json);
        if (!$soap_result['success']) {
            matrix_daft_store_report($trigger, 0, 0, 0, $soap_result['message']);
            return ['success' => false, 'message' => $soap_result['message']];
        }

        $payload = $soap_result['payload'];
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
    }

    $items = matrix_daft_extract_listing_items($payload);
    if (empty($items)) {
        $message = 'No listing items were found in the Daft response.';
        matrix_daft_store_report($trigger, 0, 0, 0, $message);
        return ['success' => false, 'message' => $message];
    }

    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $source_label = $request_mode === 'mock' ? 'daft_mock' : 'daft';
    $synced_items = [];

    foreach ($items as $raw_item) {
        if (!is_array($raw_item)) {
            $skipped++;
            continue;
        }

        $listing = matrix_daft_normalize_listing($raw_item);
        if ($listing['daft_id'] === '' && $listing['title'] === '' && $listing['address'] === '') {
            $skipped++;
            continue;
        }

        if ($listing['daft_id'] === '') {
            $listing['daft_id'] = md5(wp_json_encode([$listing['title'], $listing['address']]));
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

        if (!empty($listing['image_url']) && empty($controls['disable_image_sync'])) {
            matrix_daft_sync_featured_image($post_id, $listing['image_url'], $update_images);
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
            ];
        }
    }

    $message = sprintf(
        'Daft sync complete. Imported: %d, Updated: %d, Skipped: %d.',
        $imported,
        $updated,
        $skipped
    );

    matrix_daft_store_report($trigger, $imported, $updated, $skipped, '', $synced_items);
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
    $single = matrix_daft_pick($item, ['small_thumbnail_url', 'large_thumbnail_url'], '');
    if (is_string($single) && filter_var($single, FILTER_VALIDATE_URL)) {
        $urls[] = $single;
    }
    if (!is_array($candidates)) {
        return array_values(array_unique($urls));
    }

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
            $urls[] = $candidate;
            continue;
        }

        if (is_array($candidate)) {
            foreach (['url', 'src', 'large', 'small', 'thumbnail'] as $key) {
                if (!empty($candidate[$key]) && is_string($candidate[$key]) && filter_var($candidate[$key], FILTER_VALIDATE_URL)) {
                    $urls[] = $candidate[$key];
                    break;
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

    return array_values(array_unique($urls));
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

    $parameters = [
        'api_key' => $api_key,
    ];

    if ($query_json !== '') {
        $query = json_decode($query_json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($query)) {
            $parameters['query'] = $query;
        }
    }

    try {
        $client = new SoapClient($wsdl_url, [
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'trace' => false,
        ]);

        if (!method_exists($client, $operation)) {
            return [
                'success' => false,
                'message' => sprintf('SOAP operation "%s" is not available on %s.', $operation, $wsdl_url),
                'payload' => [],
            ];
        }

        $result = $client->{$operation}($parameters);
        $payload = json_decode(wp_json_encode($result), true);

        if (!is_array($payload)) {
            return [
                'success' => false,
                'message' => 'SOAP response could not be converted to an array.',
                'payload' => [],
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'payload' => $payload,
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'message' => sprintf('SOAP request failed (%s %s): %s', $operation, $wsdl_url, $e->getMessage()),
            'payload' => [],
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
    $address = trim((string) ($listing['address'] ?? ''));
    $description = trim((string) ($listing['description'] ?? ''));

    $overview_bits = [];
    if ($property_type !== '') {
        $overview_bits[] = esc_html($property_type) . ': Property Type';
    }
    if ($bedrooms !== '') {
        $overview_bits[] = 'Bedrooms ' . esc_html($bedrooms);
    }
    if ($bathrooms !== '') {
        $overview_bits[] = 'Bathroom ' . esc_html($bathrooms);
    }
    if ($area !== '') {
        $overview_bits[] = esc_html($area);
    }

    $right_text_parts = [];
    $meta_line = [];
    if ($price !== '') {
        $meta_line[] = '<strong>' . esc_html($price) . '</strong>';
    }
    if ($status !== '') {
        $meta_line[] = esc_html($status);
    }
    if ($address !== '') {
        $meta_line[] = esc_html($address);
    }
    if (!empty($meta_line)) {
        $right_text_parts[] = '<p>' . implode('<br>', $meta_line) . '</p>';
    }
    if (!empty($overview_bits)) {
        $right_text_parts[] = '<h3>Overview</h3><p>' . implode('<br>', $overview_bits) . '</p>';
    }
    if ($description !== '') {
        $right_text_parts[] = '<h3>Description</h3><p>' . esc_html($description) . '</p>';
    }
    $right_text = implode("\n", $right_text_parts);

    $extra_rows = [];
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

    $property_data_row = [
        'acf_fc_layout' => 'property_data',
        'sector' => $property_type,
        'size' => $area !== '' ? '<p>Area: ' . esc_html($area) . '</p>' : '',
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
    $normalize_url = static function ($url): string {
        if (!is_string($url) || $url === '') {
            return '';
        }
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        return rtrim($url, '/');
    };

    $rows = get_field('flexible_content_blocks', $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }

    $primary_hero_url = $normalize_url((string) ($listing['image_url'] ?? ''));
    $featured_url = '';
    $thumb_id = get_post_thumbnail_id($post_id);
    if ($thumb_id) {
        $featured_src = wp_get_attachment_image_url($thumb_id, 'full');
        if (is_string($featured_src)) {
            $featured_url = $normalize_url($featured_src);
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
                    $existing_media_url = $normalize_url($existing_src);
                }
            }
        }
    }

    // IMPORTANT: Never duplicate the hero/featured image in full_width_media.
    $media_url = '';
    foreach ($image_urls as $candidate) {
        if (!is_string($candidate)) {
            continue;
        }
        $candidate_url = $normalize_url($candidate);
        if ($candidate_url === '') {
            continue;
        }
        if (in_array($candidate_url, $hero_urls, true)) {
            continue;
        }
        $media_url = $candidate_url;
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

    $attachment_id = matrix_daft_resolve_media_attachment_for_post(
        $post_id,
        $media_url,
        '_matrix_daft_full_width_media'
    );

    if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
        return ['status' => 'skipped-image-download-failed'];
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

    $normalize_url = static function ($url): string {
        if (!is_string($url) || $url === '') {
            return '';
        }
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
            return rtrim($url, '/');
        }
        $scheme = !empty($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
        $host = strtolower((string) $parts['host']);
        $path = (string) $parts['path'];
        return rtrim($scheme . '://' . $host . $path, '/');
    };

    $image_urls = is_array($listing['image_urls'] ?? null) ? $listing['image_urls'] : [];
    $hero_url = $normalize_url((string) ($listing['image_url'] ?? ''));
    $full_width_media_url = $normalize_url((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));

    // Use the next 4 images after hero/full-width selections.
    $gallery_urls = [];
    foreach ($image_urls as $candidate) {
        $candidate_url = $normalize_url($candidate);
        if ($candidate_url === '') {
            continue;
        }
        if ($hero_url !== '' && $candidate_url === $hero_url) {
            continue;
        }
        if ($full_width_media_url !== '' && $candidate_url === $full_width_media_url) {
            continue;
        }
        if (in_array($candidate_url, $gallery_urls, true)) {
            continue;
        }
        $gallery_urls[] = $candidate_url;
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

    $normalize_url = static function ($url): string {
        if (!is_string($url) || $url === '') {
            return '';
        }
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
            return rtrim($url, '/');
        }
        $scheme = !empty($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
        $host = strtolower((string) $parts['host']);
        $path = (string) $parts['path'];
        return rtrim($scheme . '://' . $host . $path, '/');
    };

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
    $hero_url = $normalize_url((string) ($listing['image_url'] ?? ''));
    $first_media_url = $normalize_url((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));
    $primary_gallery_urls = json_decode((string) get_post_meta($post_id, '_matrix_daft_gallery_primary_urls', true), true);
    if (!is_array($primary_gallery_urls)) {
        $primary_gallery_urls = [];
    }
    $primary_gallery_urls = array_values(array_filter(array_map($normalize_url, $primary_gallery_urls)));

    $exclude = array_values(array_filter(array_unique(array_merge([$hero_url, $first_media_url], $primary_gallery_urls))));
    $carousel_urls = [];
    foreach ($image_urls as $candidate) {
        $candidate_url = $normalize_url($candidate);
        if ($candidate_url === '' || in_array($candidate_url, $exclude, true) || in_array($candidate_url, $carousel_urls, true)) {
            continue;
        }
        $carousel_urls[] = $candidate_url;
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

    $normalize_url = static function ($url): string {
        if (!is_string($url) || $url === '') {
            return '';
        }
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
            return rtrim($url, '/');
        }
        $scheme = !empty($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
        $host = strtolower((string) $parts['host']);
        $path = (string) $parts['path'];
        return rtrim($scheme . '://' . $host . $path, '/');
    };

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
    $hero_url = $normalize_url((string) ($listing['image_url'] ?? ''));
    $first_media_url = $normalize_url((string) get_post_meta($post_id, '_matrix_daft_full_width_media_url', true));
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
        array_map($normalize_url, $primary_gallery_urls),
        array_map($normalize_url, $carousel_gallery_urls)
    ))));

    $secondary_image_url = '';
    foreach ($image_urls as $candidate) {
        $candidate_url = $normalize_url($candidate);
        if ($candidate_url === '' || in_array($candidate_url, $exclude, true)) {
            continue;
        }
        $secondary_image_url = $candidate_url;
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
        $attachment_id = matrix_daft_resolve_media_attachment_for_post($post_id, $secondary_image_url, '_matrix_daft_full_width_media_secondary');
        if (is_wp_error($attachment_id) || !is_numeric($attachment_id)) {
            return ['status' => 'skipped-secondary-image-download-failed'];
        }
        $media_row = [
            'acf_fc_layout' => 'full_width_media',
            'media_type' => 'image',
            'image' => (int) $attachment_id,
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

function matrix_daft_sync_featured_image(int $post_id, string $image_url, bool $force_update = false): void {
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        return;
    }

    if (!$force_update && has_post_thumbnail($post_id)) {
        return;
    }

    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $attachment_id = matrix_daft_sideload_image_from_url($post_id, $image_url);

    if (!is_wp_error($attachment_id) && is_numeric($attachment_id)) {
        set_post_thumbnail($post_id, (int) $attachment_id);
    }
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
        return $tmp_file;
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

function matrix_daft_store_report(
    string $trigger,
    int $imported,
    int $updated,
    int $skipped,
    string $error = '',
    array $items = []
): void {
    update_option('matrix_daft_last_report', [
        'synced_at' => current_time('mysql'),
        'trigger' => $trigger,
        'imported' => $imported,
        'updated' => $updated,
        'skipped' => $skipped,
        'error' => $error,
        'items' => array_values($items),
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
