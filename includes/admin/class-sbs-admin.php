<?php

/**
 * Handles admin interface and UI components
 */
class SBS_Admin
{

    private static $role;

    /**
     * Initialize admin features
     */
    public static function init()
    {
        self::$role = 'edit_posts';

        add_action('admin_menu', array(__CLASS__, 'remove_comments_menu_for_editors'));
        add_action('admin_menu', array(__CLASS__, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));

        // Hook form submit action
        $form_types = ['customer', 'account', 'transaction'];
        $actions = ['add', 'edit', 'delete'];

        // Loop through form types and actions to register hooks
        foreach ($form_types as $form_type) {
            foreach ($actions as $action) {
                $hook_name = "admin_post_sbs_{$action}_{$form_type}";
                $handler_method = "handle_{$form_type}_form";
                add_action($hook_name, array(__CLASS__, $handler_method));
            }
        }
    }

    /**
     * Remove unnecessary menus from user with role 'Editor'
     */
    public static function remove_comments_menu_for_editors()
    {
        // Check if the current user is an Editor
        if (current_user_can('editor')) {
            // Remove Dashboard menu
            remove_menu_page('index.php');
            // Remove Posts menu
            remove_menu_page('edit.php');
            // Remove Comments menu
            remove_menu_page('edit-comments.php');
            // Remove Profile menu
            remove_menu_page('profile.php');
            // Remove Tools menu
            remove_menu_page('tools.php');
        }
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
            __('Simple Banking System', PLUGIN_TEXT_DOMAIN),
            __('Banking', PLUGIN_TEXT_DOMAIN),
            self::$role,
            'sbs-dashboard',
            array(__CLASS__, 'render_dashboard'),
            'dashicons-bank',
            6
        );

        // Submenus
        $submenus = array(
            'customers'     => __('Customers', PLUGIN_TEXT_DOMAIN),
            'accounts'      => __('Accounts', PLUGIN_TEXT_DOMAIN),
            'transactions'  => __('Transactions', PLUGIN_TEXT_DOMAIN),
        );

        foreach ($submenus as $slug => $title) {
            add_submenu_page(
                'sbs-dashboard',
                $title,
                $title,
                self::$role,
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

            wp_enqueue_script(
                'script-js',
                SBS_URL . 'assets/js/script.js',
                array('jquery'),
                '1.0',
                true // Load in footer
            );
        }
    }

    /**
     * Render the main dashboard page
     */
    public static function render_dashboard()
    {
        if (!current_user_can(self::$role)) {
            wp_die(__('Unauthorized access', PLUGIN_TEXT_DOMAIN));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Banking System Dashboard', PLUGIN_TEXT_DOMAIN) . '</h1>';
        echo '<div class="sbs-dashboard-widgets">';

        // Add dashboard widgets
        $dashboard = new SBS_Dashboard();
        $dashboard->display_summary_widget();
        $dashboard->display_transactions_widget();
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
        if (! current_user_can(self::$role)) {
            wp_die(__('Unauthorized access', PLUGIN_TEXT_DOMAIN));
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
                SBS_Admin_Notice::add('error', __('Customer not found.', PLUGIN_TEXT_DOMAIN));
                return;
            }
        }

        // Display customer list and form
        include SBS_PATH . 'includes/admin/views/customers.php';
    }

    /**
     * Render customers management interface
     */
    public static function render_accounts()
    {
        // Check user capabilities
        if (! current_user_can(self::$role)) {
            wp_die(__('Unauthorized access', PLUGIN_TEXT_DOMAIN));
        }

        // Check if we're in edit mode
        $is_edit = isset($_GET['action']) && $_GET['action'] === 'edit';
        $account = array();

        if ($is_edit) {
            // Get customer ID from URL
            $account_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

            // Fetch customer data
            $account = SBS_Account::get($account_id);

            if (!$account) {
                SBS_Admin_Notice::add('error', __('Account not found.', PLUGIN_TEXT_DOMAIN));
                return;
            }
        }

        // Display customer list and form
        include SBS_PATH . 'includes/admin/views/accounts.php';
    }

    /**
     * Render transactions management interface
     */
    public static function render_transactions()
    {
        // Check user capabilities
        if (! current_user_can(self::$role)) {
            wp_die(__('Unauthorized access', PLUGIN_TEXT_DOMAIN));
        }

        // Display customer list and form
        include SBS_PATH . 'includes/admin/views/transactions.php';
    }

    /**
     * Handle all customer form submissions (create, update, delete)
     */
    public static function handle_customer_form()
    {
        $slug = 'sbs-customers';

        // Handle DELETE action
        if ($_REQUEST['action'] === 'sbs_delete_customer') {
            // Validate delete request
            if (!isset($_GET['id']) || !isset($_GET['_wpnonce'])) {
                wp_die(__('Invalid request.', PLUGIN_TEXT_DOMAIN));
            }

            $customer_id = absint($_GET['id']);
            if (!wp_verify_nonce($_GET['_wpnonce'], 'sbs_delete_customer_' . $customer_id)) {
                wp_die(__('Invalid nonce.', PLUGIN_TEXT_DOMAIN));
            }

            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Perform deletion
            $result = SBS_Customer::delete($customer_id);

            // Handle results
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                SBS_Admin_Notice::add('success', __('Customer deleted successfully', PLUGIN_TEXT_DOMAIN));
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
                wp_die(__('Unauthorized action', PLUGIN_TEXT_DOMAIN));
            }

            // Check capabilities
            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Sanitize data
            $customer_data = array(
                'cif_number'     => sanitize_text_field($_POST['cif_number']),
                'full_name'      => sanitize_text_field($_POST['full_name']),
                'address'        => sanitize_textarea_field($_POST['address']),
                'email'          => sanitize_email($_POST['email']),
                'date_of_birth'  => sanitize_text_field($_POST['date_of_birth'])
            );

            // Validate required fields
            foreach ($customer_data as $value) {
                if (empty($value)) {
                    SBS_Admin_Notice::add('error', __('All fields are required', PLUGIN_TEXT_DOMAIN));
                    $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                    self::custom_admin_redirect($slug, $args);
                    exit;
                }
            }

            // Date validation
            $dob = $customer_data['date_of_birth'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                SBS_Admin_Notice::add('error', __('Invalid date format. Use YYYY-MM-DD.', PLUGIN_TEXT_DOMAIN));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            if (strtotime($dob) > time()) {
                SBS_Admin_Notice::add('error', __('Date of birth cannot be in the future.', PLUGIN_TEXT_DOMAIN));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            if (strtotime($dob) < strtotime('1900-01-01')) {
                SBS_Admin_Notice::add('error', __('Date of birth must be after 1900.', PLUGIN_TEXT_DOMAIN));
                $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : ['action' => 'add'];
                self::custom_admin_redirect($slug, $args);
                exit;
            }

            // Save or update the account
            if ($is_edit) {
                $result = SBS_Customer::update($customer_id, $customer_data);
            } else {
                $result = SBS_Customer::create($customer_data);
            }

            // Handle the result
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                $notice = $is_edit
                    ? __('Customer updated successfully.', PLUGIN_TEXT_DOMAIN)
                    : __('Customer created successfully.', PLUGIN_TEXT_DOMAIN);
                SBS_Admin_Notice::add('success', $notice);
            }

            // Redirect back to the customers list
            $args = $is_edit ? ['action' => 'edit', 'id' => $customer_id] : [];
            self::custom_admin_redirect($slug, $args);
            exit;
        }
    }

    /**
     * Handles all account form submissions (add, edit, delete).
     */
    public static function handle_account_form()
    {
        $slug = 'sbs-accounts';

        // Handle delete action
        if ($_REQUEST['action'] === 'sbs_delete_account') {
            // Validate delete request
            if (!isset($_GET['id']) || !isset($_GET['_wpnonce'])) {
                wp_die(__('Invalid request.', PLUGIN_TEXT_DOMAIN));
            }

            $account_id = absint($_GET['id']);
            if (!wp_verify_nonce($_GET['_wpnonce'], 'sbs_delete_account_' . $account_id)) {
                wp_die(__('Invalid nonce.', PLUGIN_TEXT_DOMAIN));
            }

            // Verify nonce and permissions
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'sbs_delete_account_' . $_GET['id'])) {
                wp_die(__('Invalid request.', PLUGIN_TEXT_DOMAIN));
            }

            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Delete the account
            $result = SBS_Account::delete($_GET['id']);

            // Handle the result
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                SBS_Admin_Notice::add('success', __('Account deleted successfully.', PLUGIN_TEXT_DOMAIN));
            }

            // Redirect to Account table
            self::custom_admin_redirect($slug);
            exit;
        }

        // Handle form submissions edit/delete (POST requests)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sbs_account_nonce'])) {

            // Determine action type
            $is_edit = isset($_POST['action']) && $_POST['action'] === 'sbs_edit_account';

            $account_id = $is_edit ? absint($_POST['id']) : 0;
            $nonce_action = $is_edit ? 'sbs_edit_account_' . $account_id : 'sbs_add_account';
            $args = $is_edit ? ['action' => 'edit', 'id' => $account_id] : [];

            // Verify nonce
            if (!wp_verify_nonce($_POST['sbs_account_nonce'], $nonce_action)) {
                wp_die(__('Unauthorized action', PLUGIN_TEXT_DOMAIN));
            }

            // Check capabilities
            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Sanitize input data
            $account_data = array(
                'customer_id'    => absint($_POST['customer_id']),
                'account_number' => sanitize_text_field($_POST['account_number']),
                'account_type'   => sanitize_text_field($_POST['account_type']),
                'balance'        => floatval($_POST['balance']),
                'status'         => sanitize_text_field($_POST['status'])
            );

            // Validate required fields
            foreach ($account_data as $value) {
                if (empty($value)) {
                    SBS_Admin_Notice::add('error', __('All fields are required', PLUGIN_TEXT_DOMAIN));
                    $args = $is_edit ? ['action' => 'edit', 'id' => $account_id] : ['action' => 'add'];
                    self::custom_admin_redirect($slug, $args);
                    exit;
                }
            }

            // Save or update the account
            if ($is_edit) {
                $result = SBS_Account::update($account_id, $account_data);
            } else {
                $result = SBS_Account::create($account_data);
            }

            // Handle the result
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                $notice = $is_edit
                    ? __('Account updated successfully.', PLUGIN_TEXT_DOMAIN)
                    : __('Account created successfully.', PLUGIN_TEXT_DOMAIN);
                SBS_Admin_Notice::add('success', $notice);
            }

            // Redirect back to the accounts list
            $args = $is_edit ? ['action' => 'edit', 'id' => $account_id] : [];
            self::custom_admin_redirect($slug, $args);
            exit;
        }
    }

    /**
     * Handles all transaction form submissions (add, edit, delete).
     */
    public static function handle_transaction_form()
    {

        $slug = 'sbs-transactions';

        // Handle DELETE action
        if ($_REQUEST['action'] === 'sbs_delete_transaction') {
            // Validate delete request
            if (!isset($_GET['id']) || !isset($_GET['_wpnonce'])) {
                wp_die(__('Invalid request.', PLUGIN_TEXT_DOMAIN));
            }

            $transaction_id = absint($_GET['id']);
            if (!wp_verify_nonce($_GET['_wpnonce'], 'sbs_delete_customer_' . $transaction_id)) {
                wp_die(__('Invalid nonce.', PLUGIN_TEXT_DOMAIN));
            }

            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Perform deletion
            $result = SBS_Transaction::delete($transaction_id);

            // Handle results
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                SBS_Admin_Notice::add('success', __('Transaction deleted successfully', PLUGIN_TEXT_DOMAIN));
            }

            // Redirect to Customer table
            self::custom_admin_redirect($slug);
            exit;
        }

        // Handle form submissions edit/delete (POST requests)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sbs_transaction_nonce'])) {

            // Determine action type
            $is_edit = isset($_POST['action']) && $_POST['action'] === 'sbs_edit_transaction';

            $transaction_id = $is_edit ? absint($_POST['id']) : 0;
            $nonce_action = $is_edit ? 'sbs_edit_transaction_' . $transaction_id : 'sbs_add_transaction';
            $args = $is_edit ? ['action' => 'edit', 'id' => $transaction_id] : [];

            // Verify nonce
            if (!wp_verify_nonce($_POST['sbs_transaction_nonce'], $nonce_action)) {
                wp_die(__('Unauthorized action', PLUGIN_TEXT_DOMAIN));
            }

            // Check capabilities
            if (!current_user_can(self::$role)) {
                wp_die(__('Unauthorized access.', PLUGIN_TEXT_DOMAIN));
            }

            // Sanitize input data
            $transaction_data = array(
                'account_id'        => absint($_POST['account_id']),
                'transaction_type'  => sanitize_text_field($_POST['transaction_type']),
                'amount'            => floatval($_POST['amount']),
                'description'       => sanitize_textarea_field($_POST['description'])
            );

            // Validate required fields
            foreach ($transaction_data as $value) {
                if (empty($value)) {
                    SBS_Admin_Notice::add('error', __('All fields are required', PLUGIN_TEXT_DOMAIN));
                    $args = $is_edit ? ['action' => 'edit', 'id' => $transaction_id] : ['action' => 'add'];
                    self::custom_admin_redirect($slug, $args);
                    exit;
                }
            }

            // Field target_account_id is not mandatory
            $transaction_data['target_account_id'] = sanitize_text_field($_POST['target_account_id']);

            // Save or update the account
            if ($is_edit) {
                $result = SBS_Transaction::update($transaction_id, $transaction_data);
            } else {
                // Perform the transaction before saving the log
                $result = SBS_Transaction::perform_transaction($transaction_data);
            }

            // Handle the result
            if (is_wp_error($result)) {
                SBS_Admin_Notice::add('error', $result->get_error_message());
            } else {
                $notice = $is_edit
                    ? __('Transaction updated successfully.', PLUGIN_TEXT_DOMAIN)
                    : __('Transaction created successfully.', PLUGIN_TEXT_DOMAIN);
                SBS_Admin_Notice::add('success', $notice);
            }

            // Redirect back to the accounts list
            $args = $is_edit ? ['action' => 'edit', 'id' => $transaction_id] : [];
            self::custom_admin_redirect($slug, $args);
            exit;
        }
    }
}
