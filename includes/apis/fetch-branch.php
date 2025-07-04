<?php if ( ! defined( 'ABSPATH' ) ) exit;


function nafis_fetch_branches_from_api()
{
    $branches = get_option('nafis_express_cached_branches', []);
    return is_array($branches) ? $branches : [];
}
