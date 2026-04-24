<?php
/**
 * WP Rocket + theme / webpack-dev-server compatibility.
 *
 * Delay/defer/minify can break load order or strip attributes so scripts throw;
 * the webpack-dev-server overlay then shows a generic "Script error." with no stack.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Path fragments matched inside full <script> tags (Delay JS) or URL paths (defer / minify).
 *
 * @return string[]
 */
function matrix_starter_rocket_js_exclusion_fragments(): array {
    return array(
        'matrix-starter/dist/app\\.js',
        'matrix-starter/inc/forms/js/forms\\.js',
        '@alpinejs/intersect',
        // booking_form.php inline: query-type / nice-select + validation (Delay JS breaks show/hide for "Other").
        'matrixNiceListDelegation',
        'initQueryTypeConditionalUI',
        'webpack-dev-server',
        'sockjs-node',
        '\\.hot-update\\.json',
    );
}

/**
 * @param string[] $excluded
 * @return string[]
 */
function matrix_starter_rocket_delay_js_exclusions($excluded) {
    if (!defined('WP_ROCKET_VERSION')) {
        return $excluded;
    }
    return array_values(array_unique(array_merge((array) $excluded, matrix_starter_rocket_js_exclusion_fragments())));
}

/**
 * @param string[] $excluded
 * @return string[]
 */
function matrix_starter_rocket_exclude_defer_js($excluded) {
    if (!defined('WP_ROCKET_VERSION')) {
        return $excluded;
    }
    return array_values(array_unique(array_merge((array) $excluded, matrix_starter_rocket_js_exclusion_fragments())));
}

/**
 * Exclude theme JS from minify/combine (path regex segment list, pipe-joined by Rocket).
 *
 * @param string[] $excluded
 * @return string[]
 */
function matrix_starter_rocket_exclude_js($excluded) {
    if (!defined('WP_ROCKET_VERSION')) {
        return $excluded;
    }
    $more = array(
        '/wp-content/themes/matrix-starter/dist/(.*)\\.js',
        '/wp-content/themes/matrix-starter/inc/forms/js/forms\\.js',
    );
    return array_values(array_unique(array_merge((array) $excluded, $more)));
}

add_action('init', static function () {
    if (!defined('WP_ROCKET_VERSION')) {
        return;
    }
    add_filter('rocket_delay_js_exclusions', 'matrix_starter_rocket_delay_js_exclusions');
    add_filter('rocket_exclude_defer_js', 'matrix_starter_rocket_exclude_defer_js');
    add_filter('rocket_exclude_js', 'matrix_starter_rocket_exclude_js');
}, 20);

/**
 * Request host is loopback (e.g. http://localhost:10059/) even if WP_ENVIRONMENT_TYPE is production.
 *
 * @return bool
 */
function matrix_starter_is_loopback_http_host(): bool {
    if (empty($_SERVER['HTTP_HOST']) || !is_string($_SERVER['HTTP_HOST'])) {
        return false;
    }
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']));

    return in_array($host, array('localhost', '127.0.0.1', '::1'), true);
}

/**
 * True when we should soften WP Rocket JS (delay/defer/minify) for local tooling.
 * Includes loopback so you can set WP_ENVIRONMENT_TYPE to production and still avoid
 * dev-server "Script error." spam on http://localhost:*.
 *
 * Disable loopback behaviour: add_filter('matrix_starter_rocket_relax_on_loopback', '__return_false');
 */
function matrix_starter_is_wp_rocket_dev_relax(): bool {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        return true;
    }
    if (function_exists('wp_get_environment_type')) {
        $env = wp_get_environment_type();
        if ($env === 'development' || $env === 'local') {
            return true;
        }
    }
    if (apply_filters('matrix_starter_rocket_relax_on_loopback', true) && matrix_starter_is_loopback_http_host()) {
        return true;
    }

    return false;
}

/**
 * In development, turn off WP Rocket JS transforms that break load order or trigger
 * anonymous "Script error." in the webpack-dev-server overlay (cross-origin / delayed scripts).
 * Production Rocket settings are unchanged.
 */
if (defined('WP_ROCKET_VERSION') && matrix_starter_is_wp_rocket_dev_relax()) {
    $matrix_starter_rocket_dev_false = static function () {
        return false;
    };
    add_filter('pre_get_rocket_option_delay_js', $matrix_starter_rocket_dev_false, 1);
    add_filter('pre_get_rocket_option_defer_all_js', $matrix_starter_rocket_dev_false, 1);
    add_filter('pre_get_rocket_option_minify_js', $matrix_starter_rocket_dev_false, 1);
    add_filter('pre_get_rocket_option_minify_concatenate_js', $matrix_starter_rocket_dev_false, 1);
}
