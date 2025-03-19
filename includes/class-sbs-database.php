<?php

/**
 * Handles database operations and schema management
 */
class SBS_Database
{
    /**
     * Creates plugin database tables on activation
     */
    public static function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table structure for customers
        $customers_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';
        $sql[] = "CREATE TABLE $customers_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cif_number VARCHAR(20) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            address TEXT NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            date_of_birth DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        // Table structure for accounts
        $accounts_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'accounts';
        $sql[] = "CREATE TABLE $accounts_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_number VARCHAR(20) NOT NULL UNIQUE,
            customer_id BIGINT UNSIGNED NOT NULL,
            account_type ENUM('saving', 'deposit') NOT NULL DEFAULT 'saving',
            balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES $customers_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Table structure for transactions
        $transactions_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'transactions';
        $sql[] = "CREATE TABLE $transactions_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_id BIGINT UNSIGNED NOT NULL,
            transaction_type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES $accounts_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
