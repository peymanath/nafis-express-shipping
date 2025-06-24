<?php

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nafis_nonce'])) {
    if (
        ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nafis_nonce'])), 'nafis_nonce')
    ) {
        wp_die('Invalid nonce');
    }

    if (!current_user_can('manage_woocommerce')) {
        wp_die(esc_html__('You do not have permission to access this section.', 'nafis-express-shipping'));
    }
}

if (! defined('ABSPATH')) exit;


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
    $token = nafis_get_valid_token();
    $active_tab = isset($_GET['tab'])
        ? sanitize_key($_GET['tab'])
        : ($token ? 'barcode' : 'setting');

?>
    <div class="wrap wpp-settings-wrap">
        <h2 class="nav-tab-wrapper">

            <a href="?page=nafis-express-shipping&tab=barcode"
                class="nav-tab <?php echo $active_tab === 'barcode' ? 'nav-tab-active' : ''; ?> <?php echo $token ? '' : 'disabled'; ?>">
                <?php echo esc_html__('Barcodes', 'nafis-express-shipping'); ?>
            </a>

            <a href="?page=nafis-express-shipping&tab=setting"
                class="nav-tab <?php echo $active_tab === 'setting' ? 'nav-tab-active' : ''; ?> <?php echo $token ? '' : 'disabled'; ?>">
                <?php echo esc_html__('Setting', 'nafis-express-shipping'); ?>
            </a>

            <a href="?page=nafis-express-shipping&tab=quick-guide"
                class="nav-tab <?php echo $active_tab === 'quick-guide' ? 'nav-tab-active' : ''; ?> <?php echo $token ? '' : 'disabled'; ?>">
                <?php echo esc_html__('راهنمای سریع', 'nafis-express-shipping'); ?>
            </a>
        </h2>

        <div id="tab_container">
            <?php
            switch ($active_tab) {
                // case 'branches':
                //     require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/branches-tab.php';
                //     break;
                case 'barcode':
                    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/barcode-tab.php';
                    break;
                case 'setting':
                    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/setting-tab.php';
                    break;
                case 'quick-guide':
                    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/quick-guide-tab.php';
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
