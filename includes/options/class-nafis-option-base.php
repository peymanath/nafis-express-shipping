<?php if (!defined('ABSPATH')) exit;

/**
 * Abstract base class for managing a single WordPress option key.
 *
 * Extend this class to define custom option wrappers, each bound to a specific `wp_option` key.
 *
 * Example usage:
 * ```php
 * class NafisOptionApiKey extends NafisOptionBase {
 *     protected static string $option_key = 'nafis_express_shipping_api_key';
 * }
 * NafisOptionApiKey::set('abc123');
 * $value = NafisOptionApiKey::get();
 * NafisOptionApiKey::delete();
 * ```
 *
 * @package NafisExpressShipping\Options
 */
abstract class NafisOptionBase
{
    /**
     * WordPress option key used to store/retrieve the value.
     *
     * @var string
     */
    protected static string $option_key = '';
    protected static string $option_key_base = 'nafis_express_shipping_';

    /**
     * Get the current value stored under the option key.
     *
     * @return mixed|null The stored value or null if not set.
     */
    public static function get($default_value = \null)
    {
        return get_option(static::$option_key_base . static::$option_key, $default_value);
    }

    /**
     * Update the option with a new value.
     *
     * @param mixed $value Value to store (automatically sanitized as text).
     * @return bool True if value updated successfully, false otherwise.
     */
    public static function set($value)
    {
        if (is_array($value)) {
            $value = self::recursive_sanitize($value);
        } else {
            $value = sanitize_text_field($value);
        }
    
        return update_option(static::$option_key_base . static::$option_key, $value);
    }
    

    /**
     * Delete the option from the database.
     *
     * @return bool True if option deleted, false if it did not exist.
     */
    public static function delete()
    {
        return delete_option(static::$option_key_base . static::$option_key);
    }

    private static function recursive_sanitize($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'recursive_sanitize'], $value);
        }

        return is_scalar($value) ? sanitize_text_field($value) : '';
    }
}

// Include individual option classes
require_once NAFIS_EXPRESS_SHIPPING_INC_OPTIONS . 'class-nafis-option-api-key.php';
require_once NAFIS_EXPRESS_SHIPPING_INC_OPTIONS . 'class-nafis-option-authentication.php';
require_once NAFIS_EXPRESS_SHIPPING_INC_OPTIONS . 'class-nafis-option-setting.php';
require_once NAFIS_EXPRESS_SHIPPING_INC_OPTIONS . 'class-nafis-option-store-data.php';
