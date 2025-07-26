<?php

if (!defined('ABSPATH')) exit; // Prevent direct access to the plugin file

// Add custom columns to WooCommerce Orders list (custom admin page)
add_filter(
    'manage_woocommerce_page_wc-orders_columns',
    function ($columns) {
        $reordered_columns = [];

        foreach ($columns as $key => $label) {
            $reordered_columns[$key] = $label;
        }

        // Add our custom column at the end
        $reordered_columns['nafis_barcode_column'] = esc_html__('Nafis Express', 'nafis-express-shipping');

        return $reordered_columns;
    }
);

// Add custom column with barcode issue button
add_action('manage_woocommerce_page_wc-orders_custom_column', function ($column, $order) {
    if ($column !== 'nafis_barcode_column') return;

    $options = NafisOptionSetting::get([]);
    $selected_status = $options['barcode_status'] ?? 'wc-processing';
    $order_status = $order->get_status();


    $order_id = $order->get_id();

    $auth_data = NafisOptionAuthentication::get();
    $boxes = NafisOptionCachedBoxes::get([]);

    $provinces     = NafisOptionCachedProvinces::get([]);
    $cached_cities = NafisOptionCachedCities::get([]);

    // استان
    $state_raw = $order->get_billing_state() ?: $order->get_shipping_state();
    $province_id = (int) str_replace('nafis_', '', $state_raw);
    $province_name = '';
    foreach ($provinces as $province) {
        if ((int) $province['id'] === $province_id) {
            $province_name = $province['name'];
            break;
        }
    }

    // شهر
    $city_name = normalize_fa($order->get_billing_city() ?: $order->get_shipping_city());
    $city_supported = false;
    $city_id = null;

    foreach ($cached_cities as $city) {
        if (normalize_fa($city['name']) === $city_name) {
            $city_id = (int) $city['id'];
            // var_dump($city);
            $city_supported = !empty($city['supported']);
            break;
        }
    }

    $is_disabled = (
        $order_status !== str_replace('wc-', '', $selected_status)
        || !$city_supported
    );

    $data = [
        'orderID'           => $order->get_id(),
        'companyID'         => sanitize_text_field($auth_data["companyID"]),
        'receiverFirstName' => trim($order->get_billing_first_name() ?: $order->get_shipping_first_name()),
        'receiverLastName'  => trim($order->get_billing_last_name() ?: $order->get_shipping_last_name()),
        'receiverMobile'    => $order->get_billing_phone() ?: $order->get_shipping_phone(),
        'postcode'          => $order->get_billing_postcode() ?: $order->get_shipping_postcode(),
        'address'           => trim($order->get_billing_address_1() ?: $order->get_shipping_address_1()),

        // استان: شامل هم ID و هم نام
        'province' => [
            'id'   => $province_id,
            'name' => $province_name,
        ],

        // شهر: شامل هم ID و هم نام
        'city' => [
            'id'   => $city_id,
            'name' => $city_name,
        ],

        'products' => array_values(array_map(function ($item) {
            $product = $item->get_product();
            if (!$product) return [];

            $product_id = $product->get_id();

            return [
                'id'                    => $product_id,
                'title'                 => $product->get_name(),
                'qty'                   => $item->get_quantity(),
                'length'                => get_post_meta($product_id, '_length', true),
                'width'                 => get_post_meta($product_id, '_width', true),
                'height'                => get_post_meta($product_id, '_height', true),
                'weight'                => get_post_meta($product_id, '_weight', true),
                'nafis_box_size'        => get_post_meta($product_id, '_nafis_box_size', true),
                'nafis_delivery_time'   => get_post_meta($product_id, '_nafis_delivery_time', true),
                'productID'             => (int) $product_id,
                'productBarcode'        => get_post_meta($product_id, '_nafis_product_barcode', true) ?: '', // اگر متا خاصی داری
                'brandName'             => get_the_terms($product_id, 'product_brand')[0]->name ?? '',       // اگر taxonomy استفاده می‌کنی
                'productName'           => $product->get_name(),
                'count'                 => $item->get_quantity(),
                'photo'                 => wp_get_attachment_url($product->get_image_id()) ?: '',
            ];
        }, $order->get_items())),

        'warehouseBoxes' => array_values(array_map(function ($box) {
            return [
                'id'    => $box['postSizeId'],
                'label' => sprintf(
                    __('کد %1$s — %2$dx%3$dx%4$d — %5$dg', 'nafis-express-shipping'),
                    $box['barcode'],
                    $box['lenght'], // ← احتمالاً غلط املایی، درستش کن اگر لازمه
                    $box['width'],
                    $box['height'],
                    $box['weight']
                ),
            ];
        }, $boxes)),

        // 'barcodes'      => NafisPostMetaBarcodes::get($order->get_id()),
        'barcodes'      => NafisPostMetaBarcodes::get("1163053"),
    ];

    $disabled_attr = $is_disabled ? 'disabled="disabled"' : '';
    $barcode_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M2 4h1v16H2V4zm3 0h2v16H5V4zm4 0h1v16H9V4zm2 0h1v16h-1V4zm3 0h2v16h-2V4zm4 0h1v16h-1V4zm3 0h1v16h-1V4z"/></svg>';

    printf(
        '<div style="display:flex; gap:6px;">
        <button type="button" class="button nafis-barcode-btn" %s 
            data-order="%s" data-order-id="%d" title="%s"
            style="width:34px; height:34px; padding:0; display:flex; align-items:center; justify-content:center;">
            %s
        </button>
        <button type="button" class="button nafis-print-label-btn" %s 
            data-order-id="%d" title="%s"
            style="width:34px; height:34px; padding:0; display:flex; align-items:center; justify-content:center;">
            <span class="dashicons dashicons-printer" style="font-size:18px;"></span>
        </button>
    </div>',
        $disabled_attr,
        esc_attr(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        $order_id,
        esc_attr__('صدور بارکد', 'nafis-express-shipping'),
        $barcode_icon_svg,
        $disabled_attr,
        $order_id,
        esc_attr__('چاپ لیبل', 'nafis-express-shipping')
    );
}, 20, 2);

add_action('wp_ajax_nafis_issue_barcode', function () {
    $payload_json = $_GET['payload'] ?? '';
    $payload = json_decode(stripslashes($payload_json), true);

    if (
        !isset($_GET['nonce']) ||
        !wp_verify_nonce($_GET['nonce'], 'nafis_nonce')
    ) {
        wp_send_json_error(['message' => 'دسترسی نامعتبر (nonce).']);
    }

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'دسترسی غیرمجاز.']);
    }

    if (!$payload || !is_array($payload)) {
        wp_send_json_error(['message' => 'payload نامعتبر است']);
    }

    $token = NafisOptionAuthentication::get()['token'] ?? null;
    if (!$token) {
        wp_send_json_error(['message' => 'توکن یافت نشد']);
    }

    $orders = [];

    foreach ($payload['packaging'] as $index => $box) {
        $orders[] = [
            'barcode'               => '',
            'orderID'               => (int) $payload['customer']['orderID'],
            'boxNumber'             => $index + 1,
            'receiverName'          => $payload['customer']['firstName'] . ' ' . $payload['customer']['lastName'],
            'receiverMobile'        => $payload['customer']['mobile'],
            'receiverPhone'         => $payload['customer']['mobile'],
            'receiverNationalCode'  => '0130177059', // Hard Code
            'postalCode'            => $payload['customer']['postcode'],
            'address'               => $payload['customer']['address'],
            'cityID'                => (int) $payload['customer']['city']['id'],
            'zone'                  => 0,
            'latitude'              => '',
            'longitude'             => '',
            'weight'                => (int) $box['weight'],
            'reserveWorkCapacityId' => null,
            'warehouseBoxId'        => (int) $box['boxNumber'],
            'products'              => array_map(function ($p) {
                return [
                    'productID'      => (int) ($p['id'] ?? 0),
                    'productBarcode' => "1",
                    'brandName'      => "1",
                    'productName'    => $p['title'],
                    'count'          => (int) $p['qty'],
                    'photo'          => "1",
                ];
            }, $payload['products']),
        ];
    }

    // درخواست به API نفیس اکسپرس
    $res = nafis_api_request('POST', 'customer/order-exited/create-barcodes', $token, [], [
        'orders' => $orders,
    ]);

    // پاسخ خام رو برگردون برای بررسی
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'orders' => $orders,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
});
