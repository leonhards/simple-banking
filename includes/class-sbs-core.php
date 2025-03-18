<?php

/**
 * Core plugin class handling initialization and lifecycle
 *
 * @package SimpleBankSystem
 */

class SBS_Core
{
    /**
     * Initialize the plugin components
     */
    public static function init()
    {
        // Load dependencies
        self::load_dependencies();

        // Setup admin interface
        if (is_admin()) {
            SBS_Admin::init();
        }
    }

    /**
     * Load required plugin files
     */
    private static function load_dependencies()
    {
        // Database and core components
        require_once SBS_PATH . 'includes/class-sbs-customer.php';

        // Admin interface
        if (is_admin()) {
            require_once SBS_PATH . 'includes/admin/class-sbs-admin.php';
            require_once SBS_PATH . 'includes/admin/class-sbs-admin-notice.php';
            require_once SBS_PATH . 'includes/admin/class-sbs-customer-list-table.php';
        }
    }
}
