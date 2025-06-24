<?php
$table = new Nafis_Incomplete_Products_Table();
$table->prepare_items();
?>
<div class="wrap">
    <?php if (empty($table->items)): ?>
        <div class="notice notice-success inline" style="margin: 10px 0; padding: 8px 12px; border-left: 4px solid #46b450; background: #f7fff7;">
            <p><?php echo esc_html__('Congratulations! All your products have complete shipping information. 🎉', 'nafis-express-shipping'); ?></p>
        </div>
    <?php else: ?>
        <?php $table->display(); ?>
    <?php endif; ?>
</div>
