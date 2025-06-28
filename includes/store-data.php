<?php
if (! defined('ABSPATH')) exit;


function nafis_refresh_persistent_filter_data($token)
{
    if (!$token) return;

    nafis_store_statuses_in_db($token);
    nafis_store_provinces_and_cities_in_db($token);
    nafis_generate_province_city_js_file();
    nafis_store_branches_in_db($token);
    nafis_store_boxes_in_db($token);
}

function nafis_refresh_persistent_filter_data_delete()
{
    delete_option('nafis_express_data');
    delete_option("nafis_express_cached_statuses");
    delete_option("nafis_express_cached_provinces");
    delete_option("nafis_express_cached_cities");
    delete_option("nafis_express_cached_branches");
    delete_option("nafis_express_cached_boxes");
}

function nafis_store_boxes_in_db($token)
{
    $res = nafis_api_request('GET', 'panel/warehouse-box', $token);

    if (!$res['success'] || empty($res['data']['data'])) return;

    update_option('nafis_express_cached_boxes', $res['data']['data']);
}

function nafis_store_statuses_in_db($token)
{
    $res = nafis_api_request('GET', '/panel/outcomming/base-statuses', $token);

    if (!$res['success'] || empty($res['data']['data'])) return;

    update_option('nafis_express_cached_statuses', $res['data']['data']);
}

function nafis_store_provinces_and_cities_in_db($token)
{
    $res = nafis_api_request('GET', '/customer/common/provinces', $token);

    if (!$res['success'] || empty($res['data']['data'])) return;

    $provinces = [];
    $cities = [];

    foreach ($res['data']['data'] as $province) {
        $provinces[] = [
            'id'   => $province['provinceID'],
            'name' => $province['name'],
        ];

        foreach ($province['cities'] as $city) {
            $cities[] = [
                'id'         => $city['cityID'],
                'name'       => $city['name'],
                'provinceID' => $province['provinceID'],
                'supported'  => $city['isSupported'],
            ];
        }
    }

    update_option('nafis_express_cached_provinces', $provinces);
    update_option('nafis_express_cached_cities', $cities);
}

function nafis_store_branches_in_db($token)
{
    $res = nafis_api_request('GET', '/customer/Branch/branches', $token);

    if (!$res['success'] || empty($res['data'])) return;

    $filtered = array_map(function ($branch) {
        return [
            'id'    => $branch['id'] ?? null,
            'title' => $branch['title'] ?? '',
        ];
    }, $res['data']);

    update_option('nafis_express_cached_branches', $filtered);
}
add_action('admin_post_nafis_download_city_js', 'nafis_generate_province_city_js_file');
add_action('admin_post_nopriv_nafis_download_city_js', 'nafis_generate_province_city_js_file');
function nafis_generate_province_city_js_file()
{
    ob_clean();

    $cities = get_option('nafis_express_cached_cities', []);
    $js_content = "window.nafisCityList=" . json_encode(
        $cities,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
    ) . ";";

    header('Content-Type: application/javascript; charset=utf-8');
    header('Content-Disposition: attachment; filename="province-city-data.js"');
    header('Content-Length: ' . strlen($js_content));

    echo $js_content;
    exit;
}
