<?php

if (!defined('ABSPATH')) exit;

define('NAFIS_ORDER_STATUS', 'wc-nafis-barcode'); // Final status name - must be <= 20 characters

/**
 * Register custom order status: Ready for Barcode
 */
add_filter('woocommerce_register_shop_order_post_statuses', function ($statuses) {
    $statuses[NAFIS_ORDER_STATUS] = [
        'label'                     => __('Ready for Barcode', 'nafis-express-shipping'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop(
            'Ready for Barcode <span class="count">(%s)</span>',
            'Ready for Barcode <span class="count">(%s)</span>',
            'nafis-express-shipping'
        ),
    ];
    return $statuses;
});


/**
 * Add the status to admin dropdowns, filters and everywhere Woo shows order statuses
 */
add_filter('wc_order_statuses', function ($order_statuses) {
    $new = [NAFIS_ORDER_STATUS => __('Ready for Barcode', 'nafis-express-shipping')];

    // Insert after "processing"
    $pos = array_search('wc-processing', array_keys($order_statuses), true);
    if ($pos !== false) {
        return array_slice($order_statuses, 0, $pos + 1, true)
            + $new
            + array_slice($order_statuses, $pos + 1, null, true);
    }

    return $order_statuses + $new;
});

/**
 * Add to bulk actions dropdown (CPT & HPOS compatible)
 */
add_filter('bulk_actions-edit-shop_order', 'nafis_register_bulk_action', 9);
add_filter('bulk_actions-woocommerce_page_wc-orders', 'nafis_register_bulk_action', 9);
function nafis_register_bulk_action($bulk_actions) {
    $key = 'mark_' . str_replace('wc-', '', NAFIS_ORDER_STATUS);
    $bulk_actions[$key] = __('Change status to Ready for Barcode', 'nafis-express-shipping');
    return [$key => $bulk_actions[$key]] + $bulk_actions;
}

/**
 * Make sure custom status is counted and shown in reports
 */
add_filter('woocommerce_reports_order_statuses', function ($statuses) {
    $statuses[] = str_replace('wc-', '', NAFIS_ORDER_STATUS);
    return $statuses;
});

/**
 * Make sure custom status shows in WooCommerce analytics (if active)
 */
add_filter('woocommerce_analytics_report_orders_order_statuses', function ($statuses) {
    $statuses[] = str_replace('wc-', '', NAFIS_ORDER_STATUS);
    return $statuses;
});

/**
 * Add status to order status filter dropdown (above orders list)
 */
add_filter('woocommerce_order_list_status_filters', function ($filters) {
    $filters[str_replace('wc-', '', NAFIS_ORDER_STATUS)] = __('Ready for Barcode', 'nafis-express-shipping');
    return $filters;
});

/**
 * Allow status for resending emails, invoice, etc.
 */
add_filter('woocommerce_resend_order_emails_available_statuses', function ($statuses) {
    $statuses[] = str_replace('wc-', '', NAFIS_ORDER_STATUS);
    return $statuses;
});

/**
 * Allow to edit orders in this custom status
 */
add_filter('woocommerce_order_is_editable', function ($is_editable, $order) {
    if ($order->get_status() === str_replace('wc-', '', NAFIS_ORDER_STATUS)) {
        return true;
    }
    return $is_editable;
}, 10, 2);
