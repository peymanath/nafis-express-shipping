<?php
if (!defined('ABSPATH')) exit;

// --- Handle POST & Permission ---
function nafis_handle_admin_post() {
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['nafis_nonce'])
    ) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nafis_nonce'])), 'nafis_nonce')) {
            wp_die('Invalid nonce');
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to access this section.', 'nafis-express-shipping'));
        }
    }
}

add_action('admin_init', 'nafis_handle_admin_post');

// --- Main Admin Page Renderer ---
function nafis_express_shipping_render_admin_page()
{
    global $nafis_global_notice_shown;
    $nafis_global_notice_shown = false;

    $token = nafis_get_valid_token();
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : ($token ? 'barcode' : 'setting');

    nafis_render_rest_api_warning();
    nafis_render_admin_tabs($active_tab, $token);
    nafis_render_admin_tab_content($active_tab, $token);
}

function nafis_render_rest_api_warning() {
    global $nafis_global_notice_shown;

    $response = wp_remote_get(rest_url(NAFIS_EXPRESS_SHIPPING_API_NAMESPACE . '/ping'), [
        'timeout' => 5,
        'headers' => ['Accept' => 'application/json']
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    esc_html__('❗ REST API access is blocked. Please ensure that the %1$s endpoint (%2$s) is publicly accessible. This is required for Nafis Express to update your data correctly. For more information, please visit %3$s.', 'nafis-express-shipping'),
                    '<code>' . NAFIS_EXPRESS_SHIPPING_API_NAMESPACE . '</code>',
                    esc_url(rest_url(NAFIS_EXPRESS_SHIPPING_API_NAMESPACE . '/')),
                    '<a href="https://nafisexpress.com" target="_blank">' . __('Nafis Express', 'nafis-express-shipping') . '</a>'
                );
                ?>
            </p>
        </div>
        <?php
        $nafis_global_notice_shown = true;
    }
}

function nafis_render_admin_tabs($active_tab, $token) {
    ?>
    <div class="nafis-express-shipping-layout-admin">
        <h1 class="nafis-express-shipping-title-plugin-page">
            <?php esc_html_e('پنل مشتریان نفیس اکسپرس', 'nafis-express-shipping'); ?>
        </h1>

        <h2 class="tab-wrapper">
            <?php foreach ([
                'barcode' => __('Barcodes', 'nafis-express-shipping'),
                'setting' => __('Setting', 'nafis-express-shipping'),
                'quick-guide' => __('راهنمای سریع', 'nafis-express-shipping')
            ] as $key => $label): ?>
                <a href="?page=nafis-express-shipping&tab=<?php echo esc_attr($key); ?>"
                   class="tab <?php echo $active_tab === $key ? 'active-tab' : ''; ?> <?php echo $token ? '' : 'disabled'; ?>">
                    <?php echo esc_html($label); ?>
                </a>
            <?php endforeach; ?>
        </h2>
    <?php
}

function nafis_render_admin_tab_content($active_tab, $token) {
    global $nafis_global_notice_shown;
    ?>
    <div id="tab_container">
        <?php
        switch ($active_tab) {
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
                ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e('Tab is invalid.', 'nafis-express-shipping'); ?></p>
                </div>
                <?php
        }

        if (!$token && !$nafis_global_notice_shown && $active_tab === 'login') {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php esc_html_e('Your session has expired. Please log in again.', 'nafis-express-shipping'); ?></p>
            </div>
            <?php
        }
        ?>
    </div>
    </div>
    <?php
}