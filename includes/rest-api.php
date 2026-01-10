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
            $company_id = Beauty_Company::get_company_id();
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}beauty_appointments WHERE company_id = %d",
                    $company_id
                )
            );
        }
    ]);
});
