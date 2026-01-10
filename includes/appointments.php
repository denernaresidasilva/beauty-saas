<?php
if (!defined('ABSPATH')) exit;

class Beauty_Appointments {

    public function __construct() {
        add_action('wp_ajax_beauty_create_appointment', [$this, 'create']);
        add_action('wp_ajax_beauty_cancel_appointment', [$this, 'cancel']);
        add_action('wp_ajax_beauty_get_appointments', [$this, 'list']);
    }

    /**
     * Criação de novo agendamento
     */
    public function create() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only(); // 🔒 Apenas empresa

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $client_id       = intval($_POST['client_id']);
        $professional_id = intval($_POST['professional_id']);
        $service_id      = intval($_POST['service_id']);
        $start_time      = sanitize_text_field($_POST['start_time']);
        $end_time        = sanitize_text_field($_POST['end_time']);

        if (!$client_id || !$professional_id || !$service_id || !$start_time || !$end_time) {
            wp_send_json_error('Dados incompletos');
        }

        $valid_client = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_clients WHERE id = %d AND company_id = %d",
                $client_id,
                $company_id
            )
        );

        $valid_professional = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_professionals WHERE id = %d AND company_id = %d",
                $professional_id,
                $company_id
            )
        );

        $valid_service = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_services WHERE id = %d AND company_id = %d",
                $service_id,
                $company_id
            )
        );

        if (!$valid_client || !$valid_professional || !$valid_service) {
            wp_send_json_error('Dados inválidos para esta empresa');
        }

        // Cria o agendamento já como confirmado
        $wpdb->insert(
            "{$wpdb->prefix}beauty_appointments",
            [
                'company_id'      => $company_id,
                'client_id'       => $client_id,
                'professional_id' => $professional_id,
                'service_id'      => $service_id,
                'start_time'      => $start_time,
                'end_time'        => $end_time,
                'status'          => 'confirmado',
                'reminder_sent'   => 0,
                'followup_sent'   => 0
            ]
        );

        $appointment_id = $wpdb->insert_id;

        /**
         * 🔎 Busca dados para mensagem
         */
        $client_name = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}beauty_clients WHERE id = %d AND company_id = %d",
                $client_id,
                $company_id
            )
        );

        $service_name = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}beauty_services WHERE id = %d AND company_id = %d",
                $service_id,
                $company_id
            )
        );

        /**
         * 📩 DISPARO: confirmação de agendamento
         */
        beauty_send_message('confirmacao_agendamento', [
            'nome'    => $client_name,
            'servico' => $service_name,
            'data'    => date('d/m/Y H:i', strtotime($start_time))
        ]);

        /**
         * 📝 LOG
         */
        beauty_log(
            'agendamento_confirmado',
            "Agendamento #{$appointment_id} confirmado | Cliente: {$client_name} | Serviço: {$service_name}"
        );

        wp_send_json_success([
            'id' => $appointment_id
        ]);
    }

    /**
     * Cancelar agendamento
     */
    public function cancel() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only(); // 🔒 Apenas empresa

        global $wpdb;
        $appointment_id = intval($_POST['id']);
        $company_id     = Beauty_Company::get_company_id();

        if ($appointment_id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $wpdb->update(
            "{$wpdb->prefix}beauty_appointments",
            ['status' => 'cancelado'],
            ['id' => $appointment_id, 'company_id' => $company_id]
        );

        /**
         * 📝 LOG
         */
        beauty_log(
            'agendamento_cancelado',
            "Agendamento #{$appointment_id} cancelado"
        );

        wp_send_json_success();
    }

    /**
     * Lista agendamentos (empresa ou profissional)
     */
    public function list() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        if (Beauty_Permissions::is_company()) {
            Beauty_Permissions::company_only();
        } else {
            Beauty_Permissions::professional_only();
        }

        global $wpdb;

        $company_id      = Beauty_Company::get_current_company_id();
        $professional_id = intval($_POST['professional_id'] ?? 0);

        if (!$company_id) {
            wp_send_json_error('Empresa inválida');
        }

        if (Beauty_Permissions::is_professional()) {
            $professional_id = Beauty_Company::get_professional_id();
        } elseif ($professional_id > 0) {
            $valid_professional = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_professionals WHERE id = %d AND company_id = %d",
                    $professional_id,
                    $company_id
                )
            );

            if (!$valid_professional) {
                wp_send_json_error('Profissional inválido');
            }
        }

        $query = "
            SELECT 
                a.*,
                c.name AS client_name,
                s.name AS service_name
            FROM {$wpdb->prefix}beauty_appointments a
            LEFT JOIN {$wpdb->prefix}beauty_clients c ON a.client_id = c.id AND c.company_id = a.company_id
            LEFT JOIN {$wpdb->prefix}beauty_services s ON a.service_id = s.id AND s.company_id = a.company_id
            WHERE a.company_id = %d
        ";

        $params = [$company_id];

        if ($professional_id > 0) {
            $query .= " AND a.professional_id = %d";
            $params[] = $professional_id;
        }

        $query .= " ORDER BY a.start_time ASC";

        $appointments = $wpdb->get_results(
            $wpdb->prepare($query, ...$params)
        );

        wp_send_json_success($appointments);
    }
}

new Beauty_Appointments();
