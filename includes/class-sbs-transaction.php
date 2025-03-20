<?php

/**
 * Handle transaction data management
 */
class SBS_Transaction
{
    private static $table_name = SBS_TABLE_PREFIX . 'transactions';

    /**
     * Log a new transaction.
     *
     * @param array $data Transaction data (account_id, transaction_type, amount, description).
     * @return int|false The ID of the logged transaction, or false on failure.
     */
    public static function create_log($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Insert the transaction into the database
        $result = $wpdb->insert($table, $data);

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to save transaction data.', PLUGIN_TEXT_DOMAIN));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a transaction data
     *
     * @param number $id The ID of the customer
     * @return int|WP_Error Inserted customer ID or error
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Update the customer record
        $result = $wpdb->update(
            $table,
            $data, // Data to update (associative array)
            array('id' => $id), // WHERE clause
            array('%s', '%s', '%s', '%s'), // Data format
            array('%d') // WHERE format
        );

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to update customer.', PLUGIN_TEXT_DOMAIN));
        }

        return $result;
    }

    /**
     * Delete a customer data
     *
     * @param number $id The ID of the customer
     * @return int|WP_Error Inserted customer ID or error
     */
    public static function delete($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $result = $wpdb->delete(
            $table,
            array('id' => $id),
            array('%d')
        );

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to delete customer.', PLUGIN_TEXT_DOMAIN));
        }

        return $result;
    }

    /**
     * Get transactions for an account.
     *
     * @param int   $account_id The account ID.
     * @param array $filters    Optional filters (e.g., date range, transaction type).
     * @return array An array of transactions for the specified account.
     */
    public static function get_by_account($account_id, $filters = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . SBS_TABLE_PREFIX . 'transactions';

        // Build the base query
        $query = "SELECT * FROM $table_name WHERE account_id = %d";
        $args = [$account_id];

        // Add date range filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND created_at BETWEEN %s AND %s";
            $args[] = $filters['start_date'];
            $args[] = $filters['end_date'];
        }

        // Add transaction type filter
        if (!empty($filters['transaction_type'])) {
            $query .= " AND transaction_type = %s";
            $args[] = $filters['transaction_type'];
        }

        // Add sorting
        $query .= " ORDER BY created_at DESC";

        // Retrieve the transactions
        return $wpdb->get_results($wpdb->prepare($query, $args), ARRAY_A);
    }

    /**
     * Perform a transaction (deposit, withdrawal, or transfer).
     *
     * @param array $transaction_data Transaction data (account_id, transaction_type, amount, description).
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public static function perform_transaction($transaction_data)
    {
        // Get the account
        $account = SBS_Account::get($transaction_data['account_id']);
        if (!$account) {
            return new WP_Error('invalid_account', __('Invalid account.', PLUGIN_TEXT_DOMAIN));
        }

        // Handle different transaction types
        switch ($transaction_data['transaction_type']) {
            case 'deposit':
                // Update account balance
                $new_balance = $account['balance'] + $transaction_data['amount'];
                break;

            case 'withdrawal':
                // Check for sufficient balance
                if ($account['balance'] < $transaction_data['amount']) {
                    return new WP_Error('insufficient_balance', __('Insufficient balance.', PLUGIN_TEXT_DOMAIN));
                }
                // Update account balance
                $new_balance = $account['balance'] - $transaction_data['amount'];
                break;

            case 'transfer':
                // Validate target account
                $target_account_id = absint($transaction_data['target_account_id']);
                $target_account = SBS_Account::get($target_account_id);

                if (!$target_account) {
                    return new WP_Error('invalid_target_account', __('Invalid target account.', PLUGIN_TEXT_DOMAIN));
                }

                // Check for sufficient balance
                if ($account['balance'] < $transaction_data['amount']) {
                    return new WP_Error('insufficient_balance', __('Insufficient balance.', PLUGIN_TEXT_DOMAIN));
                }

                // Update source account balance
                $new_balance = $account['balance'] - $transaction_data['amount'];

                // Update target account balance
                $target_new_balance = $target_account['balance'] + $transaction_data['amount'];
                SBS_Account::update($target_account_id, array('balance' => $target_new_balance));
                break;

            default:
                return new WP_Error('invalid_transaction_type', __('Invalid transaction type.', PLUGIN_TEXT_DOMAIN));
        }

        // Update the account balance
        SBS_Account::update($transaction_data['account_id'], array('balance' => $new_balance));

        // Log the transaction
        self::create_log($transaction_data);

        return true;
    }
}
