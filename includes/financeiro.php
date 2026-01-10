<?php
if (!defined('ABSPATH')) exit;

class Beauty_Financeiro {

    public function __construct() {
        add_action('wp_ajax_beauty_get_financial', [$this, 'list']);
    }

    public function list() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only();

        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_financial WHERE company_id=%d",
                Beauty_Company::get_company_id()
            )
        );

        wp_send_json($rows);
    }
}
