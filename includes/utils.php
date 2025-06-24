<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nafis_get_cached_boxes() {
    return get_option('nafis_express_cached_boxes', []);
}
function nafis_get_cached_statuses()
{
    $statuses = get_option('nafis_express_cached_statuses', []);
    return is_array($statuses) ? $statuses : [];
}
function nafis_get_cached_provinces()
{
    return get_option('nafis_express_cached_provinces', []);
}
function nafis_get_cities_by_province($province_id)
{
    $all = get_option('nafis_express_cached_cities', []);
    return array_values(array_filter($all, function ($city) use ($province_id) {
        return $city['provinceID'] == $province_id;
    }));
}
function nafis_get_cached_branches()
{
    $branches = get_option('nafis_express_cached_branches', []);
    return is_array($branches) ? $branches : [];
}
