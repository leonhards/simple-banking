<?php

/**
 * Customer Form Template
 * Handles both Add and Edit modes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wrap sbs-admin">


    <h1 class="wp-heading-inline">
        <?php esc_html_e('Manage Customers', 'simple-bank-system'); ?>
    </h1>

    <?php SBS_Admin_Notice::display(); ?>

    <!-- Customer List Table -->
    <div class="sbs-table-wrap">
        <?php
        $customer_table = new SBS_Customer_List_Table();
        $customer_table->prepare_items();
        $customer_table->display();
        ?>
    </div>


    <h1 class="wp-heading-inline">
        ASD
    </h1>

    <form method="post" class="sbs-form">


        <div class="form-field">
            <label for="cif_number"><?php esc_html_e('CIF Number', 'simple-bank-system'); ?></label>
            <input type="text"
                name="cif_number"
                pattern="\d+"
                required>
        </div>

        <div class="form-field">
            <label for="full_name"><?php esc_html_e('Full Name', 'simple-bank-system'); ?></label>
            <input type="text"
                name="full_name"
                required>
        </div>

        <div class="form-field">
            <label for="address"><?php esc_html_e('Address', 'simple-bank-system'); ?></label>
            <input type="text"
                name="address"
                required>
        </div>

        <div class="form-field">
            <label for="email"><?php esc_html_e('Email', 'simple-bank-system'); ?></label>
            <input type="email"
                name="email"
                placeholder="example@domain.com"
                required>
        </div>

        <div class="form-field">
            <label for="date_of_birth"><?php esc_html_e('Date of Birth', 'simple-bank-system'); ?></label>
            <input type="text"
                name="date_of_birth"
                max="<?php echo esc_attr(date('Y-m-d')); ?>"
                placeholder="YYYY-MM-DD"
                pattern="\d{4}-\d{2}-\d{2}"
                required>
            <p class="description"><?php esc_html_e('Format: YYYY-MM-DD', 'simple-bank-system'); ?></p>
        </div>

        <div style="white-space: nowrap; margin-top:40px;">
            <a href="<?php echo remove_query_arg('action', esc_URL(admin_url('admin.php?page=sbs-customers'))); ?>" class="page-title-action" style="display: inline; top: 0; margin-right: 10px;">
                <?php esc_html_e('&laquo; Back', 'simple-bank-system'); ?>
            </a>
            <p class="submit" style="display: inline; margin: 0;">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Add Customer', 'simple-bank-system'); ?>">
            </p>
        </div>
    </form>

</div>