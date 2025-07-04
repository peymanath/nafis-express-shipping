<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register a custom admin menu for Nafis Express Shipping
 */
add_action('admin_menu', function () {
    add_menu_page(
        __('Nafis Express Shipping', 'nafis-express-shipping'),         // Page title
        __('Nafis Express', 'nafis-express-shipping'),                  // Menu title
        'manage_woocommerce',                                           // Capability
        'nafis-express-shipping',                                       // Menu slug
        'nafis_express_shipping_render_admin_page',                     // Callback function
        NAFIS_EXPRESS_SHIPPING_ASSETS . "img/nafis-express-logo.svg",  // Icon
        56                                                              // Position (under WooCommerce)
    );
});

/**
 * Include Page
 */
require_once NAFIS_EXPRESS_SHIPPING_ADMIN . "admin-page.php";
