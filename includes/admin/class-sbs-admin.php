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

        // Hook form submit action for customer
        add_action('admin_post_sbs_add_customer', array(__CLASS__, 'handle_customer_form'));
        add_action('admin_post_sbs_edit_customer', array(__CLASS__, 'handle_customer_form'));
        add_action('admin_post_sbs_delete_customer', array(__CLASS__, 'handle_customer_form'));
    }

    /**
     * Redirect
     */
    public static function custom_admin_redirect($path, $args = array())
    {
        // Build the base URL
        $url = admin_url('admin.php?page=' . $path);

        // Add additional query arguments if provided
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }

        // Redirect to the constructed URL
        wp_redirect(esc_url_raw($url));
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
        if (! current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'simple-bank-system'));
        }

        // Check if we're in edit mode
        $is_edit = isset($_GET['action']) && $_GET['action'] === 'edit';
        $customer = array();

        if ($is_edit) {
            // Get customer ID from URL
            $customer_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

            // Fetch customer data
            $customer = SBS_Customer::get($customer_id);

            if (!$customer) {
                SBS_Admin_Notice::add('error', __('Customer not found.', 'simple-bank-system'));
                return;
            }
        }

        // Display customer list and form
        include_once SBS_PATH . 'includes/admin/views/customers.php';
    }

    /**
     * Handle all customer form actions (create, update, delete)
     */
    public static function handle_customer_form()
    {
        $slug = 'sbs-customers';

        // Handle DELETE action
        if ($_REQUEST['action'] === 'sbs_delete_customer') {
            // Validate delete request
            if (!isset($_GET['id']) || !isset($_GET['_wpnonce'])) {
                wp_die(__('Invalid request.', 'simple-bank-system'));
            }

            $customer_id = absint($_GET['id']);
            if (!wp_verify_nonce($_GET['_wpnonce'], 'sbs_delete_customer_' . $customer_id)) {
                wp_die(__('Invalid nonce.', 'simple-bank-system'));
            }

            if (!current_user_can('manage_options')) {
                wp_die(__('Unauthorized access.', 'simple-bank-system'));
            }

            // Perform deletion
            $result = SBS_Customer::delete($customer_id);

            // Handle results
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                SBS_Admin_Notice::add('success', __('Customer deleted successfully', 'simple-bank-system'));
            }

            // Redirect to Customer table
            self::custom_admin_redirect($slug);
            exit;
        }

        // Handle form submissions edit/delete (POST requests)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sbs_customer_nonce'])) {

            // Determine action type
            $is_edit = isset($_POST['action']) && $_POST['action'] === 'sbs_edit_customer';

            $customer_id = $is_edit ? absint($_POST['id']) : 0;
            $nonce_action = $is_edit ? 'sbs_edit_customer_' . $customer_id : 'sbs_add_customer';
            $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : [];

            // Verify nonce
            if (!wp_verify_nonce($_POST['sbs_customer_nonce'], $nonce_action)) {
                wp_die(__('Unauthorized action', 'simple-bank-system'));
            }

            // Check capabilities
            if (!current_user_can('manage_options')) {
                wp_die(__('Unauthorized access.', 'simple-bank-system'));
            }

            // Sanitize data
            $customer_data = array(
                'cif_number'    => sanitize_text_field($_POST['cif_number']),
                'full_name'     => sanitize_text_field($_POST['full_name']),
                'address'       => sanitize_textarea_field($_POST['address']),
                'email'         => sanitize_email($_POST['email']),
                'date_of_birth' => sanitize_text_field($_POST['date_of_birth'])
            );

            // Validate required fields
            $required_fields = ['cif_number', 'full_name', 'address', 'email', 'date_of_birth'];
            foreach ($required_fields as $field) {
                if (empty($customer_data[$field])) {
                    SBS_Admin_Notice::add('error', __('All fields are required', 'simple-bank-system'));
                    $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                    self::custom_admin_redirect($slug, $args);
                    exit;
                }
            }

            // Validate CIF number format
            if (!preg_match('/^\d+$/', $customer_data['cif_number'])) {
                SBS_Admin_Notice::add('error', __('Invalid CIF format. Use numbers only.', 'simple-bank-system'));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            // Date validation
            $dob = $customer_data['date_of_birth'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                SBS_Admin_Notice::add('error', __('Invalid date format. Use YYYY-MM-DD.', 'simple-bank-system'));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            if (strtotime($dob) > time()) {
                SBS_Admin_Notice::add('error', __('Date of birth cannot be in the future.', 'simple-bank-system'));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            if (strtotime($dob) < strtotime('1900-01-01')) {
                SBS_Admin_Notice::add('error', __('Date of birth must be after 1900.', 'simple-bank-system'));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            // Database operation
            if ($is_edit) {
                $result = SBS_Customer::update($customer_id, $customer_data);
            } else {
                $result = SBS_Customer::create($customer_data);
            }

            // Handle operation results
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                $notice = $is_edit
                    ? __('Customer updated successfully.', 'simple-bank-system')
                    : __('Customer created successfully.', 'simple-bank-system');
                SBS_Admin_Notice::add('success', $notice);
            }

            // Redirect with appropriate parameters
            $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : [];
            self::custom_admin_redirect($slug, $args);
            exit;
        }
    }

    // Similar methods for accounts and transactions
}
