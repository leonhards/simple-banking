<?php

/**
 * Handles admin interface and UI components
 */
class SBS_Admin
{
    /**
     * Initialize admin features
     */
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }

    /**
     * Register admin menus and submenus
     */
    public static function add_admin_menus()
    {
        // Main menu
        add_menu_page(
            __('Simple Banking System', 'simple-bank-system'),
            __('Banking', 'simple-bank-system'),
            'manage_options',
            'sbs-dashboard',
            array(__CLASS__, 'render_dashboard'),
            'dashicons-bank',
            6
        );

        // Submenus
        $submenus = array(
            'customers' => __('Customers', 'simple-bank-system'),
        );

        foreach ($submenus as $slug => $title) {
            add_submenu_page(
                'sbs-dashboard',
                $title,
                $title,
                'manage_options',
                'sbs-' . $slug,
                array(__CLASS__, 'render_' . $slug)
            );
        }
    }

    /**
     * Enqueue admin CSS and JS
     */
    public static function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'sbs-') !== false) {
            wp_enqueue_style(
                'sbs-admin-css',
                SBS_URL . 'assets/css/admin.css',
                array(),
                filemtime(SBS_PATH . 'assets/css/admin.css')
            );
        }
    }

    /**
     * Render the main dashboard page
     */
    public static function render_dashboard()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'simple-bank-system'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Banking System Dashboard', 'simple-bank-system') . '</h1>';
        echo '<div class="sbs-dashboard-widgets">';

        // Add dashboard widgets
        // self::render_quick_stats();
        // self::render_recent_transactions();
        // self::render_system_status();

        echo '</div></div>';
    }

    /**
     * Render customers management interface
     */
    public static function render_customers()
    {
        // Check user capabilities
        // if (! current_user_can('manage_options')) {
        //     wp_die(__('Unauthorized access', 'simple-bank-system'));
        // }

        // Check if we're in edit mode
        // $is_edit = isset($_GET['action']) && $_GET['action'] === 'edit';
        // $customer = array();

        // if ($is_edit) {
        //     // Get customer ID from URL
        //     $customer_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        //     // Fetch customer data
        //     global $wpdb;
        //     $table_name = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';
        //     $customer = $wpdb->get_row($wpdb->prepare(
        //         "SELECT * FROM $table_name WHERE id = %d",
        //         $customer_id
        //     ), ARRAY_A);

        //     if (!$customer) {
        //         SBS_Admin_Notice::add('error', __('Customer not found.', 'simple-bank-system'));
        //         return;
        //     }
        // }

        // Handle form submissions first
        self::handle_customer_form_submission();

        // Display customer list and form
        include SBS_PATH . 'includes/admin/views/customers.php';
    }

    /**
     * Process customer form submissions
     */
    private static function handle_customer_form_submission()
    {
        // if (! isset($_POST['sbs_customer_nonce'])) return;

        print_r($_POST);
        // // Determine if this is an add or edit request
        // $is_edit = isset($_GET['id']);
        // $nonce_action = $is_edit ? 'sbs_edit_customer_' . $_GET['id'] : 'sbs_add_customer';

        // // Verify nonce and permissions
        // if (! wp_verify_nonce($_POST['sbs_customer_nonce'], $nonce_action)) {
        //     wp_die(__('Unauthorized action', 'simple-bank-system'));
        // }

        // // Check user capabilities
        // if (!current_user_can('manage_options')) {
        //     wp_die(__('Unauthorized access.', 'simple-bank-system'));
        // }

        // // Sanitize input data
        // $customer_data = array(
        //     'cif_number'    => sanitize_text_field($_POST['cif_number']),
        //     'full_name'     => sanitize_text_field($_POST['full_name']),
        //     'address'       => sanitize_textarea_field($_POST['address']),
        //     'email'         => sanitize_email($_POST['email']),
        //     'date_of_birth' => sanitize_text_field($_POST['date_of_birth'])
        // );

        // // Validate required fields
        // if (
        //     empty($customer_data['cif_number']) || empty($customer_data['full_name']) || empty($customer_data['address'])
        //     || empty($customer_data['email']) || empty($customer_data['date_of_birth'])
        // ) {
        //     SBS_Admin_Notice::add('error', __('All fields are required', 'simple-bank-system'));
        //     return;
        // }

        // // Validate cif_number format
        // if (!preg_match('/^\d+$/', $customer_data['cif_number'])) {
        //     SBS_Admin_Notice::add('error', __('Invalid CIF number format. Please use digits only.', 'simple-bank-system'));
        //     return;
        // }

        // // Get and sanitize the date of birth
        // $date_of_birth = $customer_data['date_of_birth'];

        // // Validate date format
        // if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
        //     SBS_Admin_Notice::add('error', __('Invalid date format. Please use YYYY-MM-DD.', 'simple-bank-system'));
        //     return;
        // }

        // // Validate date is in the past
        // if (strtotime($date_of_birth) > time()) {
        //     SBS_Admin_Notice::add('error', __('Date of birth cannot be in the future.', 'simple-bank-system'));
        //     return;
        // }

        // // Validate reasonable date range (e.g., not before 1900)
        // $min_date = strtotime('1900-01-01');
        // if (strtotime($date_of_birth) < $min_date) {
        //     SBS_Admin_Notice::add('error', __('Date of birth must be after 1900.', 'simple-bank-system'));
        //     return;
        // }

        // // Save to database
        // $result = SBS_Customer::create($customer_data);

        // if (is_wp_error($result)) {
        //     SBS_Admin_Notice::add('error', $result->get_error_message());
        // } else {
        //     SBS_Admin_Notice::add('success', __('Customer created successfully', 'simple-bank-system'));
        // }
    }

    // Similar methods for accounts and transactions
}
