<?php
if (!defined('ABSPATH')) exit;

Beauty_Permissions::company_only();

global $wpdb;

$current_user = wp_get_current_user();
$company_id   = Beauty_Company::get_company_id();

$per_page = 20;
$paged    = max(1, intval($_GET['paged'] ?? 1));
$offset   = ($paged - 1) * $per_page;

$where = '1=1';
$params = [];

// Filtrar logs apenas dos usuários da empresa
$user_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT ID FROM {$wpdb->users} u
         INNER JOIN {$wpdb->prefix}beauty_professionals p ON p.user_id = u.ID
         WHERE p.company_id = %d",
        $company_id
    )
);
if (!empty($user_ids)) {
    $placeholders = implode(',', array_fill(0, count($user_ids), '%d'));
    $where .= " AND l.user_id IN ($placeholders)";
    $params = array_merge($params, $user_ids);
} else {
    $where .= " AND 1=0"; // Nenhum usuário da empresa
}

// Filtro por ação
if (!empty($_GET['action_filter'])) {
    $where    .= " AND action = %s";
    $params[] = sanitize_text_field($_GET['action_filter']);
}

// Total de registros
$total = $wpdb->get_var(
    $params ? $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}beauty_logs l WHERE $where", ...$params)
            : "SELECT COUNT(*) FROM {$wpdb->prefix}beauty_logs l"
);

// Busca os registros
$logs = $wpdb->get_results(
    $params ? $wpdb->prepare("
        SELECT l.*, u.display_name 
        FROM {$wpdb->prefix}beauty_logs l
        LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id
        WHERE $where
        ORDER BY l.created_at DESC
        LIMIT $per_page OFFSET $offset
    ", ...$params) : "
        SELECT l.*, u.display_name 
        FROM {$wpdb->prefix}beauty_logs l
        LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id
        ORDER BY l.created_at DESC
        LIMIT $per_page OFFSET $offset
    "
);

// Todas as ações para o filtro
$all_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$wpdb->prefix}beauty_logs ORDER BY action ASC");

?>

<div class="beauty-panel">
    <h2>📋 Logs da Empresa</h2>

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="beauty-company-logs" />

        <select name="action_filter">
            <option value="">Todas as ações</option>
            <?php foreach ($all_actions as $action): ?>
                <option value="<?= esc_attr($action) ?>" <?= selected($_GET['action_filter'] ?? '', $action, false) ?>>
                    <?= esc_html(ucwords(str_replace('_', ' ', $action))) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="button">Filtrar</button>
    </form>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Ação</th>
                <th>Usuário</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="4">Nenhum log encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= esc_html(date_i18n('d/m/Y H:i', strtotime($log->created_at))) ?></td>
                        <td><code><?= esc_html($log->action) ?></code></td>
                        <td><?= esc_html($log->display_name ?: 'Usuário removido') ?></td>
                        <td><?= esc_html($log->description) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    $total_pages = ceil($total / $per_page);
    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links([
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '',
            'prev_text' => '«',
            'next_text' => '»',
            'total'     => $total_pages,
            'current'   => $paged
        ]);
        echo '</div></div>';
    }
    ?>
</div>
