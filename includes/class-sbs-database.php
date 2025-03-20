<?php

/**
 * Handles database operations and schema management
 */
class SBS_Database
{
    const DB_VERSION = '1.6';

    /**
     * Creates plugin database tables on activation
     */
    public static function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table names
        $customers_table    = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';
        $accounts_table     = $wpdb->prefix . SBS_TABLE_PREFIX . 'accounts';
        $transactions_table = $wpdb->prefix . SBS_TABLE_PREFIX . 'transactions';

        // Table structure for customers
        $sql[] = "CREATE TABLE $customers_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cif_number VARCHAR(20) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            address TEXT NOT NULL,
            email VARCHAR(100) NOT NULL,
            date_of_birth DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT,
            UNIQUE KEY cif_number (cif_number),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        // Table structure for accounts
        $sql[] = "CREATE TABLE $accounts_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_number VARCHAR(20) NOT NULL,
            customer_id BIGINT UNSIGNED NOT NULL,
            account_type ENUM('saving', 'deposit') NOT NULL DEFAULT 'saving',
            balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            status ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY account_number (account_number),
            FOREIGN KEY (customer_id) REFERENCES $customers_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Table structure for transactions
        $sql[] = "CREATE TABLE $transactions_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_id BIGINT UNSIGNED NOT NULL,
            transaction_type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
            target_account_id BIGINT UNSIGNED DEFAULT NULL,
            amount DECIMAL(15,2) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES $accounts_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Save database version
        update_option('sbs_db_version', self::DB_VERSION);
    }

    /**
     * Check for database updates
     */
    public static function check_db_update()
    {
        $current_version = get_option('sbs_db_version', '0');

        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::activate(); // Run activation to update tables
        }
    }
}
