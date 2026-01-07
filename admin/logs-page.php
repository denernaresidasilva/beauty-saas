<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$logs = $wpdb->get_results("
    SELECT l.*, u.display_name
    FROM {$wpdb->prefix}beauty_logs l
    LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
    ORDER BY l.created_at DESC
    LIMIT 100
");

// Testar manualmente os cron jobs
if (isset($_GET['test_cron']) && current_user_can('manage_options')) {
    do_action('beauty_cron_run');
    echo '<div class="notice notice-success is-dismissible"><p><strong>Cron executado com sucesso.</strong> Disparos testados manualmente.</p></div>';
}
?>

<div class="wrap">
    <h1>📝 Logs do Sistema</h1>

    <p>
        <a href="?page=beauty_logs&test_cron=1" class="button button-primary">
            🔁 Testar Disparos Manualmente
        </a>
    </p>

    <table class="widefat striped">
        <thead>
            <tr>
                <th style="width:150px;">Data</th>
                <th style="width:200px;">Ação</th>
                <th>Descrição</th>
                <th style="width:200px;">Usuário</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)) : ?>
                <tr><td colspan="4">Nenhum log encontrado.</td></tr>
            <?php else : ?>
                <?php foreach ($logs as $log) : ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></td>
                        <td><code><?= esc_html($log->action) ?></code></td>
                        <td><?= esc_html($log->description) ?></td>
                        <td><?= esc_html($log->display_name ?: 'Sistema') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
