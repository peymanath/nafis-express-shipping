<?php

/**
 * This file is part of the Nafis Express Shipping plugin.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @license GPL-2.0-or-later
 */

/**
 * Plugin Name:       Nafis Express Shipping
 * Plugin URI:        https://wordpress.org/plugins/nafis-express-shipping
 * Description:       Manage and integrate Nafis Express shipping service with WooCommerce. Includes custom box size, delivery time fields, and API integration.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Tested up to:      6.8
 * Requires at least: 5.2
 * Author:            Peyman Naderi
 * Author URI:        https://github.com/peymanath
 * Company:           Nafis Express | Nafis Group
 * Company URI:       https://nafisexpress.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nafis-express-shipping
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) exit;

// Define plugin path
define('NAFIS_EXPRESS_SHIPPING_PATH', plugin_dir_path(__FILE__));

// Define plugin URL (used for loading assets)
define('NAFIS_EXPRESS_SHIPPING_URL', plugin_dir_url(__FILE__));

// Define path to the includes directory
define('NAFIS_EXPRESS_SHIPPING_INC', NAFIS_EXPRESS_SHIPPING_PATH . 'includes/');

// Define path to the includes directory Woo
define('NAFIS_EXPRESS_SHIPPING_INC_WOO', NAFIS_EXPRESS_SHIPPING_INC . 'woocommerce/');

// Define path to the includes directory Apis
define('NAFIS_EXPRESS_SHIPPING_INC_API', NAFIS_EXPRESS_SHIPPING_INC . 'apis/');

// Define path to the includes directory Actions
define('NAFIS_EXPRESS_SHIPPING_INC_ACTIONS', NAFIS_EXPRESS_SHIPPING_INC . 'actions/');

// Define path to the admin panel directory
define('NAFIS_EXPRESS_SHIPPING_ADMIN', NAFIS_EXPRESS_SHIPPING_PATH . 'admin/');

// Define URL to the plugin's assets directory
define('NAFIS_EXPRESS_SHIPPING_ASSETS', NAFIS_EXPRESS_SHIPPING_URL . 'assets/');

if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '.dev') !== false) {
    define('NAFIS_EXPRESS_SHIPPING_BASE_API', 'https://api.nafisexpress.com');
} else {
    define('NAFIS_EXPRESS_SHIPPING_BASE_API', 'https://api.nafisexpress.com');
}

//Add link to plugins row meta
add_filter('plugin_row_meta', function ($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        // $links[] = '<a href="https://github.com/peymanath/nafis-express-shipping" target="_blank">' . __('Project source on GitHub', 'nafis-express-shipping') . '</a>';
        $links[] = '<a href="https://nafisexpress.com" target="_blank">' . __('Nafis Express', 'nafis-express-shipping') . '</a>';
    }
    return $links;
}, 10, 2);

// Load core plugin files
require_once NAFIS_EXPRESS_SHIPPING_INC . 'class-loader.php';

// Activation Hook
define('NAFIS_EXPRESS_SHIPPING_PLUGIN_FILE', __FILE__);
require_once NAFIS_EXPRESS_SHIPPING_INC . "activation-hook.php";