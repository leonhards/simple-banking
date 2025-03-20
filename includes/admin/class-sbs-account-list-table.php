<?php

/**
 * Displays a list of accounts in the WordPress admin.
 */

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SBS_Account_List_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Account', PLUGIN_TEXT_DOMAIN),
            'plural'   => __('Accounts', PLUGIN_TEXT_DOMAIN),
            'ajax'     => false
        ));
    }

    /**
     * Define table columns
     */
    public function get_columns()
    {
        return array(
            'account_number' => __('Account No.', PLUGIN_TEXT_DOMAIN),
            'customer_name'  => __('Name', PLUGIN_TEXT_DOMAIN),
            'account_type'   => __('Type', PLUGIN_TEXT_DOMAIN),
            'balance'        => __('Balance (Rp)', PLUGIN_TEXT_DOMAIN),
            'status'         => __('Status', PLUGIN_TEXT_DOMAIN),
            'actions'        => __('Actions', PLUGIN_TEXT_DOMAIN)
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

    /**
     * Prepare table items
     */
    public function prepare_items()
    {
        global $wpdb;

        // Define column headers
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns()
        );

        // Build the base query
        $accounts_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'accounts';
        $customers_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';
        $query = "SELECT a.*, c.full_name AS customer_name FROM $accounts_table a
                  LEFT JOIN $customers_table c ON a.customer_id = c.id";

        // Handle search
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Add search if applicable
        if (!empty($search)) {
            $query .= $wpdb->prepare(
                " WHERE
                    a.account_number LIKE %s OR
                    a.account_type LIKE %s OR
                    a.status LIKE %s OR
                    c.full_name LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        // Handle sorting
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'DESC';

        // Add sorting if the column is sortable
        if (array_key_exists($orderby, $this->get_sortable_columns())) {
            $query .= ' ORDER BY ' . esc_sql($orderby);
            $query .= ' ' . esc_sql(strtoupper($order));
        }

        // Handle pagination
        $per_page = 5;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $query .= " LIMIT $per_page OFFSET $offset";

        // Fetch the items
        $this->items = $wpdb->get_results($query, ARRAY_A);

        // Set pagination arguments
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $accounts_table");
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    /**
     * Render the customer name column.
     */
    public function column_customer_name($item)
    {
        // Create a link to edit the customer
        return '<a href="' . admin_url('admin.php?page=sbs-customers&action=edit&id=' . $item['customer_id']) . '">' . esc_html(ucfirst($item['customer_name'])) . '</a>';
    }

    /**
     * Render the balance column format.
     */
    public function column_balance($item)
    {
        // Convert balance to format rupiah
        return $this->format_rupiah($item['balance']);
    }

    /**
     * Render the default column.
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'account_type':
                return esc_html(ucfirst($item['account_type']));
            case 'status':
                return esc_html(ucfirst($item['status']));
            case 'actions':
                $edit_url = admin_url('admin.php?page=sbs-accounts&action=edit&id=' . $item['id']);

                $delete_url = admin_url('admin-post.php');
                $delete_url = add_query_arg([
                    'action' => 'sbs_delete_account',
                    'id' => $item['id'],
                ], $delete_url);
                $delete_url = wp_nonce_url($delete_url, 'sbs_delete_account_' . $item['id']);

                return sprintf(
                    '<a href="%s" class="button button-edit">%s</a> ' .
                        '<a href="%s" class="button button-delete" onclick="return confirm(\'Are you sure you want to delete this customer?\')">%s</a>',
                    esc_url($edit_url),
                    esc_html__('Edit', PLUGIN_TEXT_DOMAIN),
                    esc_url($delete_url),
                    esc_html__('Delete', PLUGIN_TEXT_DOMAIN)
                );
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    /**
     * Render number in Rupiah format.
     */
    public function format_rupiah($number)
    {
        return number_format((float)$number, 2, ',', '.');
    }
}
