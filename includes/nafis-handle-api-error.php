<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle Nafis API errors and extract user-friendly message
 *
 * @param mixed $response WP HTTP response object
 * @return string Error message to display
 */
function nafis_handle_api_error($response)
{
    if (is_wp_error($response)) {
        return sprintf(
            // translators: %s is the error message returned from the server
            esc_html__('خطا در ارتباط با سرور: %s', 'nafis-express-shipping'),
            esc_html($response->get_error_message())
        );
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body_raw = wp_remote_retrieve_body($response);
    $body = json_decode($body_raw, true);

    if (!is_array($body)) {
        return esc_html__('پاسخ نامعتبر از سرور دریافت شد.', 'nafis-express-shipping');
    }

    if (!empty($body['exception']['exceptionMessage'])) {
        return esc_html($body['exception']['exceptionMessage']);
    }

    if (!empty($body['message'])) {
        return esc_html($body['message']);
    }

    if (isset($body['status']) && $body['status'] === false) {
        return esc_html__('درخواست ناموفق بود. لطفاً اطلاعات ورودی را بررسی کنید.', 'nafis-express-shipping');
    }

    if ($http_code >= 400) {
        return sprintf(
            // translators: %d is the HTTP status code from the server response
            esc_html__('خطا با کد HTTP %d', 'nafis-express-shipping'),
            absint($http_code)
        );
    }

    return esc_html__('خطای ناشناخته‌ای رخ داد.', 'nafis-express-shipping');
}
