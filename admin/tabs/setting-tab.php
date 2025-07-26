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

    // --- Login Form ---
?>
    <div class="nafis-login-wrapper">

        <form method="post" class="nafis-login-box">
            <?php wp_nonce_field('nafis_login_action', 'nafis_login_nonce'); ?>

            <div class="nafis-express-shipping-layout-field">
                <label for="nafis_user"><?php esc_html_e('Username', 'nafis-express-shipping'); ?></label>
                <input name="nafis_user" id="nafis_user" type="text" required>
                <div
                    style="padding:8px; background:#cde4ff; border:1px solid #bad9ff; border-radius:6px; color:#044085;">
                    نام کاربری شما در پنل کاربری نفیس اکسپرس
                </div>

            </div>

            <div class="nafis-express-shipping-layout-field">
                <label for="nafis_pass"><?php esc_html_e('Password', 'nafis-express-shipping'); ?></label>
                <input name="nafis_pass" id="nafis_pass" type="text" required>
                <div
                    style="padding:8px; background:#cde4ff; border:1px solid #bad9ff; border-radius:6px; color:#044085;">
                    رمزعبور شما در پنل کاربری نفیس اکسپرس
                </div>
            </div>

            <input type="submit" name="nafis_login_submit" value="<?php esc_attr_e('Login', 'nafis-express-shipping'); ?>">
        </form>

        <div
            style="height: 100%; padding:20px; background:#fff3cd; border:1px solid #ffeeba; border-radius:6px; color:#856404; line-height:1.8; flex-grow: 1;">
            <strong><?php echo esc_html__('توجه مهم:', 'nafis-express-shipping'); ?></strong><br>

            <p>
                استفاده از خدمات افزونه «نفیـس اکسپرس» تنها برای کسب‌وکارهایی فعال است که قرارداد رسمی با شرکت نفیس دارند.
                در صورتی که هنوز حساب کاربری ندارید، لطفاً ابتدا مراحل ثبت‌نام را تکمیل نمایید.
            </p>

            <ul style="padding-right: 20px;">
                <li>✅ مراجعه به صفحه <a href="https://nafisexpress.com/register" target="_blank">ثبت‌نام در پنل نفیس</a></li>
                <li>✅ تکمیل فرم قرارداد همکاری و تایید آن توسط واحد پشتیبانی</li>
                <li>✅ دریافت نام کاربری و رمز عبور پنل از طریق ایمیل یا پیامک</li>
            </ul>

            <p>
                پس از دریافت اطلاعات ورود، می‌توانید در همین صفحه لاگین کرده و از امکانات افزونه استفاده نمایید.
            </p>

            <hr style="margin: 20px 0; border-color: #ffeeba;">

            <p>
                در صورت نیاز به راهنمایی بیشتر یا درخواست دسترسی سریع، لطفاً از طریق یکی از روش‌های زیر با ما در تماس باشید:
            </p>

            <ul style="padding-right: 20px;">
                <li>📞 تلفن پشتیبانی: 021-12345678</li>
                <li>📧 ایمیل: <a href="mailto:support@nafisexpress.com">support@nafisexpress.com</a></li>
                <li>💬 واتساپ/تلگرام: <a href="https://t.me/nafis_support" target="_blank">@nafis_support</a></li>
            </ul>

        </div>

    </div>
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
