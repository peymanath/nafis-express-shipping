<?php

if (!defined('ABSPATH')) exit;

/**
 * Load admin assets for Nafis Express Shipping plugin
 */
add_action('admin_enqueue_scripts', function () {
    // Load CSS
    wp_enqueue_style(
        'nafis-express-admin-style',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'css/nafis-express-admin.css',
        [],
        '1.0.0'
    );

    // Register and enqueue JS
    wp_register_script(
        'nafis-express-admin-script',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'js/nafis-express-admin.js',
        [],
        '1.0.0',
        true
    );

    // Inject JS variables before the script
    wp_add_inline_script('nafis-express-admin-script', 'const nafisProvinceCityData = ' . json_encode([
        'cities'           => nafis_get_cached_cities(),
        'selectedCity'     => $_GET['city'] ?? '',
        'selectedProvince' => $_GET['province'] ?? '',
        'labels'           => [
            'allCities' => __('همه شهرها', 'nafis-express-shipping'),
        ],
    ]) . ';', 'before');

    // Localize more data if needed
    wp_localize_script('nafis-express-admin-script', 'nafisExpressData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('nafis_nonce'),
        'i18n'    => [
            'loading' => esc_html__('در حال بارگذاری...', 'nafis-express-shipping'),
            'error'   => esc_html__('خطایی رخ داد. لطفاً دوباره تلاش کنید.', 'nafis-express-shipping'),
        ],
    ]);

    wp_enqueue_script('nafis-express-admin-script');
});
