<?php

/**
 * Handles customer data management
 */
class SBS_Customer
{
    /**
     * Create new customer with validation
     * @param array $data Sanitized customer data
     * @return int|WP_Error Inserted customer ID or error
     */
    public static function create($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . SBS_TABLE_PREFIX . 'customers';

        // Validate required fields
        if (
            empty($data['cif_number']) || empty($data['full_name']) || empty($data['address'])
            || empty($data['email']) || empty($data['date_of_birth'])
        ) {
            return new WP_Error('missing_fields', __('Required fields are missing', 'simple-bank-system'));
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
            return new WP_Error('db_error', __('Database insertion failed', 'simple-bank-system'));
        }

        return $wpdb->insert_id;
    }

    // Additional methods (update, delete, get) follow similar pattern
}
