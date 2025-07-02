<?php
if (!defined('ABSPATH')) {
    exit;
}

// Utility function to register an endpoint
function nafis_register_api_route($route, $callback, $methods = ['GET'], $permission_callback = null) {
    register_rest_route(NAFIS_EXPRESS_SHIPPING_API_NAMESPACE, $route, [
        'methods'             => $methods,
        'callback'            => $callback,
        'permission_callback' => $permission_callback ?: '__return_true',
    ]);
}

// Check if REST API is blocked and show notice
function nafis_check_rest_api_status() {
    $rest_disabled = get_option('permalink_structure') === '';
    if ($rest_disabled || strpos(get_rest_url(), 'rest_route') !== false) {
        return new WP_Error('rest_api_blocked', 'REST API appears to be disabled or permalinks are not properly configured.', ['status' => 400]);
    }

    return true;
}

class NafisApiResponse {
    // Operation result: general response
    public static function success($data = [], $message = 'Operation succeeded') {
        return new WP_REST_Response([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    public static function fail($message = 'Operation failed', $data = [], $status = 400) {
        return new WP_REST_Response([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    // Problem Details standard (RFC 7807)
    public static function problem($type, $title, $status, $detail = '', $instance = '') {
        return new WP_REST_Response([
            'type'     => 'https://docs.nafisexpress.com/problems/' . $type,
            'title'    => $title,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => $instance,
        ], $status);
    }

    // Optional: Convert WP_Error to problem detail
    public static function from_wp_error(WP_Error $error, $status = 400) {
        return self::problem(
            $error->get_error_code(),
            'Error: ' . $error->get_error_message(),
            $status,
            $error->get_error_message()
        );
    }
}

// Require Apis
require_once NAFIS_EXPRESS_SHIPPING_INC_API . "web-hook-revalidate-province-city.php";
require_once NAFIS_EXPRESS_SHIPPING_INC_API . "ping.php";