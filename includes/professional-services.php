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
        $company_id = Beauty_Company::get_company_id();

        $professional_id = intval($_POST['professional_id']);
        if ($professional_id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $services = $wpdb->get_col($wpdb->prepare(
            "SELECT ps.service_id
             FROM {$wpdb->prefix}beauty_professional_services ps
             INNER JOIN {$wpdb->prefix}beauty_professionals p ON p.id = ps.professional_id
             INNER JOIN {$wpdb->prefix}beauty_services s ON s.id = ps.service_id
             WHERE ps.professional_id = %d
             AND p.company_id = %d
             AND s.company_id = %d",
            $professional_id,
            $company_id,
            $company_id
        ));

        wp_send_json_success($services);
    }

    /**
     * Vincula ou remove vínculo entre profissional e serviço
     */
    public function toggle() {
        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $professional_id = intval($_POST['professional_id']);
        $service_id      = intval($_POST['service_id']);
        $checked         = intval($_POST['checked']);

        if ($professional_id <= 0 || $service_id <= 0) {
            wp_send_json_error('Dados inválidos');
        }

        $valid_link = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$wpdb->prefix}beauty_professionals p
                 INNER JOIN {$wpdb->prefix}beauty_services s ON s.id = %d
                 WHERE p.id = %d
                 AND p.company_id = %d
                 AND s.company_id = %d",
                $service_id,
                $professional_id,
                $company_id,
                $company_id
            )
        );

        if (!$valid_link) {
            wp_send_json_error('Profissional ou serviço inválido');
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
