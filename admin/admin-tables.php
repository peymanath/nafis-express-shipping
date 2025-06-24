<?php

/**
 * WP TABLE
 */
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Nafis_Branch_List_Table extends WP_List_Table
{

    public function __construct($data)
    {
        parent::__construct([
            'singular' => 'branch',
            'plural'   => 'branches',
            'ajax'     => false,
        ]);

        $this->items = $data;
    }

    public function get_columns()
    {
        return [
            'id'      => esc_html__('شناسه', 'nafis-express-shipping'),
            'title'   => esc_html__('نام شعبه', 'nafis-express-shipping')
        ];
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $this->_column_headers = [$columns, [], []];

        $this->set_pagination_args([
            'total_items' => count($this->items),
            'per_page'    => 9999, // اگر صفحه‌بندی اضافه شد می‌تونی این رو تغییر بدی
        ]);
    }

    public function column_default($item, $column_name)
    {
        return esc_html($item[$column_name] ?? '-');
    }
}

class Nafis_Exited_Orders_List_Table extends WP_List_Table
{

    private $total_items;

    public function __construct($data, $total_items)
    {
        parent::__construct([
            'singular' => 'exited_order',
            'plural'   => 'exited_orders',
            'ajax'     => false,
        ]);

        $this->items = $data;
        $this->total_items = $total_items;
    }

    public function get_columns()
    {
        return [
            'orderID'           => esc_html__('شماره سفارش', 'nafis-express-shipping'),
            'barcode'           => esc_html__('بارکد', 'nafis-express-shipping'),
            'barcodeStatus'     => esc_html__('وضعیت بارکد', 'nafis-express-shipping'),
            // 'companyName'       => esc_html__('شرکت', 'nafis-express-shipping'),
            'branchName'        => esc_html__('شعبه', 'nafis-express-shipping'),
            'receiverName'      => esc_html__('گیرنده', 'nafis-express-shipping'),
            'city'              => esc_html__('شهر', 'nafis-express-shipping'),
            'postType'          => esc_html__('پست', 'nafis-express-shipping'),
            'weight'            => esc_html__('وزن (گرم)', 'nafis-express-shipping'),
            'modifiedDateTime'  => esc_html__('تاریخ ویرایش', 'nafis-express-shipping'),
            'actions'           => esc_html__('عملیات', 'nafis-express-shipping'),
        ];
    }

    public function column_default($item, $column_name)
    {
        if ($column_name === 'actions') {
            $barcode = esc_attr($item['barcode']);
            return '<button class="button track-barcode-btn" data-barcode="' . $barcode . '">' . esc_html__('رهگیری', 'nafis-express-shipping') . '</button>';
        }


        if ($column_name === 'orderID') {
            $order_id = esc_attr($item['orderID']);
            $url = admin_url("admin.php?page=wc-orders&action=edit&id={$order_id}");
            return '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($order_id) . '</a>';
        }

        return esc_html($item[$column_name] ?? '-');
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $this->_column_headers = [$columns, [], []];

        $this->set_pagination_args([
            'total_items' => $this->total_items,
            'per_page'    => $this->get_items_per_page('exited_orders_per_page', 20),
        ]);
    }
}

/**
 * Incompelte Product
 */
class Nafis_Incomplete_Products_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'product',
            'plural'   => 'products',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'title'                 => 'نام محصول',
            'length'                => 'طول',
            'width'                 => 'عرض',
            'height'                => 'ارتفاع',
            'weight'                => 'وزن',
            '_nafis_box_size'       => 'سایز جعبه',
            '_nafis_delivery_time'  => 'ارسال روز کاری',
        ];
    }

    public function prepare_items()
    {
        $per_page = 20;
        $paged    = $this->get_pagenum();

        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'meta_query'     => [
                'relation' => 'OR',
                ['key' => '_length',              'compare' => 'NOT EXISTS'],
                ['key' => '_width',               'compare' => 'NOT EXISTS'],
                ['key' => '_height',              'compare' => 'NOT EXISTS'],
                ['key' => '_weight',              'compare' => 'NOT EXISTS'],
                ['key' => '_nafis_box_size',      'compare' => 'NOT EXISTS'],
                ['key' => '_nafis_delivery_time', 'compare' => 'NOT EXISTS'],
            ],
        ];

        $query = new WP_Query($args);

        $data = [];
        foreach ($query->posts as $post) {
            $product_id = $post->ID;
            $data[] = [
                'ID'                    => $product_id,
                'title'                 => sprintf('<a href="%s">%s</a>', get_edit_post_link($product_id), esc_html(get_the_title($product_id))),
                'length'                => get_post_meta($product_id, '_length', true),
                'width'                 => get_post_meta($product_id, '_width', true),
                'height'                => get_post_meta($product_id, '_height', true),
                'weight'                => get_post_meta($product_id, '_weight', true),
                '_nafis_box_size'       => get_post_meta($product_id, '_nafis_box_size', true),
                '_nafis_delivery_time'  => get_post_meta($product_id, '_nafis_delivery_time', true),
            ];
        }

        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $query->found_posts,
            'per_page'    => $per_page,
            'total_pages' => $query->max_num_pages,
        ]);

        $this->_column_headers = [$this->get_columns(), [], []];
    }

    public function column_title($item) {
        return $item['title'];
    }

    protected function column_length($i) {
        return empty($i['length']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }
    protected function column_width($i) {
        return empty($i['width']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }
    protected function column_height($i) {
        return empty($i['height']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }
    protected function column_weight($i) {
        return empty($i['weight']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }
    protected function column__nafis_box_size($i) {
        return empty($i['_nafis_box_size']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }
    protected function column__nafis_delivery_time($i) {
        return empty($i['_nafis_delivery_time']) ? '<span style="color:red;">تکمیل نشده</span>' : '<span style="color:green;">تکمیل شده</span>';
    }

    public function column_default($item, $column_name) {
        return '';
    }
}
