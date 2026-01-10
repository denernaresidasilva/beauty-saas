<?php
if (!defined('ABSPATH')) exit;

class Beauty_Agenda {

    public function __construct() {

        // AJAX
        add_action('wp_ajax_beauty_get_agenda_day', [$this, 'get_day']);
        add_action('wp_ajax_beauty_create_appointment', [$this, 'create']);
        add_action('wp_ajax_beauty_update_appointment_status', [$this, 'update_status']);
    }

    /**
     * Retorna agenda de um dia específico
     */
    public function get_day() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        if (Beauty_Permissions::is_company()) {
            Beauty_Permissions::company_only();
        } else {
            Beauty_Permissions::professional_only();
        }

        global $wpdb;

        $company_id = Beauty_Company::get_current_company_id();
        $date       = sanitize_text_field($_POST['date']); // Y-m-d
        $professional_id = intval($_POST['professional_id']);

        if (!$company_id) {
            wp_send_json_error('Empresa inválida');
        }

        if (Beauty_Permissions::is_professional()) {
            $professional_id = Beauty_Company::get_professional_id();
        } else {
            if ($professional_id <= 0) {
                wp_send_json_error('Profissional inválido');
            }

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

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    a.*,
                    c.name AS client,
                    s.name AS service,
                    s.duration
                 FROM {$wpdb->prefix}beauty_appointments a
                 LEFT JOIN {$wpdb->prefix}beauty_clients c ON c.id = a.client_id AND c.company_id = a.company_id
                 LEFT JOIN {$wpdb->prefix}beauty_services s ON s.id = a.service_id AND s.company_id = a.company_id
                 WHERE a.company_id = %d
                 AND a.professional_id = %d
                 AND a.start_time BETWEEN %s AND %s
                 ORDER BY a.start_time ASC",
                $company_id,
                $professional_id,
                $start,
                $end
            )
        );

        wp_send_json_success($rows);
    }

    /**
     * Cria um novo agendamento
     */
    public function create() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only();

        global $wpdb;

        $company_id = Beauty_Company::get_company_id();

        $client_id       = intval($_POST['client_id']);
        $professional_id = intval($_POST['professional_id']);
        $service_id      = intval($_POST['service_id']);
        $date            = sanitize_text_field($_POST['date']); // Y-m-d
        $time            = sanitize_text_field($_POST['time']); // H:i
        $status          = sanitize_text_field($_POST['status'] ?? 'confirmado');

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

        if (!$valid_client || !$valid_professional) {
            wp_send_json_error('Dados inválidos para esta empresa');
        }

        // Busca duração do serviço
        $service = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT duration FROM {$wpdb->prefix}beauty_services WHERE id=%d AND company_id = %d",
                $service_id,
                $company_id
            )
        );

        if (!$service) {
            wp_send_json_error('Serviço inválido');
        }

        $start_time = date('Y-m-d H:i:s', strtotime("$date $time"));
        $end_time   = date('Y-m-d H:i:s', strtotime("+{$service->duration} minutes", strtotime($start_time)));

        // Verifica conflito
        $conflict = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_appointments
                 WHERE company_id=%d
                 AND professional_id=%d
                 AND status IN ('confirmado','pendente')
                 AND (
                    (start_time < %s AND end_time > %s)
                 )",
                $company_id,
                $professional_id,
                $end_time,
                $start_time
            )
        );

        if ($conflict > 0) {
            wp_send_json_error('Horário indisponível');
        }

        $wpdb->insert(
            "{$wpdb->prefix}beauty_appointments",
            [
                'company_id'      => $company_id,
                'client_id'       => $client_id,
                'professional_id' => $professional_id,
                'service_id'      => $service_id,
                'start_time'      => $start_time,
                'end_time'        => $end_time,
                'status'          => $status
            ]
        );

        wp_send_json_success([
            'id' => $wpdb->insert_id,
            'start' => $start_time,
            'end' => $end_time
        ]);
    }

    /**
     * Atualiza status do agendamento
     */
    public function update_status() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        if (Beauty_Permissions::is_company()) {
            Beauty_Permissions::company_only();
        } else {
            Beauty_Permissions::professional_only();
        }

        global $wpdb;

        $company_id = Beauty_Company::get_current_company_id();
        $id         = intval($_POST['id']);
        $status     = sanitize_text_field($_POST['status']);
        $professional_id = Beauty_Company::get_professional_id();

        $allowed = ['confirmado','pendente','cancelado','faltou'];

        if (!in_array($status, $allowed)) {
            wp_send_json_error('Status inválido');
        }

        if (!$company_id) {
            wp_send_json_error('Empresa inválida');
        }

        $where = [
            'id' => $id,
            'company_id' => $company_id
        ];

        if (Beauty_Permissions::is_professional()) {
            $where['professional_id'] = $professional_id;
        }

        $wpdb->update(
            "{$wpdb->prefix}beauty_appointments",
            ['status' => $status],
            $where
        );

        wp_send_json_success();
    }
}

new Beauty_Agenda();
