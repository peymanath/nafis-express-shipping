<?php

// 🔐 Only run nonce check if that specific nonce exists
if (
    isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['nafis_nonce']) &&
    ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nafis_nonce'])), 'nafis_nonce')
) {
    wp_die('Invalid nonce');
}

if (! defined('ABSPATH')) exit;



$token = nafis_get_valid_token();

if (!$token) {
    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/not-authenticated.php';
    return;
}

$provinces = NafisOptionCachedProvinces::get([]);
$cities = NafisOptionCachedCities::get([]);
$today = current_time('Y-m-d');
$selected_city = isset($_GET['city']) ? sanitize_text_field(wp_unslash($_GET['city'])) : '';
$selected_province = isset($_GET['province_id']) ? sanitize_text_field(wp_unslash($_GET['province_id'])) : '';
$date_from = '';
if (!isset($_GET['date_from']) || sanitize_text_field(wp_unslash($_GET['date_from'])) === '') {
    $date_from = gmdate('Y-m-d'); // تاریخ امروز
} else {
    $date_from = sanitize_text_field(wp_unslash($_GET['date_from']));
}

$date_to = isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '';
$result = nafis_fetch_exited_orders($token);

/**
 * Set Barcodes in Database
 */
$ordersGrouped = [];
foreach ($result['data'] as $item) {
    $post_id = (int) $item['orderID'];
    $barcode = $item['barcode'];
    if (!isset($ordersGrouped[$post_id])) {
        $ordersGrouped[$post_id] = [];
    }
    $ordersGrouped[$post_id][] = $barcode;
}
foreach ($ordersGrouped as $post_id => $barcodes) {
    NafisPostMetaBarcodes::set($post_id, $barcodes);
}

$table = new Nafis_Exited_Orders_List_Table($result['data'], $result['total']);
$table->prepare_items();
?>

<div class="wrap">

    <form method="get" id="nafis-express-filter-form" class="nafis-filter-form">
        <div class="headline">
            <h1 class="wp-heading-inline"><?php echo esc_html__('Out orders', 'nafis-express-shipping'); ?></h1>
            <div class="nafis-filter-actions">
                <button type="submit" class="button button-large button-primary">
                    <?php esc_html_e('فیلتر', 'nafis-express-shipping'); ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=nafis-express-shipping&tab=barcode')); ?>"
                    class="button button-large">
                    <?php esc_html_e('پاک‌کردن فیلترها', 'nafis-express-shipping'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="page" value="nafis-express-shipping" />
        <input type="hidden" name="tab" value="barcode" />

        <div class="nafis-grid">
            <div class="nafis-field">
                <label for="barcode">
                    <?php esc_html_e('بارکد', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['barcode'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="barcode">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <input type="text" name="barcode" id="barcode" placeholder="مثلاً 049580400000000000000000"
                value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['barcode'] ?? ''))); ?>" />
            </div>

            <div class="nafis-field">
                <label for="order_id">
                    <?php esc_html_e('شماره سفارش', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['order_id'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="order_id">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <input type="text" name="order_id" id="order_id" placeholder="مثلاً 987654"
                value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['order_id'] ?? ''))); ?>" />
            </div>

            <div class="nafis-field">
                <label for="receiverName">
                    <?php esc_html_e('نام گیرنده', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['receiverName'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="receiverName">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <input type="text" name="receiverName" id="receiverName" placeholder="مثلاً علی رضایی"
                value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['receiverName'] ?? ''))); ?>" />
            </div>

            <div class="nafis-field">
                <label for="date_from">
                    <?php esc_html_e('از تاریخ', 'nafis-express-shipping'); ?>
                    <?php if (!empty($date_from)): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="date_from">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($date_from); ?>" />
            </div>

            <div class="nafis-field">
                <label for="date_to">
                    <?php esc_html_e('تا تاریخ', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['date_to'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="date_to">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($date_to); ?>"  />
            </div>
        </div>

        <div class="nafis-grid">
            <div class="nafis-field">
                <label for="status">
                    <?php esc_html_e('وضعیت بارکد', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['status'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="status">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <select name="status" id="status">
                    <option value=""><?php esc_html_e('همه وضعیت‌ها', 'nafis-express-shipping'); ?></option>
                    <?php foreach (NafisOptionCachedStatuses::get([]) as $status): ?>
                        <option value="<?php echo esc_attr($status['id']); ?>"
                            <?php selected($_GET['status'] ?? '', $status['id']); ?>>
                            <?php echo esc_html($status['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="nafis-field">
                <label for="province-select">
                    <?php esc_html_e('استان', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['province_id'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="province_id">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <select id="province-select" name="province_id">
                    <option value=""><?php esc_html_e('همه استان‌ها', 'nafis-express-shipping'); ?></option>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo esc_attr($province['id']); ?>"
                            <?php selected(sanitize_text_field(wp_unslash($_GET['status'] ?? '')), $status['id']); ?>>
                            <?php echo esc_html($province['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="nafis-field">
                <label for="city-select">
                    <?php esc_html_e('شهر', 'nafis-express-shipping'); ?>
                    <?php if (!empty($_GET['city'])): ?>
                        <span class="nafis-filter-badge">
                            (<?php esc_html_e('فیلتر شده', 'nafis-express-shipping'); ?>)
                            <a href="#" class="nafis-remove-filter" data-filter="city">×</a>
                        </span>
                    <?php endif; ?>
                </label>
                <select id="city-select" name="city" disabled>
                    <option value=""><?php esc_html_e('ابتدا استان را انتخاب کنید', 'nafis-express-shipping'); ?></option>
                </select>
            </div>
        </div>



    </form>

    <?php $table->display(); ?>
</div>


<script>
    const nafisCities = <?php echo wp_json_encode($cities); ?>;
    const selectedCity = "<?php echo esc_js($selected_city); ?>";
    const selectedProvince = "<?php echo esc_js($selected_province); ?>";

    function updateCityOptions(provinceID) {
        const citySelect = document.getElementById('city-select');
        citySelect.innerHTML = '<option value=""><?php echo esc_js(__('همه شهرها', 'nafis-express-shipping')); ?></option>';

        const filteredCities = nafisCities.filter(city => city.provinceID == provinceID);
        filteredCities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.id;
            option.textContent = city.name;
            if (city.id == selectedCity) {
                option.selected = true;
            }
            citySelect.appendChild(option);
        });

        citySelect.disabled = filteredCities.length === 0;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province-select');
        const citySelect = document.getElementById('city-select');

        provinceSelect.addEventListener('change', function() {
            updateCityOptions(this.value);
        });

        if (selectedProvince) {
            updateCityOptions(selectedProvince);
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nafis-remove-filter').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const param = this.dataset.filter;
                const url = new URL(window.location.href);

                if (param === 'date_from') {
                    url.searchParams.set('date_from', ''); // مقدار خالی بذار
                } else {
                    url.searchParams.delete(param); // سایر فیلدها حذف بشن
                }

                window.location.href = url.toString();
            });
        });
    });
</script>