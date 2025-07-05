<?php if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:ignoreFile WordPress.Security.NonceVerification.Recommended

add_action('add_meta_boxes', function () {
    $screen_id = get_current_screen()->id;

    if (strpos($screen_id, 'shop-order') !== false || strpos($screen_id, 'wc-orders') !== false) {
        add_meta_box(
            'nafis_order_barcodes',
            esc_html__('Nafis Express Barcodes', 'nafis-express-shipping'),
            'nafis_order_barcodes_meta_box',
            $screen_id,
            'normal',
            'default'
        );
    }
});


function nafis_order_barcodes_meta_box($post)
{
    $order_id =  isset($_GET['test_order_id']) ? sanitize_key($_GET['test_order_id']) : (int) $post->ID;
    $user_id = get_current_user_id();
    $user_barcdes = get_post_meta($order_id, "nafis-express-barcode");
    $token = nafis_get_valid_token($user_id);

    if (!empty($user_barcdes)) {
        error_log($user_barcdes);
    }


    if (!$token) {
        echo '<p style="color:red;">' . esc_html__('Please log in to Nafis Express to view barcodes.', 'nafis-express-shipping') . '</p>';
        return;
    }

    $result = nafis_fetch_exited_orders($token, $order_id);
    $barcodes = isset($result['data']) ? $result['data'] : [];

    if (empty($barcodes)) {
        $order_date = get_post_meta($order_id, '_date_created', true);
        if (!$order_date) {
            $order_obj = wc_get_order($order_id);
            $order_date = $order_obj ? $order_obj->get_date_created() : null;
        }

        if ($order_date instanceof \WC_DateTime) {
            $order_date = $order_date->date('Y-m-d');
        } elseif (is_numeric($order_date)) {
            $order_date = current_time('Y-m-d', (int) $order_date);
        }

        $filter_url = add_query_arg([
            'page'       => 'nafis-express-shipping',
            'tab'        => 'barcode',
            'date_from'  => $order_date,
        ], admin_url('admin.php'));

        echo '<p style="margin-bottom: 0.5em; font-weight: bold;">'
            . esc_html__('No barcodes were found for this order.', 'nafis-express-shipping') . '</p>';

        echo '<ul style="margin-top: 0; padding-right: 1.2em; list-style: disc;">';
        echo '<li>' . esc_html__('The barcode might not have been registered yet.', 'nafis-express-shipping') . '</li>';
        echo '<li>' . esc_html__('The order may have been cancelled or returned.', 'nafis-express-shipping') . '</li>';
        echo '<li>' . esc_html__('There might be a mismatch between order date and barcode registration time.', 'nafis-express-shipping') . '</li>';
        echo '</ul>';

        echo '<p style="margin-top: 1em;">'
            . esc_html__('You can review barcodes registered on the same day as this order by visiting this page:', 'nafis-express-shipping')
            . ' <a href="' . esc_url($filter_url) . '" target="_blank" style="text-decoration: underline; color: #0073aa;">'
            . esc_html__('Go to today’s barcode list →', 'nafis-express-shipping') . '</a></p>';
        return;
    }
    foreach ($barcodes as $item) {
        $barcode = esc_html($item['barcode']);
?>
        <div class="nafis-barcode-box" style="border: 1px solid #ccc; border-radius: 6px; padding: 10px; margin: 16px  0px; ">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong><?php echo esc_html($barcode); ?></strong>
                <button type="button" class="button track-barcode-btn" data-barcode="<?php echo esc_attr($barcode); ?>">
                    <?php esc_html_e('View Tracking', 'nafis-express-shipping'); ?>
                </button>
            </div>
            <div class="track-result" style="margin-top: 12px; display: none;"></div>
        </div>
    <?php
    }
    ?>
<?php
}
