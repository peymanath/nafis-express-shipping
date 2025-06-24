<?php

if (!defined('ABSPATH')) exit; // Prevent direct access to the plugin file

// Register Actions
add_action('admin_init', function () {
    register_setting('nafis_express_shipping_settings_group', 'nafis_express_shipping_settings','nafis_express_shipping_settings_validate');

     // Add section
    add_settings_section(
        'nafis_barcode_settings_section', // section ID
        __('Barcode Settings', 'nafis-express-shipping'), // title
        '__return_null', // callback for section description
        'nafis_express_shipping_settings_group' // page slug (same as in do_settings_sections)
    );

    // Add field inside the section
    add_settings_field(
        'barcode_status',
        __('Order status for barcode issuing', 'nafis-express-shipping'),
        'nafis_render_barcode_status_field', // callback to render field
        'nafis_express_shipping_settings_group',
        'nafis_barcode_settings_section',
        ['label_for' => 'barcode_status'] // optional, helps with accessibility
    );

});

function nafis_express_shipping_settings_validate($input) {
    $output = [];

    // Check if barcode_status is set and not empty
    if (!empty($input['barcode_status'])) {
        $output['barcode_status'] = sanitize_text_field($input['barcode_status']);
        add_settings_error(
            'nafis_express_shipping_settings',
            'settings_updated',
            __('Settings have been saved successfully.', 'nafis-express-shipping'),
            'updated'
        );
    } else {
        add_settings_error(
            'nafis_express_shipping_settings',
            'settings_error',
            __('Please select a valid order status.', 'nafis-express-shipping'),
            'error'
        );
    }

    return $output;
}


/**
 * Render Fileds
 */
function nafis_render_barcode_status_field() {
    $statuses = wc_get_order_statuses();
    $options = get_option('nafis_express_shipping_settings', []);

    // If not set, fallback to 'wc-processing' as default
    $selected = $options['barcode_status'] ?? 'wc-processing';

    // List of order statuses to exclude from dropdown
    $excluded_statuses = [
        'wc-pending',     // Pending payment
        'wc-failed',      // Failed
        'wc-draft',       // Draft (custom or plugin-specific)
        'draft',          // Just in case some status is saved as 'draft'
        'wc-refunded',
        'wc-checkout-draft',
    ];
    ?>
    <select name="nafis_express_shipping_settings[barcode_status]" id="barcode_status">
        <option value=""><?php esc_html_e('-- Select status --', 'nafis-express-shipping'); ?></option>
        <?php foreach ($statuses as $key => $label): ?>
            <?php if (in_array($key, $excluded_statuses, true)) continue; ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($selected, $key); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description">
        <?php esc_html_e('Select the order status where the "Issue Barcode" action should appear.', 'nafis-express-shipping'); ?>
    </p>
    <?php
}
