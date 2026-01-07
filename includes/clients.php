<?php
if (!defined('ABSPATH')) exit;

class Beauty_Clients {

    public function __construct() {
        add_action('wp_ajax_beauty_add_client', [$this, 'add']);
    }

    public function add() {
        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;
        $company = Beauty_Company::get_company_id();

        $name  = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);

        $wpdb->insert("{$wpdb->prefix}beauty_clients", [
            'company_id' => $company,
            'name'       => $name,
            'phone'      => $phone,
            'notes'      => ''
        ]);

        $client_id = $wpdb->insert_id;

        // ✅ Log
        beauty_log('cliente_adicionado', "Cliente #$client_id | Nome: $name");

        wp_send_json(['id' => $client_id]);
    }
}
