<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('rest_api_init', function () {
    nafis_register_api_route(
        '/revalidate-province-city',
        function () {
            // Checking for a valid token for a logged in user
            $token = nafis_get_valid_token(get_current_user_id());

            if (empty($token)) {
                return NafisApiResponse::problem(
                    'unauthorized',
                    __('Unauthorized', 'nafis-express-shipping'),
                    401,
                    __('No valid token found for the current user.', 'nafis-express-shipping')
                );
            }


            try {
                // Re-storing provinces and cities with a valid token
                nafis_store_provinces_and_cities_in_db($token);
            } catch (Exception $e) {
                return NafisApiResponse::problem(
                    'refresh_failed',
                    __('Failed to refresh data', 'nafis-express-shipping'),
                    500,
                    $e->getMessage()
                );
            }

            return NafisApiResponse::success([
                'updated_at'      => current_time('mysql'),
                'company_id'      => get_option('nafis_express_data')['companyID'] ?? null,
            ], __('Province and city cache has been revalidated successfully.', 'nafis-express-shipping'));
        },
        ['GET'] // Access for Get Method Only
    );
});
