<?php if ( ! defined( 'ABSPATH' ) ) exit;


function nafis_fetch_boxes_from_api($token) {
    $res = nafis_api_request('GET', 'panel/warehouse-box', $token);
    return $res['success'] ? ($res['data']['data'] ?? []) : [];
}

function nafis_prepare_box_options($boxes) {
    $options = ['' => esc_html__('— انتخاب جعبه —', 'nafis-express-shipping')];
    foreach ($boxes as $box) {
        $label = esc_html(sprintf(
            /* translators: 1: code, 2: width, 3: height, 4: length, 5: weight */
            __('کد %1$s — %2$dx%3$dx%4$d — %5$dg', 'nafis-express-shipping'),
            $box['barcode'],
            $box['lenght'],  // احتمالاً باید باشه: length
            $box['width'],
            $box['height'],
            $box['weight']
        ));
        $options[$box['postSizeId']] = $label;
    }
    return $options;
}