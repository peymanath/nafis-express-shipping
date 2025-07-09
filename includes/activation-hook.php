<?php
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook(NAFIS_EXPRESS_SHIPPING_PLUGIN_FILE, function () {
    $defaults = [
        'barcode_status' => 'wc-processing',
    ];

    $current = NafisOptionSetting::get([]);

    if (!is_array($current)) {
        $current = [];
    }

    $updated = false;

    foreach ($defaults as $key => $default_value) {
        if (!isset($current[$key])) {
            $current[$key] = $default_value;
            $updated = true;
        }
    }

    if ($updated) {
        NafisOptionSetting::set($current);
    }
});
