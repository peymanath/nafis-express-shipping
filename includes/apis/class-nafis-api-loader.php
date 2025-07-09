<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class NafisApiLoader
 *
 * Registers and loads REST API endpoints and utility logic for Nafis Express Shipping.
 */
class NafisApiLoader
{
    /**
     * Register a REST API route with default permission callback.
     *
     * @param string $route
     * @param callable $callback
     * @param array $methods
     * @param callable|null $permission_callback
     */
    public static function register_route($route, $callback, $methods = ['GET'], $permission_callback = null)
    {
        register_rest_route(NAFIS_EXPRESS_SHIPPING_API_NAMESPACE, $route, [
            'methods'             => $methods,
            'callback'            => $callback,
            'permission_callback' => $permission_callback ?: '__return_true',
        ]);
    }

    /**
     * Check if the REST API is properly enabled and permalinks are configured.
     *
     * @return true|WP_Error
     */
    public static function check_rest_api_status()
    {
        $rest_disabled = get_option('permalink_structure') === '';
        if ($rest_disabled || strpos(get_rest_url(), 'rest_route') !== false) {
            return new WP_Error(
                'rest_api_blocked',
                'REST API appears to be disabled or permalinks are not properly configured.',
                ['status' => 400]
            );
        }

        return true;
    }

    /**
     * Load all custom API endpoints.
     */
    public static function init()
    {

        $files = [
            'class-nafis-api-response.php',
            'class-web-hook-store-api-key.php',
            'class-web-hook-revalidate-province-city.php',
            'class-web-hook-ping.php',
        ];
        
        foreach ($files as $file) {
            $path = NAFIS_EXPRESS_SHIPPING_INC_API . $file;
            
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}

NafisApiLoader::init();