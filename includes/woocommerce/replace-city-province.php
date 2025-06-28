<?php

// جایگزینی استان‌ها با داده کش شده
add_filter('woocommerce_states', function ($states) {
    $provinces = nafis_get_cached_provinces();

    $iran_states = [];

    foreach ($provinces as $province) {
        $iran_states["nafis_" . $province['id']] = $province['name'];
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
    if (
        !isset($_POST['nonce']) ||
        !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nafis_nonce')
    ) {
        wp_send_json_error(['message' => 'درخواست نامعتبر است.']);
        return;
    }

    $province_key = sanitize_text_field($_POST['province_id'] ?? '');

    // حذف prefix 'nafis_' برای رسیدن به شناسه واقعی که تو دیتای کش شده هست
    $clean_id = str_replace('nafis_', '', $province_key);

    $cities = get_option('nafis_express_cached_cities', []);

    $filtered = array_filter($cities, function ($city) use ($clean_id) {
        return $city['provinceId'] == $clean_id;
    });

    wp_send_json_success(array_values($filtered));
}


/**
 * Checkout Field Replace
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
    // فیلد استان‌ها
    $provinces = nafis_get_cached_provinces();
    $province_options = ['' => __('— انتخاب استان —', 'nafis-express-shipping')];

    foreach ($provinces as $province) {
        $province_options['nafis_' . $province['id']] = $province['name'];
    }

    // Billing - استان
    $fields['billing']['billing_state']['type'] = 'select';
    $fields['billing']['billing_state']['options'] = $province_options;
    $fields['billing']['billing_state']['priority'] = 50;
    $fields['billing']['billing_state']['label'] = __('استان', 'nafis-express-shipping');

    // Billing - شهر
    $fields['billing']['billing_city']['type'] = 'select';
    $fields['billing']['billing_city']['options'] = ['' => __('— ابتدا استان را انتخاب کنید —', 'nafis-express-shipping')];
    $fields['billing']['billing_city']['priority'] = 60;
    $fields['billing']['billing_city']['label'] = __('شهر', 'nafis-express-shipping');

    // Shipping - استان
    $fields['shipping']['shipping_state']['type'] = 'select';
    $fields['shipping']['shipping_state']['options'] = $province_options;

    // Shipping - شهر
    $fields['shipping']['shipping_city']['type'] = 'select';
    $fields['shipping']['shipping_city']['options'] = ['' => __('— ابتدا استان را انتخاب کنید —', 'nafis-express-shipping')];

    return $fields;
});

add_action('woocommerce_after_checkout_form', function () {
    $provinces = nafis_get_cached_provinces();
    $cities    = get_option('nafis_express_cached_cities', []);

    echo '<script>';
    echo 'window.nafisProvinces = ' . wp_json_encode($provinces) . ';';
    echo 'window.nafisCities = ' . wp_json_encode($cities) . ';';
    echo '</script>';
});
