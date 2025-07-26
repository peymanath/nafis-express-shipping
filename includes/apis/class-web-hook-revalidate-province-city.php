<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the /revalidate-province-city REST endpoint
 */
add_action('rest_api_init', function () {
	NafisApiLoader::register_route(
		'/revalidate-province-city',
		[NafisRevalidateProvinceCityEndpoint::class, 'handle'],
		['POST']
	);
});

/**
 * Class NafisRevalidateProvinceCityEndpoint
 *
 * Refreshes province and city cache from remote API using current user token.
 */
class NafisRevalidateProvinceCityEndpoint
{
	/**
	 * Handles GET /revalidate-province-city
	 *
	 * @return WP_REST_Response
	 */
	public static function handle()
	{
		$user_id = get_current_user_id();
		$token = nafis_get_valid_token($user_id);

		if (empty($token)) {
			// Note: status intentionally set to 200 by business decision
			return NafisApiResponse::fail(
				'unauthorized',
				__('Unauthorized', 'nafis-express-shipping'),
				200,
				['detail' => __('No valid token found for the current user.', 'nafis-express-shipping')]
			);
		}

		try {
			nafis_store_provinces_and_cities_in_db($token);
		} catch (Exception $e) {
			// Note: status intentionally set to 200 by business decision
			return NafisApiResponse::fail(
				'refresh_failed',
				__('Failed to refresh data', 'nafis-express-shipping'),
				200,
				['detail' => $e->getMessage()]
			);
		}

		return NafisApiResponse::success(
			null,
			__('Province and city cache has been revalidated successfully.', 'nafis-express-shipping')
		);
	}
}
