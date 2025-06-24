<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nafis_is_token_expired($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false; // اجازه نده فقط چون malformed هست کاربر بیرون انداخته بشه
    }

    $payload_json = base64_decode(strtr($parts[1], '-_', '+/'));
    if (!$payload_json) return false; // در صورت خطا، فرض کنیم سالمه

    $payload = json_decode($payload_json, true);
    if (!is_array($payload)) return false;

    if (!isset($payload['exp']) || !is_numeric($payload['exp'])) {
        return false; // اگر زمان انقضا مشخص نیست، فرض کنیم معتبره
    }

    return time() >= $payload['exp']; // true => منقضی شده
}

/**
 * Refresh Token System
 */
function nafis_get_valid_token()
{
    $stored = get_option('nafis_express_data');

    if (!is_array($stored)) {
        return null;
    }

    $token   = isset($stored['token']) ? sanitize_text_field($stored['token']) : null;
    // $refresh = isset($stored['refreshToken']) ? sanitize_text_field($stored['refreshToken']) : null;

    if (empty($token)) {
        return null;
    }

    // اگر توکن هنوز معتبره
    if (!nafis_is_token_expired($token)) {
        return $token;
    }

    // اگر توکن منقضی شده ولی refresh token وجود داره
    // if ($refresh) {
    //     $response = wp_remote_post('https://api.nafisexpress.com/customer/Auth/refresh-token', [
    //         'headers' => [
    //             'Content-Type' => 'application/json',
    //             'Accept' => 'application/json',
    //         ],
    //         'body' => json_encode([
    //             'refreshToken' => $refresh,
    //         ]),
    //     ]);

    //     if (!is_wp_error($response)) {
    //         $body = json_decode(wp_remote_retrieve_body($response), true);

    //         if (!empty($body['status']) && !empty($body['response']['token'])) {
    //             update_user_meta($user_id, 'nafis_express_data', [
    //                 'token' => $body['response']['token'],
    //                 'refreshToken' => $body['response']['refereshToken'] ?? $refresh,
    //             ]);

    //             return $body['response']['token'];
    //         }
    //     }
    // }

    // اگر رفرش هم موفق نبود، توکن را حذف کن
    delete_option('nafis_express_data');
    return null;
}