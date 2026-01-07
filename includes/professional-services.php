<?php
if (!defined('ABSPATH')) exit;

class Beauty_ProfessionalServices {

    public function __construct() {
        add_action('wp_ajax_beauty_get_professional_services', [$this, 'get']);
        add_action('wp_ajax_beauty_toggle_professional_service', [$this, 'toggle']);
    }

    /**
     * Lista serviços vinculados a um profissional
     */
    public function get() {
        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;

        $professional_id = intval($_POST['professional_id']);
        if ($professional_id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $services = $wpdb->get_col($wpdb->prepare(
            "SELECT service_id
             FROM {$wpdb->prefix}beauty_professional_services
             WHERE professional_id = %d",
            $professional_id
        ));

        wp_send_json_success($services);
    }

    /**
     * Vincula ou remove vínculo entre profissional e serviço
     */
    public function toggle() {
        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

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

new Beauty_ProfessionalServices();
