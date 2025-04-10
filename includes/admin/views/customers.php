<?php

/**
 * Customers View Template
 * Handles both Add and Edit modes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Determine if we're in edit mode
$is_edit = !empty($customer);
$form_title  = $is_edit ? __('Edit Customer', PLUGIN_TEXT_DOMAIN) : __('Add Customer', PLUGIN_TEXT_DOMAIN);
$form_action  = $is_edit ? 'sbs_edit_customer' : 'sbs_add_customer';
$form_nonce = $is_edit ? 'sbs_edit_customer_' . $customer['id'] : 'sbs_add_customer';
$submit_text = $is_edit ? __('Update Customer', PLUGIN_TEXT_DOMAIN) : __('Add Customer', PLUGIN_TEXT_DOMAIN);
?>

<div class="wrap sbs-admin">

    <?php if (! isset($_GET['action']) || $_GET['action'] === 'sbs_delete_customer') : ?>
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Manage Customers', PLUGIN_TEXT_DOMAIN); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sbs-customers&action=add')); ?>" class="page-title-action">
                <?php esc_html_e('Add New', PLUGIN_TEXT_DOMAIN); ?>
            </a>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <!-- Customer List Table -->
        <div class="sbs-table-wrap">
            <?php
            $customer_table = new SBS_Customer_List_Table();
            $customer_table->prepare_items();
            ?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                <?php $customer_table->search_box('Search Customers', 'search_id'); ?>
            </form>
            <?php
            $customer_table->display(); ?>
        </div>

    <?php else : ?>

        <h1 class="wp-heading-inline">
            <?php echo $form_title; ?>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <form method="post" class="sbs-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
            <?php if ($is_edit) : ?>
                <input type="hidden" name="id" value="<?php echo esc_attr($customer['id']); ?>">
            <?php endif; ?>

            <?php wp_nonce_field($form_nonce, 'sbs_customer_nonce'); ?>

            <div class="form-field">
                <label for="cif_number"><?php esc_html_e('CIF Number', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="cif_number"
                    id="cif_number"
                    value="<?php echo $is_edit ? esc_attr($customer['cif_number']) : ''; ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="full_name"><?php esc_html_e('Full Name', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="full_name"
                    id="full_name"
                    value="<?php echo $is_edit ? esc_attr($customer['full_name']) : ''; ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="address"><?php esc_html_e('Address', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="address"
                    id="address"
                    value="<?php echo $is_edit ? esc_attr($customer['address']) : ''; ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="email"><?php esc_html_e('Email', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="email"
                    name="email"
                    id="email"
                    value="<?php echo $is_edit ? esc_attr($customer['email']) : ''; ?>"
                    placeholder="example@domain.com"
                    required>
            </div>

            <div class="form-field">
                <label for="date_of_birth"><?php esc_html_e('Date of Birth', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="date_of_birth"
                    id="date_of_birth"
                    value="<?php echo $is_edit ? esc_attr($customer['date_of_birth']) : ''; ?>"
                    max="<?php echo esc_attr(date('Y-m-d')); ?>"
                    placeholder="YYYY-MM-DD"
                    pattern="\d{4}-\d{2}-\d{2}"
                    required>
                <p class="description"><?php esc_html_e('Format: YYYY-MM-DD', PLUGIN_TEXT_DOMAIN); ?></p>
            </div>

            <div style="white-space: nowrap; margin-top:40px;">
                <a href="<?php echo remove_query_arg('action', esc_URL(admin_url('admin.php?page=sbs-customers'))); ?>" class="page-title-action" style="top: 0; margin-right: 10px;">
                    <?php esc_html_e('&laquo; Back', PLUGIN_TEXT_DOMAIN); ?>
                </a>
                <p class="submit" style="display: inline; margin: 0;">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $submit_text; ?>">
                </p>
            </div>
        </form>
    <?php endif; ?>
</div>