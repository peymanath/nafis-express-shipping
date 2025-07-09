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
    $stored = NafisOptionAuthentication::get();

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


    // اگر رفرش هم موفق نبود، توکن را حذف کن
    NafisOptionAuthentication::delete();
    return null;
}