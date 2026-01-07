<?php
if (!defined('ABSPATH')) exit;

class Beauty_Company_Auto {

    public function __construct() {
        add_action('user_register', [$this, 'create_company']);
    }

    public function create_company($user_id) {
        global $wpdb;

        // Evita duplicar
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}beauty_companies WHERE user_id=%d",
                $user_id
            )
        );

        if ($exists) return;

        $user = get_user_by('id', $user_id);

        $wpdb->insert(
            "{$wpdb->prefix}beauty_companies",
            [
                'user_id' => $user_id,
                'name'    => $user->display_name
            ]
        );
    }
}
