<?php
if (!defined('ABSPATH')) exit;

class Beauty_Logs {

    public static function add($action, $description = '') {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}beauty_logs",
            [
                'user_id' => get_current_user_id(),
                'action' => sanitize_text_field($action),
                'description' => sanitize_textarea_field($description),
            ]
        );
    }
}
