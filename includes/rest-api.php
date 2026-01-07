<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {

    register_rest_route('beauty/v1', '/appointments', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return Beauty_Company::is_owner();
        },
        'callback' => function () {
            global $wpdb;
            return $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}beauty_appointments"
            );
        }
    ]);
});
