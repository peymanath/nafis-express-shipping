<?php if (!defined('ABSPATH')) exit;

/**
 * Abstract base class for managing post meta fields.
 *
 * Extend this class to define custom post meta handlers, each bound to a specific meta_key.
 *
 * @package NafisExpressShipping\PostMeta
 */
abstract class NafisPostMetaBase
{
    protected static string $meta_key = '';
    protected static string $meta_key_prefix = 'nafis_express_shipping_';

    /**
     * Get the meta value for a specific post ID.
     *
     * @param int $post_id
     * @param mixed $default
     * @return mixed
     */
    public static function get(int $post_id, $default = null)
    {
        $value = get_post_meta($post_id, static::$meta_key_prefix . static::$meta_key, true);
        return $value !== '' ? $value : $default;
    }

    /**
     * Set the meta value for a specific post ID.
     *
     * @param int $post_id
     * @param mixed $value
     * @return bool
     */
    public static function set(int $post_id, $value): bool
    {
        if (is_array($value)) {
            $value = self::recursive_sanitize($value);
        } else {
            $value = sanitize_text_field($value);
        }

        return update_post_meta($post_id, static::$meta_key_prefix . static::$meta_key, $value);
    }

    /**
     * Delete the meta value from the post.
     *
     * @param int $post_id
     * @return bool
     */
    public static function delete(int $post_id): bool
    {
        return delete_post_meta($post_id, static::$meta_key_prefix . static::$meta_key);
    }

    /**
     * Recursive sanitize helper
     */
    private static function recursive_sanitize($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'recursive_sanitize'], $value);
        }

        return is_scalar($value) ? sanitize_text_field($value) : '';
    }
}

require_once NAFIS_EXPRESS_SHIPPING_INC_POST_META . 'class-nafis-post-barcodes.php';