<?php

/**
 * Transaction View Template
 * Handles both Add and Edit modes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Determine if we're in edit mode
$is_edit = !empty($transaction);
$form_title  = $is_edit ? __('Edit Transaction', PLUGIN_TEXT_DOMAIN) : __('Add Transaction', PLUGIN_TEXT_DOMAIN);
$form_action  = $is_edit ? 'sbs_edit_transaction' : 'sbs_add_transaction';
$form_nonce = $is_edit ? 'sbs_edit_transaction_' . $transaction['id'] : 'sbs_add_transaction';
$submit_text = $is_edit ? __('Update Transaction', PLUGIN_TEXT_DOMAIN) : __('Add Transaction', PLUGIN_TEXT_DOMAIN);

$accounts = SBS_Account::get_all();
?>

<div class="wrap sbs-admin">

    <?php if (! isset($_GET['action']) || $_GET['action'] === 'sbs_delete_transaction') : ?>

        <h1 class="wp-heading-inline">
            <?php esc_html_e('Manage Transactions', PLUGIN_TEXT_DOMAIN); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sbs-transactions&action=add')); ?>" class="page-title-action">
                <?php esc_html_e('Add New', PLUGIN_TEXT_DOMAIN); ?>
            </a>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <!-- Customer List Table -->
        <div class="sbs-table-wrap">
            <?php
            $transaction_table = new SBS_Transaction_List_Table();
            $transaction_table->prepare_items();
            ?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

                <!-- Search Box -->
                <?php $transaction_table->search_box('Search Transactions', 'search_id'); ?>
            </form>
            <?php
            $transaction_table->display(); ?>
        </div>

    <?php else : ?>

        <h1 class="wp-heading-inline">
            <?php echo $form_title; ?>
        </h1>

        <?php SBS_Admin_Notice::display(); ?>

        <form method="post" class="sbs-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
            <?php if ($is_edit) : ?>
                <input type="hidden" name="id" value="<?php echo esc_attr($transaction['id']); ?>">
            <?php endif; ?>

            <?php wp_nonce_field($form_nonce, 'sbs_transaction_nonce'); ?>

            <div class="form-field">
                <label for="account_id"><?php esc_html_e('Account', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="account_id" id="account_id" required>
                    <option value=""><?php esc_html_e('Select Account', PLUGIN_TEXT_DOMAIN); ?></option>
                    <?php
                    foreach ($accounts as $account) {
                        echo '<option value="' . esc_attr($account['id']) . '" ' .
                            selected($transaction && $transaction['account_id'] === $account['id'], true, false) . '>' .
                            esc_html($account['account_number']) . ' - ' . SBS_Customer::get($account['customer_id'])['full_name'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-field">
                <label for="transaction_type"><?php esc_html_e('Transaction Type', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="transaction_type" id="transaction_type" required>
                    <option value=""><?php esc_html_e('Select Type', PLUGIN_TEXT_DOMAIN); ?></option>
                    <option value="deposit" <?php selected($transaction && $transaction['transaction_type'] === 'deposit', true); ?>>
                        <?php esc_html_e('Deposit', PLUGIN_TEXT_DOMAIN); ?>
                    </option>
                    <option value="withdrawal" <?php selected($transaction && $transaction['transaction_type'] === 'withdrawal', true); ?>>
                        <?php esc_html_e('Withdrawal', PLUGIN_TEXT_DOMAIN); ?>
                    </option>
                    <option value="transfer" <?php selected($transaction && $transaction['transaction_type'] === 'transfer', true); ?>>
                        <?php esc_html_e('Transfer', PLUGIN_TEXT_DOMAIN); ?>
                    </option>
                </select>
            </div>

            <div class="form-field transfer-field" style="display: none;">
                <label for="target_account_id"><?php esc_html_e('Target Account', PLUGIN_TEXT_DOMAIN); ?></label>
                <select name="target_account_id" id="target_account_id" required>
                    <option value=""><?php esc_html_e('Select Target Account', PLUGIN_TEXT_DOMAIN); ?></option>
                    <?php
                    foreach ($accounts as $account) {
                        echo '<option value="' . esc_attr($account['id']) . '" ' .
                            selected($transaction && $transaction['target_account_id'] === $account['id'], true, false) . '>' .
                            esc_html($account['account_number']) . ' - ' . SBS_Customer::get($account['customer_id'])['full_name'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-field">
                <label for="amount"><?php esc_html_e('Amount', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="amount"
                    id="amount"
                    value="<?php echo esc_attr($transaction ? (int)$transaction['amount'] : ''); ?>"
                    required>
            </div>

            <div class="form-field">
                <label for="description"><?php esc_html_e('Description', PLUGIN_TEXT_DOMAIN); ?></label>
                <input type="text"
                    name="description"
                    id="description"
                    value="<?php echo $is_edit ? esc_attr($transaction['description']) : ''; ?>"
                    required>
            </div>

            <div style="white-space: nowrap; margin-top:40px;">
                <a href="<?php echo remove_query_arg('action', esc_URL(admin_url('admin.php?page=sbs-transactions'))); ?>"
                    class="page-title-action" style="top: 0; margin-right: 10px;">
                    <?php esc_html_e('&laquo; Back', PLUGIN_TEXT_DOMAIN); ?>
                </a>
                <p class="submit" style="display: inline; margin: 0;">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $submit_text; ?>">
                </p>
            </div>
        </form>

        <script>
            // Show/hide transfer target field dynamically
            jQuery(document).ready(function($) {
                $('#transaction_type').change(function() {
                    if ($(this).val() === 'transfer') {
                        $('.transfer-field').show();
                        $('#target_account_id').prop('required', true);
                    } else {
                        $('.transfer-field').hide();
                        $('#target_account_id').prop('required', false);
                    }
                }).trigger('change');
            });
        </script>
    <?php endif; ?>
</div>