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

        // Validate required fields
        if (
            empty($data['cif_number']) || empty($data['full_name']) || empty($data['address'])
            || empty($data['email']) || empty($data['date_of_birth'])
        ) {
            return new WP_Error('missing_fields', __('Required fields are missing', 'simple-bank-system'));
        }

        // Check for duplicate email
        $existing_email = $wpdb->get_var($wpdb->prepare(
            "SELECT email FROM $table WHERE email = %s",
            $data['email']
        ));

        if ($existing_email) {
            return new WP_Error('duplicate_email', __('A customer with email \'' . $data['email'] . '\' already exists.', 'simple-bank-system'));
        }

        // Check for duplicate CIF number
        $existing_cif = $wpdb->get_var($wpdb->prepare(
            "SELECT cif_number FROM $table WHERE cif_number = %s",
            $data['cif_number']
        ));

        if ($existing_cif) {
            return new WP_Error('duplicate_cif', __('A customer with CIF number \'' . $data['cif_number'] . '\' already exists.', 'simple-bank-system'));
        }

        // Prepare sanitized data
        $customer_data = array(
            'cif_number'    => sanitize_text_field($data['cif_number']),
            'full_name'     => sanitize_text_field($data['full_name']),
            'address'       => sanitize_textarea_field($data['address']),
            'email'         => sanitize_email($data['email']),
            'date_of_birth' => sanitize_text_field($data['date_of_birth']),
        );

        // Insert using WordPress database methods
        $result = $wpdb->insert($table, $customer_data);

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to insert customer.', 'simple-bank-system'));
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

        // Validate required fields
        if (
            empty($id) || empty($data['cif_number']) || empty($data['full_name']) || empty($data['address'])
            || empty($data['email']) || empty($data['date_of_birth'])
        ) {
            return new WP_Error('missing_fields', __('Required fields are missing', 'simple-bank-system'));
        }

        // Update the customer record
        $result = $wpdb->update(
            $table,
            $data, // Data to update (associative array)
            array('id' => $id), // WHERE clause
            array('%s', '%s', '%s', '%s'), // Data format
            array('%d') // WHERE format
        );

        if (false === $result) {
            return new WP_Error('db_error', __('Failed to update customer.', 'simple-bank-system'));
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
            return new WP_Error('db_error', __('Failed to delete customer.', 'simple-bank-system'));
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
        if (!$id) {
            return new WP_Error('invalid_id', __('Invalid customer ID.', 'simple-bank-system'));
        }

        // Get the customer data
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $customer_id
        ), ARRAY_A);

        // Check if the customer exists
        if (empty($customer)) {
            return new WP_Error('not_found', __('Customer not found.', 'simple-bank-system'));
        }

        return $customer;
    }
}
