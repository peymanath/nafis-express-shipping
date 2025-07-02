<?php

if (!defined('ABSPATH')) exit;

/**
 * Load Customer assets for Nafis Express Shipping plugin
 */
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_script(
        'nafis-province-city-data',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'js/nafis-province-city-data.js',
        [],
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'nafis-checkout-province-city',
        NAFIS_EXPRESS_SHIPPING_ASSETS . 'js/checkout-city-province.js',
        [],
        '1.0.0',
        true
    );


    wp_localize_script('nafis-checkout-province-city', 'nafisExpressData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('nafis_nonce'),
    ]);
});
