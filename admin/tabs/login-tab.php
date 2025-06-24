<?php
if (! defined('ABSPATH')) exit;


global $nafis_global_notice_shown;
$nafis_global_notice_shown = false;

$stored = get_option('nafis_express_data');
$token   = is_array($stored) ? sanitize_text_field($stored['token'] ?? '') : null;
$refresh = is_array($stored) ? sanitize_text_field($stored['refreshToken'] ?? '') : null;

$login_success = false;
$error_msg = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nafis_logout_submit'])) {
        check_admin_referer('nafis_logout_action', 'nafis_logout_nonce');
        nafis_handle_logout();
        $token = null;
    }

    if (isset($_POST['nafis_login_submit'])) {
        check_admin_referer('nafis_login_action', 'nafis_login_nonce');
        [$token, $error_msg] = nafis_handle_login();
        $login_success = $token !== null;
    }
}

// If the token is valid, show user panel
if ($token && !nafis_is_token_expired($token)) {
    nafis_show_logged_in_user_ui($token, $login_success);
    return;
}

// Show error message if login failed
if ($error_msg) {
    echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
    $nafis_global_notice_shown = true;
}

// Show token expiration message if no other notice shown
if (!$nafis_global_notice_shown) {
    echo '<div class="notice notice-warning is-dismissible"><p>' .
        esc_html__('Your session has expired. Please log in again.', 'nafis-express-shipping') .
        '</p></div>';
    $nafis_global_notice_shown = true;
}

// Show login form
nafis_render_login_form();

/**
 * Handle logout logic and UI message
 */
function nafis_handle_logout()
{
    nafis_refresh_persistent_filter_data_delete();

    echo '<div class="notice notice-warning is-dismissible"><p>' .
        esc_html__('You have successfully logged out.', 'nafis-express-shipping') .
        '</p></div>';

    global $nafis_global_notice_shown;
    $nafis_global_notice_shown = true;
}

/**
 * Handle login logic and return [token, error]
 */
function nafis_handle_login()
{
    
    $username = sanitize_text_field($_POST['nafis_user']);
    $password = sanitize_text_field($_POST['nafis_pass']);

    $res = nafis_api_request('POST', '/customer/Auth/login', null, [], [
        'userName' => $username,
        'password' => $password,
    ]);

    if (!$res['success'] || empty($res['data']['token'])) {
        return [null, __('Login failed. Please check your credentials.', 'nafis-express-shipping')];
    }

    $parts = explode('.', $res['data']['token']);
    $payload_json = base64_decode(strtr($parts[1], '-_', '+/'));
    $payload = json_decode($payload_json, true);


    update_option('nafis_express_data', [
        'token'            => sanitize_text_field($res['data']['token']),
        'refreshToken'     => sanitize_text_field($res['data']['refereshToken'] ?? ''),
        'companyID'        => sanitize_text_field($payload['companyID']),
        'userID'           => sanitize_text_field($payload['userID']),
        'roles'            => sanitize_text_field($payload['roles']),
    ]);

    return [$res['data']['token'], null];
}


/**
 * Display user panel when logged in
 */
function nafis_show_logged_in_user_ui($token, $just_logged_in = false)
{
    global $nafis_global_notice_shown;

    $data = get_option("nafis_express_data");

    if ($just_logged_in) {
        nafis_refresh_persistent_filter_data($token);
        echo '<div class="notice notice-success is-dismissible"><p>' .
            esc_html__('Login successful.', 'nafis-express-shipping') .
            '</p></div>';
    }

    $nafis_global_notice_shown = true;
?>
    <h2 class="hndle" style="display: flex; justify-content: space-between; align-items: center;">
        <span><?php echo esc_html__('User Information', 'nafis-express-shipping'); ?></span>
        <form method="post" style="margin: 0;">
            <?php wp_nonce_field('nafis_logout_action', 'nafis_logout_nonce'); ?>
            <input type="submit" name="nafis_logout_submit" class="button button-secondary"
                value="<?php echo esc_attr__('Logout', 'nafis-express-shipping'); ?>">
        </form>

    </h2>
    <div class="postbox">
        <div class="inside">
            <?php if (!empty($data['roles'])) : ?>
                <p><strong><?php echo esc_html__('Roles:', 'nafis-express-shipping'); ?></strong>
                    <?php echo esc_html($data['roles']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('disabled'));
        });
    </script>
<?php
}

/**
 * Render login form HTML
 */
function nafis_render_login_form()
{
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
                <td><input name="nafis_pass" id="nafis_pass" type="password" required></td>
            </tr>
        </table>
        <p><input type="submit" name="nafis_login_submit" class="button button-primary"
                value="<?php esc_attr_e('Login', 'nafis-express-shipping'); ?>"></p>
    </form>
<?php
}
