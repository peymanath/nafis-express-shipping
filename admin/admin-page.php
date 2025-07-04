<?php if ( ! defined( 'ABSPATH' ) ) exit;


global $nafis_global_notice_shown;
$nafis_global_notice_shown = false;

function nafis_express_shipping_render_admin_page()
{
    global $nafis_global_notice_shown;

    /**
     * Check Token Expiration
     */
    $token = nafis_get_valid_token();

    /**
     * Set Active Tab
     */
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'login';

?>
    <div class="wrap wpp-settings-wrap">
        <h2 class="nav-tab-wrapper">
            <a href="?page=nafis-express-shipping&tab=login"
                class="nav-tab <?= esc_attr($active_tab === 'login' ? 'nav-tab-active' : ''); ?>">
                <?= esc_html__('Login', 'nafis-express-shipping'); ?>
            </a>

            <a href="?page=nafis-express-shipping&tab=barcode"
                class="nav-tab <?= esc_attr($active_tab === 'barcode' ? 'nav-tab-active' : ''); ?><?= esc_attr($token ? '' : ' disabled'); ?>">
                <?= esc_html__('Barcodes', 'nafis-express-shipping'); ?>
            </a>
        </h2>

        <div id="tab_container">
            <?php
            switch ($active_tab) {
                case 'login':
                    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/login-tab.php';
                    break;
                case 'barcode':
                    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/barcode-tab.php';
                    break;
                default:
                    echo '<div class="notice notice-error"><p>' . esc_html__('Tab is invalid.', 'nafis-express-shipping') . '</p></div>';
            }

            if (!$token && !$nafis_global_notice_shown && $active_tab === 'login') {
                echo '<div class="notice notice-warning is-dismissible"><p>' .
                    esc_html__('Your session has expired. Please log in again.', 'nafis-express-shipping') .
                    '</p></div>';
            }
            ?>
        </div>
    </div>
<?php
}
