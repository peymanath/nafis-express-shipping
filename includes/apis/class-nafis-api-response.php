<?php

/**
 * Class NafisApiResponse
 *
 * Unified API response structure for REST endpoints to ensure full compatibility
 * with the default WordPress REST API (wp-json) error/response format.
 *
 * @author Peyman Naderi | Developer
 */
class NafisApiResponse
{
    /**
     * Success response
     *
     * Returns a standardized success response compatible with wp-json structure.
     * Though WordPress does not enforce a success structure, this format
     * adds consistency across custom endpoints.
     *
     * Example:
     * {
     *   "code": "success",
     *   "message": "Operation completed successfully.",
     *   "data": { ... }
     * }
     *
     * @param mixed  $data    Any data to return to the client
     * @param string $message Custom success message
     * @return WP_REST_Response
     */
    public static function success($data = [], $message = 'Operation completed successfully.')
    {
        return new WP_REST_Response([
            'code'    => 'success',
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    /**
     * Failure response (standard WordPress format)
     *
     * Returns a properly formatted error response compatible with WordPress core API.
     * This structure is used by default WordPress errors like rest_no_route, rest_forbidden, etc.
     *
     * Example:
     * {
     *   "code": "missing_param",
     *   "message": "The order ID is required.",
     *   "data": {
     *     "status": 400,
     *     "field": "order_id"
     *   }
     * }
     *
     * @param string $code       A short string uniquely identifying the error condition
     * @param string $message    A human-readable message providing more details
     * @param int    $status     HTTP status code (default: 400)
     * @param array  $extraData  Additional context data (optional)
     * @return WP_REST_Response
     */
    public static function fail($code = 'nafis_error', $message = 'Operation failed.', $status = 400, $extraData = [])
    {
        return new WP_REST_Response([
            'code'    => $code,
            'message' => $message,
            'data'    => array_merge(['status' => $status], $extraData),
        ], $status);
    }

    /**
     * Problem-style error response (restructured to match wp-json format)
     *
     * Previously used Problem Details (RFC 7807), but now adapted to wp-json error shape.
     * Use this for more semantic, structured error reporting.
     *
     * Example:
     * {
     *   "code": "invalid_date_range",
     *   "message": "The date range is invalid.",
     *   "data": {
     *     "status": 422,
     *     "field": "dateTo"
     *   }
     * }
     *
     * @param string $code     Unique error identifier
     * @param string $message  Human-readable error title/message
     * @param int    $status   HTTP status code
     * @param string $detail   Optional detailed message (will be added to data)
     * @param string $instance Optional request instance/ID
     * @return WP_REST_Response
     */
    public static function problem($code, $message, $status = 400, $detail = '', $instance = '')
    {
        $data = ['status' => $status];
        if ($detail) $data['detail'] = $detail;
        if ($instance) $data['instance'] = $instance;

        return new WP_REST_Response([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Convert a WP_Error object into a WordPress-style error response
     *
     * Ensures that any WP_Error returned from WordPress core functions or validation
     * is transformed into a proper REST response, compatible with wp-json clients.
     *
     * @param WP_Error $error  Instance of WP_Error
     * @param int      $status Optional custom HTTP status code (default: 400)
     * @return WP_REST_Response
     */
    public static function from_wp_error(WP_Error $error, $status = 400)
    {
        return self::fail(
            $error->get_error_code(),
            $error->get_error_message(),
            $status,
            is_array($error->get_error_data()) ? $error->get_error_data() : []
        );
    }
}
