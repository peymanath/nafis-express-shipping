<?php
if (!defined('ABSPATH')) exit;

// Get all WooCommerce order statuses
$statuses = wc_get_order_statuses();

// Get saved settings
$saved_settings   = get_option('nafis_express_shipping_settings', []);
$barcode_status   = isset($saved_settings['barcode_status']) ? $saved_settings['barcode_status'] : '';

// Show notices (error or success)
settings_errors('nafis_express_shipping_settings');
?>

<form method="post" action="options.php">
    <?php
    settings_fields('nafis_express_shipping_settings_group');

    // Check if any section is registered
    $sections = $GLOBALS['wp_settings_sections']['nafis_express_shipping_settings_group'] ?? [];

    if (!empty($sections)) {
        do_settings_sections('nafis_express_shipping_settings_group');
        submit_button(__('Save Settings', 'nafis-express-shipping'));
    } else {
        echo '<div class="notice notice-warning inline"><p>' .
            esc_html__('No settings section has been defined.', 'nafis-express-shipping') .
            '</p></div>';
    }
    ?>
</form>