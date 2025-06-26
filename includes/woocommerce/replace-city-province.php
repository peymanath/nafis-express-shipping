<?php

// جایگزینی استان‌ها با داده کش شده
add_filter('woocommerce_states', function ($states) {
    $provinces = nafis_get_cached_provinces();

    $iran_states = [];

    foreach ($provinces as $province) {
        $iran_states[$province['id']] = $province['name'];
    }

    $states['IR'] = $iran_states;
    return $states;
});


/**
 * Ajax
 */
add_action('wp_ajax_nafis_get_cities_by_province', 'nafis_ajax_get_cities');
add_action('wp_ajax_nopriv_nafis_get_cities_by_province', 'nafis_ajax_get_cities');

function nafis_ajax_get_cities()
{
    // بررسی nonce با همون نامی که در wp_localize_script دادی: nafis_nonce
    if (
        !isset($_POST['nonce']) ||
        !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nafis_nonce')
    ) {
        wp_send_json_error(['message' => 'درخواست نامعتبر است.']);
        return;
    }

    $province_id = sanitize_text_field($_POST['province_id'] ?? '');
    $cities = get_option('nafis_express_cached_cities', []);

    $filtered = array_filter($cities, function ($city) use ($province_id) {
        return $city['provinceId'] == $province_id;
    });

    wp_send_json_success(array_values($filtered));
}

/**
 * Checkout Field Replace
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
    // فیلد استان
    $fields['billing']['billing_state']['type'] = 'select';
    $fields['billing']['billing_state']['options'] = [];

    $fields['billing']['billing_state']['priority'] = 50;
    $fields['billing']['billing_state']['label'] = __('استان', 'nafis-express-shipping');

    // فیلد شهر
    $fields['billing']['billing_city']['type'] = 'select';
    $fields['billing']['billing_city']['options'] = [];

    $fields['billing']['billing_city']['priority'] = 60;
    $fields['billing']['billing_city']['label'] = __('شهر', 'nafis-express-shipping');

    // استان shipping
    $fields['shipping']['shipping_state']['type'] = 'select';
    $fields['shipping']['shipping_state']['options'] = [];

    // شهر shipping
    $fields['shipping']['shipping_city']['type'] = 'select';
    $fields['shipping']['shipping_city']['options'] = [];

    return $fields;
});
add_action('woocommerce_after_checkout_form', function () {
    $provinces = nafis_get_cached_provinces();

    echo '<script>';
    echo 'window.nafisProvinces = ' . wp_json_encode($provinces) . ';';
    echo '</script>';
});