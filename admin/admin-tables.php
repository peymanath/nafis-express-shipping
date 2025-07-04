<?php if ( ! defined( 'ABSPATH' ) ) exit;

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