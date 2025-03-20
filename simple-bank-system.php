<?php

/**
 * Plugin Name: Simple Banking System
 * Description: Manages banking operations including customers, accounts, and transactions
 * Version: 1.0.0
 * Author: Leon Sinaga
 * Text Domain: simple-bank-system
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit; // Prevent direct access

// Define plugin constants
define('SBS_PATH', plugin_dir_path(__FILE__));
define('SBS_URL', plugin_dir_url(__FILE__));
define('SBS_TABLE_PREFIX', 'sbs_');
define('PLUGIN_TEXT_DOMAIN', PLUGIN_TEXT_DOMAIN);

// Load database class before activation hook
require_once SBS_PATH . 'includes/class-sbs-database.php';

// Register activation/deactivation hooks
register_activation_hook(__FILE__, array('SBS_Database', 'activate'));

// Load core files
require_once SBS_PATH . 'includes/class-sbs-core.php';

// Initialize plugin
add_action('plugins_loaded', array('SBS_Core', 'init'));

// Run database update check
add_action('init', array('SBS_Database', 'check_db_update'));
