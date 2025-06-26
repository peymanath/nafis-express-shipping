<?php

if (!defined('ABSPATH')) exit;

var_dump('salam');
/**
 * Load Customer assets for Nafis Express Shipping plugin
 */
add_action('wp_enqueue_scripts', function () {
        wp_enqueue_script(
            'nafis-checkout-province-city',
            plugin_dir_url(__FILE__) . 'assets/js/checkout-city-province.js',
            [],
            '1.0.0',
            true
        );

        wp_localize_script('nafis-checkout-province-city', 'nafisExpressData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('nafis_nonce'),
        ]);
});
