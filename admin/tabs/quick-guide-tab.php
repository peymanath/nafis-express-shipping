<?php

if (!defined('ABSPATH')) exit; // Prevent direct access to the plugin file

$subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'incomplete-products';

// echo '<h2 class="nav-tab-wrapper subtab">';
// echo '<a href="?page=nafis-express-shipping&tab=quick-guide&subtab=incomplete-products" class="nav-tab ' . ($subtab === 'incomplete-products' ? 'nav-tab-active' : '') . '">محصولات ناقص</a>';
// echo '<a href="?page=nafis-express-shipping&tab=quick-guide&subtab=faq" class="nav-tab ' . ($subtab === 'faq' ? 'nav-tab-active' : '') . '">سوالات متداول</a>';
// echo '</h2>';

switch ($subtab) {
    // case 'faq':
    //     include NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/quick-guide/faq-tab.php';
    //     break;
    case 'incomplete-products':
    default:
        include NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/quick-guide/incomplete-products-tab.php';
        break;
}
