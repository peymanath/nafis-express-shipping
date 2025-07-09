<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_action('wp_ajax_nafis_track_barcode', function () {
    check_ajax_referer('nafis_nonce', 'nonce');
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'دسترسی غیرمجاز.']);
    }

    $barcode = sanitize_text_field(wp_unslash($_GET['barcode'] ?? ''));
    $auth = NafisOptionAuthentication::get();
    $token   = $auth['token'] ?? null;

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
    $current_page = isset($_GET['paged']) ? max(1, intval(wp_unslash($_GET['paged']))) : 1;

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
            error_log('⚠️ Failed to fetch exited orders: ' . print_r($result['data'], true));
        }
        return ['data' => [], 'total' => 0];
    }

    return [
        'data'  => $result['data']['data'] ?? [],
        'total' => $result['data']['totalRecords'] ?? 0,
    ];
}


// array(2) {
//   ["success"]=>
//   bool(true)
//   ["data"]=>
//   array(9) {
//     ["companyTitle"]=>
//     string(44) "سلامت تدبیر اصیل مهر آسا"
//     ["startCity"]=>
//     string(44) "سلامت تدبیر اصیل مهر آسا"
//     ["finalCity"]=>
//     string(12) "اصفهان"
//     ["receiverName"]=>
//     string(21) "محسن  عزیزی "
//     ["receiverMobile"]=>
//     string(11) "09132011391"
//     ["orderID"]=>
//     int(9239662)
//     ["weight"]=>
//     int(7140)
//     ["agentName"]=>
//     string(23) "محمدرضا خلجی"
//     ["logs"]=>
//     array(7) {
//       [0]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(0) ""
//         ["statusTitle"]=>
//         string(28) "ثبت اولیه بارکد"
//         ["statusDateTime"]=>
//         string(23) "2025-06-15T12:47:23.297"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/25 12:47:23"
//       }
//       [1]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(34) "شعبه مرکزی اشتهارد"
//         ["statusTitle"]=>
//         string(22) "ورود به شعبه"
//         ["statusDateTime"]=>
//         string(23) "2025-06-15T12:47:25.513"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/25 12:47:25"
//       }
//       [2]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(0) ""
//         ["statusTitle"]=>
//         string(28) "مانیفست از شعبه"
//         ["statusDateTime"]=>
//         string(23) "2025-06-15T15:10:35.427"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/25 15:10:35"
//       }
//       [3]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(0) ""
//         ["statusTitle"]=>
//         string(43) "در حال حرکت به شعبه مقصد"
//         ["statusDateTime"]=>
//         string(23) "2025-06-15T15:10:35.427"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/25 15:10:35"
//       }
//       [4]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(34) "نفیس اکسپرس اصفهان"
//         ["statusTitle"]=>
//         string(22) "ورود به شعبه"
//         ["statusDateTime"]=>
//         string(22) "2025-06-16T07:12:25.39"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/26 07:12:25"
//       }
//       [5]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(34) "نفیس اکسپرس اصفهان"
//         ["statusTitle"]=>
//         string(28) "تحویل به راننده"
//         ["statusDateTime"]=>
//         string(23) "2025-06-16T09:53:16.033"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/26 09:53:16"
//       }
//       [6]=>
//       array(4) {
//         ["branchTitle"]=>
//         string(34) "نفیس اکسپرس اصفهان"
//         ["statusTitle"]=>
//         string(55) "تحویل داده شد  (تحویل با کد ملی)"
//         ["statusDateTime"]=>
//         string(23) "2025-06-16T18:39:56.133"
//         ["statusDateTimeString"]=>
//         string(19) "1404/03/26 18:39:56"
//       }
//     }
//   }
// }