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
        $reordered_columns['nafis_barcode_column'] = esc_html__('Nafis Express Actions', 'nafis-express-shipping');

        return $reordered_columns;
    }
);

// Add custom column with barcode issue button
add_action('manage_woocommerce_page_wc-orders_custom_column', function ($column, $order) {
    if ($column !== 'nafis_barcode_column') return;

    $options = get_option('nafis_express_shipping_settings', []);
    $selected_status = $options['barcode_status'] ?? 'wc-processing';
    $order_status = $order->get_status();
    $is_disabled = $order_status !== str_replace('wc-', '', $selected_status);

    $order_id = $order->get_id();

    $nafis_express_data = get_option("nafis_express_data");
    $boxes = nafis_get_cached_boxes();

    // // companyID: z.union([z.string(), z.number()]),
    // // receiverFirstName: z.string().min(1, { message: 'نام گیرنده الزامی است' }),
    // // receiverLastName: z.string().min(1, { message: 'نام خانوادگی گیرنده الزامی است' }),
    // // receiverMobile: z.string().min(1, { message: 'شماره موبایل گیرنده الزامی است' }),
    // postType: z.number().min(1, { message: 'نوع پست الزامی است' }),
    // cityID: z.union([z.string(), z.number()]),
    // // address: z.string().min(1, { message: 'آدرس الزامی است' }),
    // warehouseBoxID: z.union([z.string(), z.number()]),
    // count: z.string().min(1, { message: 'تعداد الزامی است' }),
    // weight: z.string().min(1, { message: 'وزن الزامی است' }),

    $data = [
        'companyID'                     => sanitize_text_field($nafis_express_data['companyID']),
        'receiverFirstName'             => trim($order->get_billing_first_name() ?: $order->get_shipping_first_name()),
        'receiverLastName'              => trim($order->get_billing_last_name() ?: $order->get_shipping_last_name()),
        'receiverMobile'                => $order->get_billing_phone() ?: $order->get_shipping_phone(),
        'postcode'                      => $order->get_billing_postcode() ?: $order->get_shipping_postcode(),
        'address'                       => trim($order->get_billing_address_1() ?: $order->get_shipping_address_1()),
        'provinceCode'                  => $order->get_billing_state() ?: $order->get_shipping_state(),
        'city'                          => $order->get_billing_city() ?: $order->get_shipping_city(),
        'products'                      => array_values(array_map(function ($item) {
            return [
                'name' => $item->get_name(),
                'qty'  => $item->get_quantity(),
            ];
        }, $order->get_items())),
        'warehouseBoxes' => array_values(array_map(function ($box) {
            return [
                'id'     => $box['postSizeId'],
                'label'  => sprintf(
                    __('کد %1$s — %2$dx%3$dx%4$d — %5$dg', 'nafis-express-shipping'),
                    $box['barcode'],
                    $box['lenght'], // احتمالاً غلط املایی، باید اصلاح بشه
                    $box['width'],
                    $box['height'],
                    $box['weight']
                ),
            ];
        }, $boxes)),
    ];

    printf(
        '<button type="button" class="button nafis-barcode-btn" %s data-order="%s" data-order-id="%d">%s</button>',
        $is_disabled ? 'disabled="disabled"' : '',
        esc_attr(json_encode($data)),
        $order_id,
        esc_html__('Issue Barcode', 'nafis-express-shipping')
    );
}, 20, 2);