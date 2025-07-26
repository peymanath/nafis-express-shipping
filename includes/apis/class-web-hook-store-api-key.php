<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the /store-api-key REST endpoint
 */
add_action('rest_api_init', function () {
	NafisApiLoader::register_route(
		'/store-api-key',
		[NafisStoreApiKeyEndpoint::class, 'handle'],
		['POST']
	);
});

/**
 * Class NafisStoreApiKeyEndpoint
 *
 * Stores the apiKey passed via JSON body into wp_options.
 */
class NafisStoreApiKeyEndpoint
{
	/**
	 * Handles POST /store-api-key
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public static function handle(WP_REST_Request $request)
	{
		$api_key = $request->get_param('ApiKey');

		if (empty($api_key)) {
			return NafisApiResponse::fail(
				'missing_api_key',
				__('The "apiKey" parameter is required in the request body.', 'nafis-express-shipping'),
				400
			);
		}

		NafisOptionApiKey::set($api_key);

		return NafisApiResponse::success(
			null,
			__('API key stored successfully.', 'nafis-express-shipping')
		);
	}
}
