<?php

/**
 * Accounts Form Template
 * Handles both Add and Edit modes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Determine if we're in edit mode
$is_edit = !empty($account);
$form_title  = $is_edit ? __('Edit Account', PLUGIN_TEXT_DOMAIN) : __('Add Account', PLUGIN_TEXT_DOMAIN);
$form_action  = $is_edit ? 'sbs_edit_account' : 'sbs_add_account';
$form_nonce = $is_edit ? 'sbs_edit_account_' . $account['id'] : 'sbs_add_account';
$submit_text = $is_edit ? __('Update Account', PLUGIN_TEXT_DOMAIN) : __('Add Account', PLUGIN_TEXT_DOMAIN);
?>

<div class="wrap sbs-admin">

    <?php if (! isset($_GET['action']) || $_GET['action'] === 'sbs_delete_account') : ?>

        <h1 class="wp-heading-inline">
            <?php esc_html_e('Manage Accounts', PLUGIN_TEXT_DOMAIN); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sbs-accounts&action=add')); ?>" class="page-title-action">
                <?php esc_html_e('Add New', PLUGIN_TEXT_DOMAIN); ?>
            </a>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <!-- Account List Table -->
        <div class="sbs-table-wrap">
            <?php
            $account_table = new SBS_Account_List_Table();
            $account_table->prepare_items();
            ?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
                <?php $account_table->search_box('Search Accounts', 'search_id'); ?>
            </form>
            <?php
            $account_table->display(); ?>
        </div>

    <?php else : ?>

        <h1 class="wp-heading-inline">
            <?php echo $form_title; ?>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <form method="post" class="sbs-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
            <?php if ($is_edit) : ?>
                <input type="hidden" name="id" value="<?php echo esc_attr($account['id']); ?>">
            <?php endif; ?>

            <?php wp_nonce_field($form_nonce, 'sbs_account_nonce'); ?>

            <div class="form-field">
                <label for="customer_id"><?php esc_html_e('Customer Name', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="customer_id" id="customer_id" required>
                    <option value=""><?php esc_html_e('Select a customer', PLUGIN_TEXT_DOMAIN); ?></option>
                    <?php
                    $customers = SBS_Customer::get_all();
                    foreach ($customers as $customer) {
                        echo '<option value="' . esc_attr($customer['id']) . '" ' .
                            selected($account && $account['customer_id'] === $customer['id'], true, false) . '>' .
                            esc_html($customer['full_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-field">
                <label for="account_number"><?php esc_html_e('Account Number', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="account_number"
                    id="account_number"
                    value="<?php echo $is_edit ? esc_attr($account['account_number']) : ''; ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="account_type"><?php esc_html_e('Account Type', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="account_type" id="account_type" required>
                    <option value="saving" <?php selected($account && $account['account_type'] === 'saving', true); ?>><?php esc_html_e('Saving', PLUGIN_TEXT_DOMAIN); ?></option>
                    <option value="deposit" <?php selected($account && $account['account_type'] === 'deposit', true); ?>><?php esc_html_e('Deposit', PLUGIN_TEXT_DOMAIN); ?></option>
                </select>
            </div>

            <div class="form-field">
                <label for="balance"><?php esc_html_e('Balance', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="balance"
                    id="balance"
                    value="<?php echo esc_attr($account ? $account['balance'] : ''); ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="status"><?php esc_html_e('Status', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="status" id="status" required>
                    <option value="active" <?php selected($account && $account['status'] === 'active', true); ?>><?php esc_html_e('Active', PLUGIN_TEXT_DOMAIN); ?></option>
                    <option value="inactive" <?php selected($account && $account['status'] === 'inactive', true); ?>><?php esc_html_e('Inactive', PLUGIN_TEXT_DOMAIN); ?></option>
                    <option value="closed" <?php selected($account && $account['status'] === 'closed', true); ?>><?php esc_html_e('Closed', PLUGIN_TEXT_DOMAIN); ?></option>
                </select>
            </div>

            <div style="white-space: nowrap; margin-top:40px;">
                <a href="<?php echo remove_query_arg('action', esc_URL(admin_url('admin.php?page=sbs-accounts'))); ?>" class="page-title-action" style="top: 0; margin-right: 10px;">
                    <?php esc_html_e('&laquo; Back', PLUGIN_TEXT_DOMAIN); ?>
                </a>
                <p class="submit" style="display: inline; margin: 0;">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $submit_text; ?>">
                </p>
            </div>
        </form>
    <?php endif; ?>
</div>