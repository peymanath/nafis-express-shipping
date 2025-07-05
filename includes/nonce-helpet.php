<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * phpcs:ignoreFile WordPress.Security.ValidatedSanitizedInput
 */

class Nafis_Nonce
{
    /**
     * Validate nonce for POST request
     */
    public static function validate_post($nonce_key, $action)
    {
        if (! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        if (! isset($_POST[$nonce_key])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[$nonce_key]));
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Validate nonce for GET request
     */
    public static function validate_get($nonce_key, $action)
    {
        if (! isset($_GET[$nonce_key])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_GET[$nonce_key]));
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Validate nonce in AJAX (POST or GET)
     */
    public static function validate_ajax($nonce_key, $action)
    {
        $nonce_raw = $_POST[$nonce_key] ?? $_GET[$nonce_key] ?? '';
        $nonce = sanitize_text_field(wp_unslash($nonce_raw));
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Echo nonce field in forms
     */
    public static function add_nonce_field($action, $nonce_key = '_wpnonce')
    {
        wp_nonce_field($action, $nonce_key, true, true);
    }

    /**
     * Generate nonce
     */
    public static function generate($action)
    {
        return wp_create_nonce($action);
    }

    /**
     * If validation fails, die
     */
    public static function check_or_die($result, $message = 'Invalid nonce')
    {
        if (! $result) {
            wp_die(esc_html($message));
        }
    }

    /**
     * Validate POST nonce with permission check
     */
    public static function check_post_security($action, $nonce_key, $capability = '', $submit_field = '')
    {
        if (! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if ($submit_field && ! isset($_POST[$submit_field])) {
            return;
        }

        if (
            ! isset($_POST[$nonce_key]) ||
            ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), $action)
        ) {
            wp_die(esc_html__('Invalid nonce.', 'nafis-express-shipping'));
        }

        if (! empty($capability) && ! current_user_can($capability)) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'nafis-express-shipping'));
        }
    }
}
