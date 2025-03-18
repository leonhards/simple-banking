<?php

/**
 * Displays customers in WordPress admin list table format
 */

// Loading WP_List_Table class file
// We need to load it as it's not automatically loaded by WordPress
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SBS_Customer_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(array(
            'singular' => __('Customer', 'simple-bank-system'),
            'plural'   => __('Customers', 'simple-bank-system'),
            'ajax'     => false
        ));
    }

    /**
     * Prepare table items
     */
    public function prepare_items()
    {
        global $wpdb;

        // Define columns and sortable columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // Pagination variables
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Build query
        $table_name = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'cif_number';
        $order = isset($_GET['order']) ? sanitize_key($_GET['order']) : 'DESC';

        $query = "SELECT * FROM $table_name";
        $query .= " ORDER BY $orderby $order";
        $query .= " LIMIT $per_page OFFSET $offset";

        // Get data
        $this->items = $wpdb->get_results($query, ARRAY_A);

        // Set pagination
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));

        $this->_column_headers = array($columns, $hidden, $sortable);
    }

    /**
     * Define table columns
     */
    public function get_columns()
    {
        return array(
            'cif_number'    => __('CIF Number', 'simple-bank-system'),
            'full_name'     => __('Name', 'simple-bank-system'),
            'address'       => __('Address', 'simple-bank-system'),
            'email'         => __('Email', 'simple-bank-system'),
            'date_of_birth' => __('Date of Birth', 'simple-bank-system'),
            'actions'       => __('Actions', 'simple-bank-system')
        );
    }

    /**
     * Render individual columns
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'cif_number':
                return esc_html($item['cif_number']);
            case 'full_name':
                return esc_html($item['full_name']);
            case 'address':
                return esc_html($item['address']);
            case 'email':
                return sanitize_email($item['email']);
            case 'date_of_birth':
                return date_i18n(get_option('date_format'), strtotime($item['date_of_birth']));
            case 'actions':
                $edit_url = admin_url('admin.php?page=sbs-customers&action=edit&id=' . $item['id']);
                $delete_url = wp_nonce_url(
                    admin_url('admin.php?page=sbs-customers&action=delete&id=' . $item['id']),
                    'sbs_delete_customer_' . $item['id']
                );
                return sprintf(
                    '<a href="%s" class="button button-edit">%s</a> ' .
                        '<a href="%s" class="button button-delete" onclick="return confirm(\'Are you sure you want to delete this customer?\')">%s</a>',
                    esc_url($edit_url),
                    esc_html__('Edit', 'simple-bank-system'),
                    esc_url($delete_url),
                    esc_html__('Delete', 'simple-bank-system')
                );
            default:
                return print_r($item, true);
        }
    }
}
