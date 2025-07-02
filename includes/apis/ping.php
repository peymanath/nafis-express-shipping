<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('rest_api_init', function () {
    nafis_register_api_route('/ping', function () {

        return NafisApiResponse::success([
            'status'   => 'ok',
            'datetime' => current_time('mysql'),
            'version'  => '1.0.0',
            'plugin'   => 'Nafis Express Shipping'
        ], __('Ok.', 'nafis-express-shipping'));
    }, ['GET']);
});