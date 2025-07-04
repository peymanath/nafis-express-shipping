<?php if ( ! defined( 'ABSPATH' ) ) exit;

// Load helpers
require_once NAFIS_EXPRESS_SHIPPING_INC . 'class-loader.php';
require_once NAFIS_EXPRESS_SHIPPING_INC . 'nafis-handle-api-error.php';
require_once NAFIS_EXPRESS_SHIPPING_INC . 'nafis-handle-token.php';
require_once NAFIS_EXPRESS_SHIPPING_INC . 'store-data.php';
require_once NAFIS_EXPRESS_SHIPPING_INC . 'utils.php';

// Delay admin features until plugins are fully loaded
add_action('plugins_loaded', function () {
    // Only run in admin
    if (is_admin()) {
        // Check if WooCommerce is active
        if (class_exists('WooCommerce')) {
            require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'settings-page.php';
            require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'admin-assets.php';
            require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'admin-tables.php';
            require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'metabox/index.php';
            require_once NAFIS_EXPRESS_SHIPPING_INC_API . 'fetch-boxes.php';
            require_once NAFIS_EXPRESS_SHIPPING_INC_API . 'fetch-branch.php';
            require_once NAFIS_EXPRESS_SHIPPING_INC_API . 'fetch-barcode.php';
            require_once NAFIS_EXPRESS_SHIPPING_INC_API . 'nafis_api_request.php';
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p><strong>' .
                    esc_html__('Nafis Express Shipping:', 'nafis-express-shipping') .
                    '</strong> ' .
                    esc_html__('WooCommerce must be installed and active for this plugin to work.', 'nafis-express-shipping') .
                    '</p></div>';
            });
        }
    }
});
