<?php

if (!defined('ABSPATH')) exit; // Prevent direct access to the plugin file

add_action('woocommerce_product_options_shipping', function () {
    $token = nafis_get_valid_token();

    echo '<div class="options_group" style="padding: 0px 8px;">';
    echo '<h3><span>' . esc_html__('نفیس اکسپرس', 'nafis-express-shipping') . '</span></h3>';

    if (!$token) {
        echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 8px 12px; border-left: 4px solid #ffba00; background: #fffbe5;">
                <p>' . esc_html__('برای استفاده از امکانات نفیس اکسپرس باید وارد حساب کاربری شوید.', 'nafis-express-shipping') . '</p>
              </div>';
        echo '</div>';
        return;
    }

    $boxes = NafisOptionCachedBoxes::get([]);

    if (!empty($boxes)) {
        woocommerce_wp_select([
            'id' => '_nafis_box_size',
            'label' => esc_html__('سایز جعبه', 'nafis-express-shipping'),
            'options' => nafis_prepare_box_options($boxes),
            'desc_tip' => true,
            'description' => esc_html__('سایز مناسب جعبه برای ارسال کالا در صورتی که فقط و فقط یک واحد از محصول مد نظر در سبد خرید داشته باشد.', 'nafis-express-shipping'),
            'value' => get_post_meta(get_the_ID(), '_nafis_box_size', true),
        ]);
    } else {
        echo '<div class="notice notice-warning inline" style="margin: 10px 0; padding: 8px 12px; border-left: 4px solid #ffba00; background: #fffbe5;">
                <p>' . esc_html__('هیچ سایز جعبه‌ای یافت نشد. لطفاً از صفحه ورود، اطلاعات را به‌روزرسانی کنید.', 'nafis-express-shipping') . '</p>
              </div>';
    }

    woocommerce_wp_text_input([
        'id' => '_nafis_delivery_time',
        'label' => esc_html__('زمان ارسال (روز کاری)', 'nafis-express-shipping'),
        'type' => 'number',
        'desc_tip' => true,
        'description' => esc_html__('زمان تقریبی ارسال از زمان سفارش در x روز کاری دیگر', 'nafis-express-shipping'),
        'value' => get_post_meta(get_the_ID(), '_nafis_delivery_time', true),
    ]);

    echo '</div>';
});

add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_nafis_box_size'])) {
        update_post_meta($post_id, '_nafis_box_size', sanitize_text_field($_POST['_nafis_box_size']));
    }

    if (isset($_POST['_nafis_delivery_time']) && $_POST['_nafis_delivery_time'] !== '') {
        update_post_meta($post_id, '_nafis_delivery_time', intval($_POST['_nafis_delivery_time']));
    }
});