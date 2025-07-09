<?php
if (!defined('ABSPATH')) exit;

global $nafis_global_notice_shown;
$nafis_global_notice_shown = false;


// nafis_generate_province_city_js_file();

// --- Handle Login/Logout ---
$stored = NafisOptionAuthentication::get();
$token = is_array($stored) ? sanitize_text_field($stored['token'] ?? '') : null;
$refresh = is_array($stored) ? sanitize_text_field($stored['refreshToken'] ?? '') : null;

$login_success = false;
$error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nafis_logout_submit'])) {
        check_admin_referer('nafis_logout_action', 'nafis_logout_nonce');
        nafis_refresh_persistent_filter_data_delete();
        echo '<div class="notice notice-warning is-dismissible"><p>' .
            esc_html__('You have successfully logged out.', 'nafis-express-shipping') . '</p></div>';
        $token = null;
        $nafis_global_notice_shown = true;
    }

    if (isset($_POST['nafis_login_submit'])) {
        check_admin_referer('nafis_login_action', 'nafis_login_nonce');
        [$token, $error_msg] = nafis_handle_login();
        $login_success = $token !== null;
    
        if ($login_success) {
            nafis_refresh_persistent_filter_data($token);
            $redirect_url = admin_url('admin.php?page=nafis-express-shipping&tab=barcode');
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
}

// --- Login Check ---
if ($token && !nafis_is_token_expired($token)) {

    $data = NafisOptionAuthentication::get();

    // --- User Info Box (Redesigned) ---
    echo '<h2>' . esc_html__('User Information', 'nafis-express-shipping') . '</h2>';
    echo '<form method="post">';
    wp_nonce_field('nafis_logout_action', 'nafis_logout_nonce');
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">' . esc_html__('User Information', 'nafis-express-shipping') . '</th>';
    echo '<td>';
    echo '<input type="submit" name="nafis_logout_submit" class="button button-secondary" value="' . esc_attr__('Logout', 'nafis-express-shipping') . '">';
    echo '<p style="margin: 8px 0;">' . esc_html__('You are logged in. You can logout using the button below.', 'nafis-express-shipping') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';
    

    // --- Settings Section ---
    settings_errors('nafis_express_shipping_settings');

    echo '<form method="post" action="options.php">';
    settings_fields('nafis_express_shipping_settings_group');

    $sections = $GLOBALS['wp_settings_sections']['nafis_express_shipping_settings_group'] ?? [];

    if (!empty($sections)) {
        do_settings_sections('nafis_express_shipping_settings_group');
        submit_button(__('Save Settings', 'nafis-express-shipping'));
    } else {
        echo '<div class="notice notice-warning inline"><p>' .
            esc_html__('No settings section has been defined.', 'nafis-express-shipping') .
            '</p></div>';
    }
    echo '</form>';
} else {
    if ($error_msg) {
        echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
        $nafis_global_notice_shown = true;
    }

    if (!$nafis_global_notice_shown) {
        echo '<div class="notice notice-warning is-dismissible"><p>' .
            esc_html__('Your session has expired. Please log in again.', 'nafis-express-shipping') .
            '</p></div>';
        $nafis_global_notice_shown = true;
    }

    // --- Login Form ---
?>
    <form method="post">
        <?php wp_nonce_field('nafis_login_action', 'nafis_login_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="nafis_user"><?php esc_html_e('Username', 'nafis-express-shipping'); ?></label></th>
                <td><input name="nafis_user" id="nafis_user" type="text" required></td>
            </tr>
            <tr>
                <th><label for="nafis_pass"><?php esc_html_e('Password', 'nafis-express-shipping'); ?></label></th>
                <td><input name="nafis_pass" id="nafis_pass" type="text" required></td>
            </tr>
        </table>
        <p><input type="submit" name="nafis_login_submit" class="button button-primary"
                value="<?php esc_attr_e('Login', 'nafis-express-shipping'); ?>"></p>
    </form>
<?php
}


// --- Login Handler Function ---
function nafis_handle_login()
{
    $username = sanitize_text_field($_POST['nafis_user']);
    $password = sanitize_text_field($_POST['nafis_pass']);

    $res = nafis_api_request('POST', '/customer/Auth/login', null, [], [
        'userName' => $username,
        'password' => $password,
    ]);

    var_dump($res);

    if (!$res['success'] || empty($res['data']['token'])) {
        return [null, __('Login failed. Please check your credentials.', 'nafis-express-shipping')];
    }

    $parts = explode('.', $res['data']['token']);
    $payload_json = base64_decode(strtr($parts[1], '-_', '+/'));
    $payload = json_decode($payload_json, true);

    NafisOptionAuthentication::set([
        'token'        => sanitize_text_field($res['data']['token']),
        'refreshToken' => sanitize_text_field($res['data']['refereshToken'] ?? ''),
        'companyID'    => sanitize_text_field($payload['companyID']),
        'userID'       => sanitize_text_field($payload['userID']),
        'roles'        => sanitize_text_field($payload['roles']),
    ]);

    return [$res['data']['token'], null];
}
