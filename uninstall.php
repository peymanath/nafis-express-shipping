<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Optional: delete plugin options
nafis_refresh_persistent_filter_data_delete();