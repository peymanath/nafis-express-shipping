<?php

// Replace WooCommerce states with cached provinces
add_filter('woocommerce_states', function ($states) {
    $provinces = NafisOptionCachedProvinces::get([]);
    $iran_states = [];

    foreach ($provinces as $province) {
        $iran_states["nafis_" . $province['id']] = $province['name'];
    }

    $states['IR'] = $iran_states;
    return $states;
});

// Customize checkout fields
add_filter('woocommerce_checkout_fields', function ($fields) {
    $provinces = NafisOptionCachedProvinces::get([]);
    $province_options = ['' => __('— انتخاب استان —', 'nafis-express-shipping')];

    foreach ($provinces as $province) {
        $province_options['nafis_' . $province['id']] = $province['name'];
    }

    // Determine selected province
    $selected_province = strtolower(
        $_POST['billing_state'] ?? get_user_meta(get_current_user_id(), 'billing_state', true)
    );

    // Province field for billing
    $fields['billing']['billing_state'] = [
        'type'     => 'select',
        'options'  => $province_options,
        'priority' => 50,
        'label'    => __('استان', 'nafis-express-shipping'),
        'value'    => $selected_province,
    ];

    // Build city options based on selected province
    $cities = NafisOptionCachedCities::get([]);
    $city_options = ['' => __('— ابتدا استان را انتخاب کنید —', 'nafis-express-shipping')];

    if (!empty($selected_province)) {
        $province_id = (int) str_replace('nafis_', '', $selected_province);

        foreach ($cities as $city) {
            if (
                is_array($city) &&
                isset($city['provinceID'], $city['name']) &&
                (int)$city['provinceID'] === $province_id
            ) {
                $city_options[$city['name']] = $city['name'];
            }
        }
    }

    // Determine selected city
    $selected_city = $_POST['billing_city'] ?? get_user_meta(get_current_user_id(), 'billing_city', true);

    // City field for billing
    $fields['billing']['billing_city'] = [
        'type'     => 'select',
        'options'  => $city_options,
        'priority' => 60,
        'label'    => __('شهر', 'nafis-express-shipping'),
        'value'    => $selected_city,
    ];

    // City field for shipping (static empty list)
    $fields['shipping']['shipping_city']['type'] = 'select';
    $fields['shipping']['shipping_city']['options'] = ['' => __('— ابتدا استان را انتخاب کنید —', 'nafis-express-shipping')];

    // Remove address line 2 and require phone number
    unset($fields['billing']['billing_address_2']);
    $fields['billing']['billing_phone']['required'] = true;

    return $fields;
});

// Output province and city data for JS
add_action('woocommerce_after_checkout_form', function () {
    $provinces = NafisOptionCachedProvinces::get([]);
    $cities    = NafisOptionCachedCities::get([]);

    echo '<script>';
    echo 'window.nafisProvinces = ' . wp_json_encode($provinces) . ';';
    echo 'window.nafisCities = ' . wp_json_encode($cities) . ';';
    echo '</script>';
});

// Output province and city data for edit address page
add_action('woocommerce_edit_address_form', function () {
    $provinces = NafisOptionCachedProvinces::get([]);
    $cities    = NafisOptionCachedCities::get([]);

    echo '<script>';
    echo 'window.nafisProvinceOptions = ' . wp_json_encode(wp_list_pluck($provinces, 'name', 'id')) . ';';
    echo 'window.nafisCityList = ' . wp_json_encode($cities) . ';';
    echo '</script>';
});

// Default address fields for edit address form
add_filter('woocommerce_default_address_fields', function ($fields) {
    $provinces = NafisOptionCachedProvinces::get([]);
    $cities    = NafisOptionCachedCities::get([]);

    $province_options = ['' => __('— انتخاب استان —', 'nafis-express-shipping')];
    foreach ($provinces as $province) {
        $province_options['nafis_' . $province['id']] = $province['name'];
    }

    $user_id = get_current_user_id();
    $selected_province = strtolower(get_user_meta($user_id, 'billing_state', true));
    $selected_city     = get_user_meta($user_id, 'billing_city', true);
    $selected_city_normalized = normalize_fa($selected_city);

    if ($selected_province && !str_starts_with($selected_province, 'nafis_')) {
        foreach ($provinces as $p) {
            if ($p['id'] == $selected_province || $p['name'] == $selected_province) {
                $selected_province = 'nafis_' . $p['id'];
                break;
            }
        }
    }

    $city_options = ['' => __('— ابتدا استان را انتخاب کنید —', 'nafis-express-shipping')];
    if ($selected_province) {
        $clean_id = str_replace('nafis_', '', $selected_province);
        $related_cities = array_filter($cities, fn($c) => $c['provinceID'] == $clean_id);
        foreach ($related_cities as $city) {
            $city_options[normalize_fa($city['name'])] = normalize_fa($city['name']);
        }
    }

    if (isset($fields['state'])) {
        $fields['state']['type']    = 'select';
        $fields['state']['options'] = $province_options;
        $fields['state']['label']   = __('استان', 'nafis-express-shipping');
        $fields['state']['value']   = $selected_province;
    }

    if (isset($fields['city'])) {
        $fields['city']['type']    = 'select';
        $fields['city']['options'] = $city_options;
        $fields['city']['label']   = __('شهر', 'nafis-express-shipping');
        $fields['city']['value']   = $selected_city_normalized;
    }

    var_dump($city_options,$city_options);

    unset($fields['billing_address_2']);
    return $fields;
});

// Ensure province is lowercase when saving customer meta
add_filter('woocommerce_customer_meta_fields', function ($fields) {
    add_filter('woocommerce_customer_save_address', function ($address, $user_id, $load_address) {
        if (isset($address['state'])) {
            $address['state'] = strtolower($address['state']);
        }
        return $address;
    }, 10, 3);
    return $fields;
});