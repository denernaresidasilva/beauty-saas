<?php
if (!defined('ABSPATH')) exit;

class Beauty_Finalize {

    public function __construct() {
        add_action('wp_ajax_beauty_finalize_appointment', [$this, 'run']);
    }

    /**
     * Finaliza o agendamento e gera registro financeiro
     */
    public function run() {
        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;

        $appointment_id = intval($_POST['appointment_id']);
        $amount         = floatval($_POST['amount']);
        $method         = sanitize_text_field($_POST['payment_method'] ?? 'Dinheiro');
        $company_id     = Beauty_Company::get_company_id();

        if ($appointment_id <= 0 || $amount <= 0) {
            wp_send_json_error('Dados inválidos');
        }

        // Pega o agendamento
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}beauty_appointments
             WHERE id = %d AND company_id = %d",
            $appointment_id,
            $company_id
        ));

        if (!$appointment) {
            wp_send_json_error('Agendamento não encontrado');
        }

        // Atualiza status do agendamento
        $wpdb->update(
            "{$wpdb->prefix}beauty_appointments",
            ['status' => 'finalizado'],
            ['id' => $appointment_id]
        );

        // Cria entrada no financeiro
        $wpdb->insert(
            "{$wpdb->prefix}beauty_financial",
            [
                'company_id'      => $company_id,
                'appointment_id'  => $appointment_id,
                'professional_id' => $appointment->professional_id,
                'amount'          => $amount,
                'payment_method'  => $method
            ]
        );

        // ✅ Log
        beauty_log('agendamento_finalizado', "Agendamento #$appointment_id | Valor R$ $amount");

        wp_send_json_success();
    }
}

new Beauty_Finalize();
