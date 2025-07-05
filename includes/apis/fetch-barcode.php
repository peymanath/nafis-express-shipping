<?php if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:ignoreFile WordPress.Security.NonceVerification.Recommended

add_action('wp_ajax_nafis_track_barcode', function () {
    if (!Nafis_Nonce::validate_ajax('nafis_track_nonce', 'nafis_track_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
    }

    $barcode = sanitize_text_field(wp_unslash($_GET['barcode'] ?? ''));
    $token   = get_option('nafis_express_data')['token'] ?? null;

    if (!$barcode || !$token) {
        wp_send_json_error(['message' => 'ورودی نامعتبر.']);
    }

    $res = nafis_api_request('GET', 'panel/site/barcode-statuses', $token, ['Barcode' => $barcode]);

    if (!$res['success']) {
        wp_send_json_error(['message' => 'خطا در ارتباط یا اطلاعات نامعتبر.']);
    }

    wp_send_json($res['data']);
});

function nafis_fetch_exited_orders($token, $order_id = null)
{
    $paged_raw = isset($_GET['paged']) ? wp_unslash($_GET['paged']) : '';
    $current_page = max(1, absint($paged_raw));

    $query = [
        'PageIndex' => $current_page,
        'PageSize'  => 20,
    ];

    if ($order_id) {
        $query['OrderID'] = sanitize_text_field(wp_unslash($order_id));
    } else {
        if (!empty($_GET['barcode'])) {
            $query['Barcode'] = sanitize_text_field(wp_unslash($_GET['barcode']));
        }

        if (!empty($_GET['order_id'])) {
            $query['OrderID'] = sanitize_text_field(wp_unslash($_GET['order_id']));
        }

        if (!empty($_GET['status'])) {
            $query['BarcodeStatus'] = sanitize_text_field(wp_unslash($_GET['status']));
        }

        if (!empty($_GET['branch_id'])) {
            $query['BranchID'] = sanitize_text_field(wp_unslash($_GET['branch_id']));
        }

        if (!empty($_GET['receiverName'])) {
            $query['receiverName'] = sanitize_text_field(wp_unslash($_GET['receiverName']));
        }

        if (!empty($_GET['city'])) {
            $query['CityID'] = sanitize_text_field(wp_unslash($_GET['city']));
        }

        if (!empty($_GET['post_type'])) {
            $query['BarcodeType'] = sanitize_text_field(wp_unslash($_GET['post_type']));
        }

        if (!empty($_GET['date_to'])) {
            $query['DateTo'] = sanitize_text_field(wp_unslash($_GET['date_to']));
        }

        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
            $query['DateFrom'] = sanitize_text_field(wp_unslash($_GET['date_from']));
        } elseif (!isset($_GET['date_from'])) {
            $query['DateFrom'] = current_time('Y-m-d');
        }
    }

    $result = nafis_api_request('GET', '/panel/ordersexited', $token, $query);

    if (!$result['success']) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_data = is_array($result['data']) ? json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $result['data'];
            error_log('⚠️ Failed to fetch exited orders: ' . $log_data);
        }        
        return ['data' => [], 'total' => 0];
    }

    return [
        'data'  => $result['data']['data'] ?? [],
        'total' => $result['data']['totalRecords'] ?? 0,
    ];
}