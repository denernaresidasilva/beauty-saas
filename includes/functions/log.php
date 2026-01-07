<?php
if (!defined('ABSPATH')) exit;

/**
 * Registra uma ação no log do sistema
 *
 * @param string $action  Nome da ação ex: 'login', 'agendamento_criado'
 * @param string $desc    Detalhes extras (opcional)
 */
function beauty_log($action, $desc = '') {
    global $wpdb;

    $wpdb->insert(
        "{$wpdb->prefix}beauty_logs",
        [
            'user_id'     => get_current_user_id(),
            'action'      => sanitize_text_field($action),
            'description' => sanitize_textarea_field($desc),
        ]
    );
}
