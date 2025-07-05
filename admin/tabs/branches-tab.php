<?php if ( ! defined( 'ABSPATH' ) ) exit;

$token = nafis_get_valid_token();
if (!$token) {
    require_once NAFIS_EXPRESS_SHIPPING_ADMIN . 'tabs/not-authenticated.php';
    return;
}

$branches = nafis_fetch_branches_from_api();
$table = new Nafis_Branch_List_Table($branches);
$table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('شعب نفیس اکسپرس', 'nafis-express-shipping'); ?></h1>
    <hr class="wp-header-end">
    <?php $table->display(); ?>
</div>
