<?php
if (!defined('ABSPATH')) exit;

class Beauty_Cron {

    public function __construct() {
        // Hook manual (botão de teste)
        add_action('beauty_cron_run', [$this, 'run']);
    }

    /**
     * Executa todos os disparos automáticos
     */
    public function run() {
        $this->birthday();
        $this->reminders();
        $this->followups();
    }

    /**
     * 🎂 Disparo de mensagens de aniversário
     */
    private function birthday() {
        global $wpdb;

        $clients = $wpdb->get_results("
            SELECT id, company_id, name, birthday
            FROM {$wpdb->prefix}beauty_clients
            WHERE company_id IS NOT NULL
            AND birthday IS NOT NULL
            AND DAY(birthday) = DAY(CURDATE())
            AND MONTH(birthday) = MONTH(CURDATE())
        ");

        foreach ($clients as $c) {

            // Disparo da mensagem
            beauty_send_message('aniversario', [
                'company_id' => $c->company_id,
                'nome' => $c->name,
                'data' => date('d/m')
            ]);

            // Log
            beauty_log(
                'aniversario_enviado',
                "Mensagem de aniversário enviada para {$c->name} (Cliente ID {$c->id})"
            );
        }
    }

    /**
     * ⏰ Lembrete 1 hora antes do agendamento
     */
    private function reminders() {
        global $wpdb;

        $rows = $wpdb->get_results("
            SELECT 
                a.id,
                a.company_id,
                a.start_time,
                c.name AS client_name,
                s.name AS service_name
            FROM {$wpdb->prefix}beauty_appointments a
            INNER JOIN {$wpdb->prefix}beauty_clients c ON a.client_id = c.id AND c.company_id = a.company_id
            INNER JOIN {$wpdb->prefix}beauty_services s ON a.service_id = s.id AND s.company_id = a.company_id
            WHERE a.start_time BETWEEN DATE_ADD(NOW(), INTERVAL 55 MINUTE)
            AND DATE_ADD(NOW(), INTERVAL 65 MINUTE)
            AND a.reminder_sent = 0
            AND a.status = 'confirmado'
            AND a.company_id IS NOT NULL
        ");

        foreach ($rows as $a) {

            beauty_send_message('lembrete_agendamento', [
                'company_id' => $a->company_id,
                'nome'    => $a->client_name,
                'servico' => $a->service_name,
                'data'    => date('d/m/Y H:i', strtotime($a->start_time))
            ]);

            // Marca como enviado
            $wpdb->update(
                "{$wpdb->prefix}beauty_appointments",
                ['reminder_sent' => 1],
                ['id' => $a->id, 'company_id' => $a->company_id]
            );

            // Log
            beauty_log(
                'lembrete_enviado',
                "Lembrete enviado para {$a->client_name} | Agendamento #{$a->id}"
            );
        }
    }

    /**
     * 🔁 Follow‑up após atendimento finalizado
     */
    private function followups() {
        global $wpdb;

        $rows = $wpdb->get_results("
            SELECT 
                a.id,
                a.company_id,
                a.start_time,
                a.end_time,
                c.name AS client_name,
                s.name AS service_name,
                s.followup_delay_value,
                s.followup_delay_unit
            FROM {$wpdb->prefix}beauty_appointments a
            INNER JOIN {$wpdb->prefix}beauty_clients c ON a.client_id = c.id AND c.company_id = a.company_id
            INNER JOIN {$wpdb->prefix}beauty_services s ON a.service_id = s.id AND s.company_id = a.company_id
            WHERE a.status = 'finalizado'
            AND s.followup_enabled = 1
            AND a.followup_sent = 0
            AND a.company_id IS NOT NULL
        ");

        foreach ($rows as $r) {

            // Calcula quando o follow‑up deve ser enviado
            $delay     = "+{$r->followup_delay_value} {$r->followup_delay_unit}";
            $send_time = date('Y-m-d H:i:s', strtotime($r->end_time . ' ' . $delay));

            if ($send_time <= current_time('mysql')) {

                beauty_send_message('followup', [
                    'company_id' => $r->company_id,
                    'nome'    => $r->client_name,
                    'servico' => $r->service_name,
                    'data'    => date('d/m/Y', strtotime($r->start_time))
                ]);

                // Marca como enviado
                $wpdb->update(
                    "{$wpdb->prefix}beauty_appointments",
                    ['followup_sent' => 1],
                    ['id' => $r->id, 'company_id' => $r->company_id]
                );

                // Log
                beauty_log(
                    'followup_enviado',
                    "Follow-up enviado para {$r->client_name} | Agendamento #{$r->id}"
                );
            }
        }
    }
}

new Beauty_Cron();
