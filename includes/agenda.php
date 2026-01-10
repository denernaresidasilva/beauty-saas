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
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();
        $date       = sanitize_text_field($_POST['date']); // Y-m-d
        $professional_id = intval($_POST['professional_id']);

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
                 LEFT JOIN {$wpdb->prefix}beauty_clients c ON c.id = a.client_id
                 LEFT JOIN {$wpdb->prefix}beauty_services s ON s.id = a.service_id
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
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();

        $client_id       = intval($_POST['client_id']);
        $professional_id = intval($_POST['professional_id']);
        $service_id      = intval($_POST['service_id']);
        $date            = sanitize_text_field($_POST['date']); // Y-m-d
        $time            = sanitize_text_field($_POST['time']); // H:i
        $status          = sanitize_text_field($_POST['status'] ?? 'confirmado');

        // Busca duração do serviço
        $service = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT duration FROM {$wpdb->prefix}beauty_services WHERE id=%d",
                $service_id
            )
        );

        if (!$service) {
            wp_send_json_error('Serviço inválido');
        }

        $start_time = date('Y-m-d H:i:s', strtotime("$date $time"));
        $end_time   = date('Y-m-d H:i:s', strtotime("+{$service->duration} minutes", strtotime($start_time)));

        $conflict_statuses = apply_filters(
            'beauty_appointment_conflict_statuses',
            ['confirmado', 'pendente'],
            $company_id,
            $professional_id
        );

        if (!is_array($conflict_statuses)) {
            $conflict_statuses = ['confirmado', 'pendente'];
        }

        $conflict_statuses = array_values(array_filter(array_map('sanitize_text_field', $conflict_statuses)));

        // Verifica conflito
        $conflict = 0;
        if (!empty($conflict_statuses)) {
            $placeholders = implode(',', array_fill(0, count($conflict_statuses), '%s'));
            $conflict_sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_appointments
                 WHERE company_id=%d
                 AND professional_id=%d
                 AND status IN ($placeholders)
                 AND (start_time < %s AND end_time > %s)",
                array_merge(
                    [$company_id, $professional_id],
                    $conflict_statuses,
                    [$end_time, $start_time]
                )
            );
            $conflict = $wpdb->get_var($conflict_sql);
        }

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
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();
        $id         = intval($_POST['id']);
        $status     = sanitize_text_field($_POST['status']);

        $allowed = ['confirmado','pendente','cancelado','faltou'];

        if (!in_array($status, $allowed)) {
            wp_send_json_error('Status inválido');
        }

        $wpdb->update(
            "{$wpdb->prefix}beauty_appointments",
            ['status' => $status],
            [
                'id' => $id,
                'company_id' => $company_id
            ]
        );

        wp_send_json_success();
    }
}

new Beauty_Agenda();
