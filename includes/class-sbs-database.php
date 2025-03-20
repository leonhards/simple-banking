<?php

/**
 * Handles database operations and schema management
 */
class SBS_Database
{
    const DB_VERSION = '2.3';

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

        // Check if tables exist
        $customers_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$customers_table'") === $customers_table;
        $accounts_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$accounts_table'") === $accounts_table;
        $transactions_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$transactions_table'") === $transactions_table;

        // Table structure for customers
        if (!$customers_table_exists) {
            $sql[] = "CREATE TABLE $customers_table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                cif_number VARCHAR(20) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                address TEXT NOT NULL,
                email VARCHAR(100) NOT NULL,
                date_of_birth DATE NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY cif_number (cif_number),
                UNIQUE KEY email (email),
                PRIMARY KEY  (id)
            ) $charset_collate;";
        }

        // Table structure for accounts
        if (!$accounts_table_exists) {
            $sql[] = "CREATE TABLE $accounts_table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                account_number VARCHAR(20) NOT NULL,
                customer_id BIGINT UNSIGNED NOT NULL,
                account_type ENUM('saving', 'deposit') NOT NULL DEFAULT 'saving',
                balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                status ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY account_number (account_number),
                INDEX customer_id (customer_id),
                PRIMARY KEY  (id)
            ) $charset_collate;";
        }

        // Table structure for transactions
        if (!$transactions_table_exists) {
            $sql[] = "CREATE TABLE $transactions_table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                account_id BIGINT UNSIGNED NOT NULL,
                transaction_type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
                target_account_id BIGINT UNSIGNED DEFAULT NULL,
                amount DECIMAL(15,2) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX account_id (account_id),
                INDEX target_account_id (target_account_id),
                PRIMARY KEY  (id)
            ) $charset_collate;";
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Create tables if they don't exist
        if (!empty($sql)) {
            foreach ($sql as $query) {
                dbDelta($query);
            }
        }

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
