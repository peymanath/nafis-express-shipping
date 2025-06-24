<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Optional: delete plugin options
delete_option('nafis_express_data');
delete_option('nafis_express_settings');