<?php

/**
 * Handles customer data management
 */
class SBS_Customer
{
    private static $table_name = SBS_TABLE_PREFIX . 'customers';

    /**
     * Create new customer with validation
     *
     * @param array $data Sanitized customer data
     * @return int|WP_Error Inserted customer ID or error
     */
    public static function create($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Check for duplicate email
        $check_email = $wpdb->get_var($wpdb->prepare(
            "SELECT email FROM $table WHERE email = %s",
            $data['email']
        ));

        if ($check_email) {
            return new WP_Error('duplicate_email', __('A customer with email \'' . $data['email'] . '\' already exists.', PLUGIN_TEXT_DOMAIN));
        }

        // Check for duplicate CIF number
        $check_cif = $wpdb->get_var($wpdb->prepare(
            "SELECT cif_number FROM $table WHERE cif_number = %s",
            $data['cif_number']
        ));

        if ($check_cif) {
            return new WP_Error('duplicate_cif', __('A customer with CIF number \'' . $data['cif_number'] . '\' already exists.', PLUGIN_TEXT_DOMAIN));
        }

        // Insert using WordPress database methods
        $result = $wpdb->insert($table, $data);

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to insert customer.', PLUGIN_TEXT_DOMAIN));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a customer data
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
     * Get a customer by ID
     *
     * @param int $id The customer ID
     * @return array|WP_Error The customer data or WP_Error if not found
     */
    public static function get($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Sanitize the ID
        $customer_id = absint($id);
        if (!$customer_id) {
            return new WP_Error('invalid_id', __('Invalid customer ID.', PLUGIN_TEXT_DOMAIN));
        }

        // Get the customer data
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $customer_id
        ), ARRAY_A);

        // Check if the customer exists
        if (empty($customer)) {
            return new WP_Error('not_found', __('Customer not found.', PLUGIN_TEXT_DOMAIN));
        }

        return $customer;
    }

    /**
     * Get all customers.
     *
     * @return array An array of customers, each as an associative array.
     */
    public static function get_all()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Retrieve all accounts from the database
        return $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
    }
}
