<?php if ( ! defined( 'ABSPATH' ) ) exit;


class Nafis_Nonce
{
    /**
     * Validate nonce for POST request
     */
    public static function validate_post($nonce_key, $action)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $nonce = $_POST[$nonce_key] ?? '';
        return wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), $action);
    }

    /**
     * Validate nonce for GET request (e.g., URL-based actions)
     */
    public static function validate_get($nonce_key, $action)
    {
        $nonce = $_GET[$nonce_key] ?? '';
        return wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), $action);
    }

    /**
     * Validate nonce in AJAX (both authenticated and unauthenticated)
     */
    public static function validate_ajax($nonce_key, $action)
    {
        $nonce = $_POST[$nonce_key] ?? $_GET[$nonce_key] ?? '';
        return wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), $action);
    }

    /**
     * Echo nonce field in forms
     */
    public static function add_nonce_field($action, $nonce_key = '_wpnonce')
    {
        wp_nonce_field($action, $nonce_key, true, true);
    }

    /**
     * Generate nonce (useful in JS / link construction)
     */
    public static function generate($action)
    {
        return wp_create_nonce($action);
    }

    /**
     * If validation fails, kill page
     */
    public static function check_or_die($result, $message = 'Invalid nonce')
    {
        if (! $result) {
            wp_die(esc_html($message));
        }
    }

    /**
     * Combination of anomaly check and access level for POST requests.
     *
     * @param string $action Action value for nonce
     * @param string $nonce_key Nonce key (e.g. 'nafis_barcode_nonce')
     * @param string $capability Capability to execute (e.g. 'manage_woocommerce')
     */
    public static function check_post_security($action, $nonce_key, $capability = '', $submit_field = '')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { return; }
        if ($submit_field && ! isset($_POST[$submit_field])) return;

        if (
            ! isset($_POST[$nonce_key]) ||
            ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), $action)
        ) {
            wp_die(__('Invalid nonce.', 'nafis-express-shipping'));
        }

        if (! empty($capability) && ! current_user_can($capability)) {
            wp_die(__('You are not allowed to perform this action.', 'nafis-express-shipping'));
        }
    }
}
