<?php
if (! defined('ABSPATH')) exit;


function nafis_refresh_persistent_filter_data($token)
{
    if (!$token) return;

    nafis_store_statuses_in_db($token);
    nafis_store_provinces_and_cities_in_db($token);
    nafis_store_boxes_in_db($token);
}

function nafis_refresh_persistent_filter_data_delete()
{
    NafisOptionAuthentication::delete();
    NafisOptionCachedBoxes::delete();
    NafisOptionCachedStatuses::delete();
    NafisOptionCachedProvinces::delete();
    NafisOptionCachedCities::delete();
}

function nafis_store_boxes_in_db($token)
{
    $res = nafis_api_request('GET', 'panel/warehouse-box', $token);

    if (!$res['success'] || empty($res['data']['data'])) return;


    NafisOptionCachedBoxes::set($res['data']['data']);
}

function nafis_store_statuses_in_db($token)
{
    $res = nafis_api_request('GET', '/panel/outcomming/base-statuses', $token);

    if (!$res['success'] || empty($res['data']['data'])) return;

    NafisOptionCachedStatuses::set($res['data']['data']);
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

    NafisOptionCachedProvinces::set($provinces);
    NafisOptionCachedCities::set($cities);
}