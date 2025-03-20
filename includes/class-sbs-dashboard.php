<?php

/**
 * Banking Dashboard Widgets
 *
 * This file adds custom widgets to the WordPress admin dashboard
 * for the Banking System plugin.
 */

// Exit if accessed directly to prevent security issues.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the addition of custom dashboard widgets.
 */
class SBS_Dashboard
{
    private $customers;
    private $accounts;
    private $transactions;

    /**
     * Constructor
     *
     * Hooks into the WordPress dashboard to add custom widgets.
     */
    public function __construct()
    {
        $this->customers  = SBS_TABLE_PREFIX . 'customers';
        $this->accounts   = SBS_TABLE_PREFIX . 'accounts';
        $this->transactions = SBS_TABLE_PREFIX . 'transactions';

        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
    }

    /**
     * Displays the Banking Summary widget.
     *
     * Fetches data on total customers, accounts, transactions, and balance.
     */
    public function display_summary_widget()
    {
        $total_customers = $this->get_total_customers();
        $total_accounts = $this->get_total_accounts();
        $total_transactions = $this->get_total_transactions();
        $total_balance = $this->get_total_balance();

        // Output a simple statistics dashboard in a flexible row layout.
        echo "<div class='sbs-dashboard-summary'>";
        echo "<div class='dashboard-box'>
                <span>" . esc_html__('Customers:', 'banking-plugin') . "</span> {$total_customers}
              </div>";
        echo "<div class='dashboard-box'>
                <span>" . esc_html__('Accounts:', 'banking-plugin') . "</span> {$total_accounts}
              </div>";
        echo "<div class='dashboard-box'>
                <span>" . esc_html__('Transactions:', 'banking-plugin') . "</span> {$total_transactions}
              </div>";
        echo "<div class='dashboard-box'>
                <span>" . esc_html__('Total Balance:', 'banking-plugin') . "</span> Rp" . number_format($total_balance, 2) . "
              </div>";
        echo "</div>";
    }

    /**
     * Displays the Recent Transactions widget.
     *
     * Fetches and displays the latest 5 transactions.
     */
    public function display_transactions_widget()
    {
        $transactions = $this->get_recent_transactions();

        echo "<h2 clas='hndle'>Recent Transaction</h2>";
        echo "<table class='sbs-dashboard-recent-transaction'>";
        echo "<thead style='background: #f1f1f1;'>
                <tr>
                    <th>" . esc_html__('Date', 'banking-plugin') . "</th>
                    <th>" . esc_html__('Customer', 'banking-plugin') . "</th>
                    <th>" . esc_html__('Amount (Rp)', 'banking-plugin') . "</th>
                    <th>" . esc_html__('Type', 'banking-plugin') . "</th>
                </tr>
              </thead>";
        echo "<tbody>"; // Start the tbody section

        foreach ($transactions as $tx) {
            echo "<tr>";
            echo "<td>" . esc_html($tx['created_at']) . "</td>";
            echo "<td>" . esc_html($tx['account_id']) . "</td>";
            echo "<td>" . number_format($tx['amount'], 2) . "</td>";
            echo "<td>" . esc_html($tx['transaction_type']) . "</td>";
            echo "</tr>";
        }

        echo "</tbody>"; // End the tbody section
        echo "</table>";
    }

    /**
     * Gets the total number of customers from the database.
     *
     * @return int Total customers count.
     */
    private function get_total_customers()
    {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}$this->customers");
    }

    /**
     * Gets the total number of accounts from the database.
     *
     * @return int Total accounts count.
     */
    private function get_total_accounts()
    {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}$this->accounts");
    }

    /**
     * Gets the total number of transactions from the database.
     *
     * @return int Total transactions count.
     */
    private function get_total_transactions()
    {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}$this->transactions");
    }

    /**
     * Gets the total balance across all accounts.
     *
     * @return float Total balance.
     */
    private function get_total_balance()
    {
        global $wpdb;
        return (float) $wpdb->get_var("SELECT SUM(balance) FROM {$wpdb->prefix}$this->accounts");
    }

    /**
     * Gets the latest 5 transactions from the database.
     *
     * @return array List of transactions.
     */
    private function get_recent_transactions()
    {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT created_at, account_id, transaction_type, amount FROM {$wpdb->prefix}$this->transactions ORDER BY created_at DESC LIMIT 5",
            ARRAY_A
        );
    }
}
