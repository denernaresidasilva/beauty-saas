<?php
if (!defined('ABSPATH')) exit;

class Beauty_Services {

    public function __construct() {
        add_action('wp_ajax_beauty_get_services', [$this, 'get_services']);
        add_action('wp_ajax_beauty_save_service', [$this, 'save_service']);
        add_action('wp_ajax_beauty_delete_service', [$this, 'delete_service']);
        add_action('wp_ajax_beauty_assign_professional', [$this, 'assign_professional']);
    }

    /**
     * Lista serviços da empresa
     */
    public function get_services() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $services = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_services
                 WHERE company_id = %d
                 ORDER BY name ASC",
                $company_id
            )
        );

        wp_send_json_success($services);
    }

    /**
     * Cria ou atualiza serviço
     */
    public function save_service() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $id       = intval($_POST['id'] ?? 0);
        $name     = sanitize_text_field($_POST['name']);
        $duration = intval($_POST['duration']);
        $price    = floatval($_POST['price']);
        $active   = isset($_POST['active']) ? 1 : 0;

        if (!$name || $duration <= 0) {
            wp_send_json_error('Dados inválidos');
        }

        $data = [
            'company_id' => $company_id,
            'name'       => $name,
            'duration'   => $duration,
            'price'      => $price,
            'active'     => $active
        ];

        if ($id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}beauty_services",
                $data,
                ['id' => $id, 'company_id' => $company_id]
            );

            // ✅ Log
            beauty_log('servico_editado', "Serviço #$id | Nome: $name");
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}beauty_services",
                $data
            );
            $id = $wpdb->insert_id;

            // ✅ Log
            beauty_log('servico_criado', "Serviço #$id | Nome: $name");
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Remove serviço
     */
    public function delete_service() {
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();
        $id = intval($_POST['id']);

        if ($id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $wpdb->delete(
            "{$wpdb->prefix}beauty_professional_services",
            ['service_id' => $id]
        );

        $wpdb->delete(
            "{$wpdb->prefix}beauty_services",
            [
                'id' => $id,
                'company_id' => $company_id
            ]
        );

        // ✅ Log
        beauty_log('servico_excluido', "Serviço #$id removido");

        wp_send_json_success();
    }

    /**
     * Vínculo profissional x serviço
     */
    public function assign_professional() {
        Beauty_Permissions::company_only();

        global $wpdb;

        $professional_id = intval($_POST['professional_id']);
        $service_id      = intval($_POST['service_id']);
        $checked         = intval($_POST['checked']);

        if ($professional_id <= 0 || $service_id <= 0) {
            wp_send_json_error('Dados inválidos');
        }

        if ($checked) {
            $wpdb->replace(
                "{$wpdb->prefix}beauty_professional_services",
                [
                    'professional_id' => $professional_id,
                    'service_id'      => $service_id
                ]
            );
        } else {
            $wpdb->delete(
                "{$wpdb->prefix}beauty_professional_services",
                [
                    'professional_id' => $professional_id,
                    'service_id'      => $service_id
                ]
            );
        }

        wp_send_json_success();
    }
}

new Beauty_Services();
