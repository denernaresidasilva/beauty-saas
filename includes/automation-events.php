<?php
if (!defined('ABSPATH')) exit;

class Beauty_AutomationEvents {

    public function __construct() {
        add_action('wp_ajax_beauty_get_automations', [$this, 'get']);
        add_action('wp_ajax_beauty_save_automation', [$this, 'save']);
        add_action('wp_ajax_beauty_delete_automation', [$this, 'delete']);
    }

    /**
     * Lista automações da empresa
     */
    public function get() {
        check_ajax_referer('beauty_nonce', 'nonce');
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $automations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, m.title
                 FROM {$wpdb->prefix}beauty_automations a
                 LEFT JOIN {$wpdb->prefix}beauty_messages m
                    ON a.message_id = m.id
                 WHERE a.company_id = %d
                 ORDER BY a.id DESC",
                $company_id
            )
        );

        wp_send_json_success($automations);
    }

    /**
     * Cria ou edita automação
     */
    public function save() {
        check_ajax_referer('beauty_nonce', 'nonce');
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $id         = intval($_POST['id'] ?? 0);
        $event      = sanitize_text_field($_POST['event']);
        $message_id = intval($_POST['message_id']);
        $delay_days = intval($_POST['delay_days']);
        $active     = isset($_POST['active']) ? 1 : 0;

        if (!$event || !$message_id) {
            wp_send_json_error('Dados inválidos');
        }

        $data = [
            'company_id'     => $company_id,
            'event'          => $event,
            'message_id'     => $message_id,
            'delay_days'     => $delay_days,
            'active'         => $active
        ];

        if ($id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}beauty_automations",
                $data,
                ['id' => $id, 'company_id' => $company_id]
            );

            // ✅ Log
            beauty_log('automacao_editada', "Automação #$id | Evento: $event");
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}beauty_automations",
                $data
            );
            $id = $wpdb->insert_id;

            // ✅ Log
            beauty_log('automacao_criada', "Automação #$id | Evento: $event");
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Exclui automação
     */
    public function delete() {
        check_ajax_referer('beauty_nonce', 'nonce');
        Beauty_Permissions::company_only();

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();
        $id = intval($_POST['id']);

        if ($id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $wpdb->delete(
            "{$wpdb->prefix}beauty_automations",
            ['id' => $id, 'company_id' => $company_id]
        );

        // ✅ Log
        beauty_log('automacao_excluida', "Automação #$id removida");

        wp_send_json_success();
    }
}

new Beauty_AutomationEvents();
