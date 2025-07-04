<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="notice notice-error is-dismissible">
    <p><?= esc_html__('⚠️ You must log in first to view this section.', 'nafis-express-shipping'); ?></p>
</div>

<div
    style="margin-top:20px; padding:15px; background:#fff3cd; border:1px solid #ffeeba; border-radius:5px; color:#856404;">
    <strong><?= esc_html__('Notice:', 'nafis-express-shipping'); ?></strong>
    <?= esc_html__('To use Nafis Express services, you must first log in to your panel account to retrieve the access token.', 'nafis-express-shipping'); ?>
</div>