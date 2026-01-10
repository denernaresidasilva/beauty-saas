<?php
if (!defined('ABSPATH')) exit;

class Beauty_Automations {

    public function __construct() {
        // Engine
        add_action('beauty_run_automations', [$this, 'run'], 10, 2);
    }

    /**
     * Engine de execução das automações
     */
    public function run($event, $context = []) {
        global $wpdb;

        $company_id = $context['company_id'] ?? 0;

        if (!$company_id) {
            return;
        }

        $automations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_automations
                 WHERE company_id=%d
                 AND event=%s
                 AND active=1",
                $company_id,
                $event
            )
        );

        foreach ($automations as $automation) {
            $payload = $context;
            $payload['company_id'] = $company_id;

            // Delay (em dias)
            if ($automation->delay_days > 0) {
                $send_at = date(
                    'Y-m-d H:i:s',
                    strtotime("+{$automation->delay_days} days", current_time('timestamp'))
                );

                $wpdb->insert(
                    "{$wpdb->prefix}beauty_automation_queue",
                    [
                        'company_id'    => $company_id,
                        'automation_id' => $automation->id,
                        'message_id'    => $automation->message_id,
                        'payload'       => wp_json_encode($payload),
                        'send_at'       => $send_at,
                    ]
                );

                continue;
            }

            do_action('beauty_send_message', $automation->message_id, $payload);
        }
    }
}

new Beauty_Automations();
