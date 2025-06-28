<?php
if ( ! defined( 'ABSPATH' ) ) exit;


function nafis_api_request($method, $endpoint, $token = null, $params = [], $body = null, $timeout = 10) {
    $url = trailingslashit(NAFIS_EXPRESS_SHIPPING_BASE_API) . ltrim($endpoint, '/');

    if ($method === 'GET' && !empty($params)) {
        $url = add_query_arg($params, $url);
    }

    $headers = [
        'Content-Type' => 'application/json',
        'Accept'       => 'application/json',
    ];

    if ($token) {
        $headers['token'] = sanitize_text_field($token); // یا Authorization
    }

    $args = [
        'headers' => $headers,
        'timeout' => $timeout,
    ];

    if ($method === 'POST' || $method === 'PUT') {
        $args['body'] = wp_json_encode($body ?? []);
    }
    
    $response = wp_remote_request($url, array_merge($args, ['method' => strtoupper($method)]));
    
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("❌ Nafis API Error ({$endpoint}): " . $response->get_error_message());
        }
        return ['success' => false, 'data' => null];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['status']) || !$body['status']) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("⚠️ Nafis API response issue ({$endpoint}): " . wp_remote_retrieve_body($response));
        }
        return ['success' => false, 'data' => $body];
    }

    return ['success' => true, 'data' => $body['response'] ?? []];
}
