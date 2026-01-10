<?php
if (!defined('ABSPATH')) exit;

class Beauty_Automations {

    public function __construct() {

        // CRUD
        add_action('wp_ajax_beauty_get_automations', [$this, 'get']);
        add_action('wp_ajax_beauty_save_automation', [$this, 'save']);
        add_action('wp_ajax_beauty_delete_automation', [$this, 'delete']);

        // Engine
        add_action('beauty_run_automations', [$this, 'run'], 10, 2);
    }

    /**
     * Lista automações
     */
    public function get() {
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, m.slug AS message_slug
                 FROM {$wpdb->prefix}beauty_automations a
                 LEFT JOIN {$wpdb->prefix}beauty_messages m ON m.id = a.message_id
                 WHERE a.company_id = %d
                 ORDER BY a.id DESC",
                $company_id
            )
        );

        wp_send_json_success($rows);
    }

    /**
     * Cria ou atualiza automação
     */
    public function save() {
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();

        $id         = intval($_POST['id'] ?? 0);
        $event      = sanitize_text_field($_POST['event']);
        $message_id = intval($_POST['message_id']);
        $delay_days = intval($_POST['delay_days'] ?? 0);
        $active     = isset($_POST['active']) ? 1 : 0;

        if (!$event || !$message_id) {
            wp_send_json_error('Dados inválidos');
        }

        $data = [
            'company_id' => $company_id,
            'event'      => $event,
            'message_id' => $message_id,
            'delay_days' => $delay_days,
            'active'     => $active
        ];

        if ($id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}beauty_automations",
                $data,
                ['id' => $id, 'company_id' => $company_id]
            );
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}beauty_automations",
                $data
            );
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Remove automação
     */
    public function delete() {
        global $wpdb;

        $company_id = Beauty_Company::get_company_id();
        $id = intval($_POST['id']);

        $wpdb->delete(
            "{$wpdb->prefix}beauty_automations",
            [
                'id' => $id,
                'company_id' => $company_id
            ]
        );

        wp_send_json_success();
    }

    /**
     * Engine de execução das automações
     */
    public function run($event, $context = []) {
        global $wpdb;

        $company_id = $context['company_id'] ?? 0;

        if (!$company_id) return;

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

            // Delay (em dias)
            if ($automation->delay_days > 0) {