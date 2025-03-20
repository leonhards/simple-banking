<?php

/**
 * Displays a list of accounts in the WordPress admin.
 */

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SBS_Transaction_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Transaction', PLUGIN_TEXT_DOMAIN),
            'plural'   => __('Transactions', PLUGIN_TEXT_DOMAIN),
            'ajax'     => false
        ));
    }

    /**
     * Define table columns
     */
    public function get_columns()
    {
        return array(
            'transaction_type' => __('Type', PLUGIN_TEXT_DOMAIN),
            'account_number'   => __('Account No.', PLUGIN_TEXT_DOMAIN),
            'customer_name'    => __('Customer', PLUGIN_TEXT_DOMAIN),
            'transfer_account' => __('To', PLUGIN_TEXT_DOMAIN),
            'amount'           => __('Amount', PLUGIN_TEXT_DOMAIN),
            'description'      => __('Description', PLUGIN_TEXT_DOMAIN),
            'created_at'       => __('Date', PLUGIN_TEXT_DOMAIN)
        );
    }

    /**
     * Define sortable columns.
     */
    public function get_sortable_columns()
    {
        return array(
            'account_number' => array('account_number', false), // Not sorted by default
            'customer_name'  => array('customer_name', false),  // Not sorted by default
            'balance'        => array('balance', false),        // Not sorted by default
            'created_at'     => array('created_at', true)       // Sorted by default (descending)
        );
    }

    public function prepare_items()
    {
        global $wpdb;

        // Define column headers
        $this->_column_headers = array(
            $this->get_columns(),
            array(), // Hidden columns
            $this->get_sortable_columns()
        );

        // Build base query with joins
        $transactions_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'transactions';
        $accounts_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'accounts';
        $customers_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';

        $query = "SELECT t.*, a.account_number, c.full_name AS customer_name
                  FROM $transactions_table t
                  INNER JOIN $accounts_table a ON t.account_id = a.id
                  INNER JOIN $customers_table c ON a.customer_id = c.id";

        // Handle search
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Add search if applicable
        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " WHERE
                    t.description LIKE %s OR
                    a.account_number LIKE %s OR
                    c.full_name LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
            );
        }

        // Handle transaction type filter
        $transaction_type = isset($_REQUEST['transaction_type']) ? sanitize_text_field($_REQUEST['transaction_type']) : '';
        if (!empty($transaction_type)) {
            $query .= $wpdb->prepare(" AND t.transaction_type = %s", $transaction_type);
        }

        // Handle date filter
        $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
        $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';

        if (!empty($start_date) && !empty($end_date)) {
            $query .= $wpdb->prepare(" AND DATE(t.created_at) BETWEEN %s AND %s", $start_date, $end_date);
        } elseif (!empty($start_date)) {
            $query .= $wpdb->prepare(" AND DATE(t.created_at) >= %s", $start_date);
        } elseif (!empty($end_date)) {
            $query .= $wpdb->prepare(" AND DATE(t.created_at) <= %s", $end_date);
        }

        // Handle sorting
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'DESC';

        // Add sorting if the column is sortable
        if (array_key_exists($orderby, $this->get_sortable_columns())) {
            $query .= ' ORDER BY ' . esc_sql($orderby);
            $query .= ' ' . esc_sql(strtoupper($order));
        }

        // Pagination
        $per_page = 5;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $query .= " LIMIT $per_page OFFSET $offset";

        // Fetch items
        $this->items = $wpdb->get_results($query, ARRAY_A);

        // Set pagination
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $transactions_table");
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    /**
     * Add filter by date and transaction type
     */
    public function extra_tablenav($which)
    {
        if ($which == "top") {
?>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

                <div class="alignleft actions">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>" />
                </div>

                <div class="alignleft actions">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>" />
                </div>

                <div class="alignleft">
                    <label for="transaction_type">Transaction Type:</label>
                    <select name="transaction_type">
                        <option value="">All Types</option>
                        <option value="deposit" <?php selected($_GET['transaction_type'] ?? '', 'deposit'); ?>>Deposit</option>
                        <option value="withdrawal" <?php selected($_GET['transaction_type'] ?? '', 'withdrawal'); ?>>Withdrawal</option>
                        <option value="transfer" <?php selected($_GET['transaction_type'] ?? '', 'transfer'); ?>>Transfer</option>
                    </select>

                    <input type="submit" class="button" value="Filter" />
                </div>
            </form>
<?php
        }
    }

    /**
     * Render the customer name column.
     */
    public function column_customer_name($item)
    {
        $customer_id = SBS_Account::get($item['account_id'])['customer_id'];
        return '<a href="' . admin_url('admin.php?page=sbs-customers&action=edit&id=' . $customer_id) . '">'
            . esc_html(ucfirst($item['customer_name'])) . '</a>';
    }

    /**
     * Render the customer name column.
     */
    public function column_transfer_account($item)
    {
        if (!$item['target_account_id']) return;

        $target_account_id = isset($item['target_account_id']) ? SBS_Account::get($item['target_account_id'])['customer_id'] : '';
        $target_name = SBS_Customer::get($target_account_id)['full_name'];
        return esc_html(ucfirst($target_name));
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'transaction_type':
                return esc_html(ucfirst($item['transaction_type']));
            case 'amount':
                return number_format($item[$column_name], 2, ',', '.');
            case 'created_at':
                return date_i18n(get_option('date_format'), strtotime($item[$column_name]));
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }
}
