<?php

/**
 * Handles account data management
 */
class SBS_Account
{
    private static $table_name = SBS_TABLE_PREFIX . 'accounts';

    /**
     * Create a new account.
     *
     * @param array $data Account data (customer_id, account_number, account_type, balance, status).
     * @return int|false The ID of the newly created account, or false on failure.
     */
    public static function create($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Check for duplicate email
        $check_acct_number = $wpdb->get_var($wpdb->prepare(
            "SELECT account_number FROM $table WHERE account_number = %s",
            $data['account_number']
        ));

        if ($check_acct_number) {
            return new WP_Error('duplicate_acct_number', __('The account number \'' . $data['account_number'] . '\' already exists.', PLUGIN_TEXT_DOMAIN));
        }

        // Insert the account into the database
        $result = $wpdb->insert($table, $data);

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to insert account.', PLUGIN_TEXT_DOMAIN));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update an existing account.
     *
     * @param int $id The account ID.
     * @param array $data Updated account data.
     * @return int|false The number of rows updated, or false on failure.
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Update the account in the database
        $result = $wpdb->update($table, $data, array('id' => $id));

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to update account.', PLUGIN_TEXT_DOMAIN));
        }

        return $result;
    }

    /**
     * Delete an account.
     *
     * @param int $id The account ID.
     * @return int|false The number of rows deleted, or false on failure.
     */
    public static function delete($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Delete the account from the database
        $result = $wpdb->delete($table, array('id' => $id));

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to delete account.', PLUGIN_TEXT_DOMAIN));
        }

        return $result;
    }

    /**
     * Get a single account by ID.
     *
     * @param int $id The account ID.
     * @return array|false The account data as an associative array, or false if not found.
     */
    public static function get($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $account_id = absint($id);
        if (!$account_id) {
            return new WP_Error('invalid_id', __('Invalid account ID.', PLUGIN_TEXT_DOMAIN));
        }

        // Retrieve the account from the database
        $account = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $account_id
        ), ARRAY_A);

        // Check if the customer exists
        if (empty($account)) {
            return new WP_Error('not_found', __('Account not found.', PLUGIN_TEXT_DOMAIN));
        }

        return $account;
    }

    /**
     * Get all accounts.
     *
     * @return array An array of accounts, each as an associative array.
     */
    public static function get_all()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Retrieve all accounts from the database
        return $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
    }

    /**
     * Get accounts by customer ID.
     *
     * @param int $customer_id The customer ID.
     * @return array An array of accounts for the specified customer.
     */
    public static function get_by_customer($customer_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Retrieve accounts for the specified customer
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE customer_id = %d", $customer_id), ARRAY_A);
    }
}
