<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the /ping endpoint for health checks.
 */
add_action('rest_api_init', function () {
	NafisApiLoader::register_route('/ping', [NafisPingEndpoint::class, 'handle'], ['GET']);
});

/**
 * Class NafisPingEndpoint
 *
 * Handles the response logic for the /ping endpoint.
 */
class NafisPingEndpoint
{
	/**
	 * Handles the GET /ping request
	 *
	 * @return WP_REST_Response
	 */
	public static function handle()
	{
		return NafisApiResponse::success([
			'status'   => 'ok',
			'datetime' => current_time('mysql'),
			'version'  => '1.0.0',
			'plugin'   => 'Nafis Express Shipping'
		], __('Ok.', 'nafis-express-shipping'));
	}
}
