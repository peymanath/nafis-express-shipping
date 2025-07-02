<?php

if (!defined('ABSPATH')) exit;

/**
 * Load admin assets for Nafis Express Shipping plugin
 */
add_action('admin_enqueue_scripts', function ($hook) {

    // Load CSS
    wp_enqueue_style(
        'nafis-express-admin-style',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'css/nafis-express-admin.css',
        [],
        '1.0.0'
    );

    // Load JS
    wp_enqueue_script(
        'nafis-express-admin-script',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'js/nafis-express-admin.js',
        [],
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'nafis-province-city-data',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'js/nafis-province-city-data.js',
        [],
        '1.0.0',
        true
    );

    // Localize script for nonce and translations if needed
    wp_localize_script('nafis-express-admin-script', 'nafisExpressData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('nafis_nonce'),
        'i18n'    => [
            'loading' => esc_html__('در حال بارگذاری...', 'nafis-express-shipping'),
            'error'   => esc_html__('خطایی رخ داد. لطفاً دوباره تلاش کنید.', 'nafis-express-shipping'),
        ],
    ]);
});
