<?php

/**
 * Handles admin notifications display
 */
class SBS_Admin_Notice
{
    const SESSION_KEY = 'sbs_admin_notices';

    /**
     * Add new notice to be displayed
     */
    public static function add($type, $message)
    {
        $notices = get_transient(self::SESSION_KEY) ?: array();
        $notices[] = array(
            'type' => sanitize_key($type),
            'message' => wp_kses_post($message)
        );
        set_transient(self::SESSION_KEY, $notices, 30);
    }

    /**
     * Display stored notices
     */
    public static function display()
    {
        $notices = get_transient(self::SESSION_KEY);
        if (empty($notices)) return;

        foreach ($notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                $notice['message']
            );
        }
        delete_transient(self::SESSION_KEY);
    }
}
