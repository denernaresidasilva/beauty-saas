<?php
if (!defined('ABSPATH')) exit;

class Beauty_Professionals {

    public function __construct() {
        add_action('wp_ajax_beauty_get_professionals', [$this, 'get_professionals']);
        add_action('wp_ajax_beauty_save_professional', [$this, 'save_professional']);
        add_action('wp_ajax_beauty_delete_professional', [$this, 'delete_professional']);
    }

    public function get_professionals() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $professionals = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_professionals
                 WHERE company_id = %d
                 ORDER BY name ASC",
                $company_id
            )
        );

        wp_send_json_success($professionals);
    }

    public function save_professional() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $id         = intval($_POST['id'] ?? 0);
        $name       = sanitize_text_field($_POST['name']);
        $phone      = sanitize_text_field($_POST['phone']);
        $commission = floatval($_POST['commission']);
        $active     = isset($_POST['active']) ? 1 : 0;

        if (!$name) {
            wp_send_json_error('Nome obrigatório');
        }

        $data = [
            'company_id' => $company_id,
            'name'       => $name,
            'phone'      => $phone,
            'commission' => $commission,
            'active'     => $active
        ];

        if ($id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}beauty_professionals",
                $data,
                ['id' => $id, 'company_id' => $company_id]
            );

            // ✅ Log
            beauty_log('profissional_editado', "Profissional #$id | Nome: $name");
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}beauty_professionals",
                $data
            );
            $id = $wpdb->insert_id;

            // ✅ Log
            beauty_log('profissional_criado', "Profissional #$id | Nome: $name");
        }

        wp_send_json_success(['id' => $id]);
    }

    public function delete_professional() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();
        $id = intval($_POST['id']);

        if ($id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $wpdb->delete(
            "{$wpdb->prefix}beauty_professionals",
            [
                'id' => $id,
                'company_id' => $company_id
            ]
        );

        // ✅ Log
        beauty_log('profissional_excluido', "Profissional #$id removido");

        wp_send_json_success();
    }
}

new Beauty_Professionals();
