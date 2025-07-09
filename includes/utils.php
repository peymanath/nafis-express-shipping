<?php
if (! defined('ABSPATH')) exit;

function nafis_get_cities_by_province($province_id)
{
    $all = NafisOptionCachedCities::get([]);
    return array_values(array_filter($all, function ($city) use ($province_id) {
        return $city['provinceID'] == $province_id;
    }));
}

// Normalize Persian characters
if (!function_exists('normalize_fa')) {
    function normalize_fa($text): string
    {
        return trim(str_replace(['ي', 'ك', '‌'], ['ی', 'ک', ''], $text));
    }
}
