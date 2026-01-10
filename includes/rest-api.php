<?php
if (!defined('ABSPATH')) exit;

function beauty_financial_company_id() {
    $company_id = Beauty_Company::get_company_id();

    if ($company_id) {
        return $company_id;
    }

    if (Beauty_Permissions::is_accountant()) {
        return (int) get_user_meta(get_current_user_id(), 'beauty_company_id', true);
    }

    return 0;
}

add_action('rest_api_init', function () {

    register_rest_route('beauty/v1', '/appointments', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return Beauty_Company::is_owner();
        },
        'callback' => function () {
            global $wpdb;
            return $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}beauty_appointments"
            );
        }
    ]);

    register_rest_route('beauty/v1', '/reports/dre', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return Beauty_Permissions::is_company() || Beauty_Permissions::is_accountant();
        },
        'callback' => function (WP_REST_Request $request) {
            global $wpdb;

            $company_id = beauty_financial_company_id();
            if (!$company_id) {
                return new WP_Error('forbidden', 'Permissão insuficiente.', ['status' => 403]);
            }

            $start = sanitize_text_field($request->get_param('start'));
            $end = sanitize_text_field($request->get_param('end'));

            $where = "WHERE l.company_id = %d";
            $params = [$company_id];

            if ($start) {
                $where .= " AND l.entry_date >= %s";
                $params[] = $start;
            }

            if ($end) {
                $where .= " AND l.entry_date <= %s";
                $params[] = $end;
            }

            $sql = "
                SELECT c.name, l.entry_type, SUM(l.amount) as total
                FROM {$wpdb->prefix}beauty_financial_ledger l
                LEFT JOIN {$wpdb->prefix}beauty_financial_categories c ON c.id = l.category_id
                $where
                GROUP BY c.name, l.entry_type
            ";

            $rows = $wpdb->get_results($wpdb->prepare($sql, ...$params));

            $totals = [
                'receitas' => 0,
                'despesas' => 0,
                'resultado' => 0,
            ];

            foreach ($rows as $row) {
                if ($row->entry_type === 'receita') {
                    $totals['receitas'] += (float) $row->total;
                } else {
                    $totals['despesas'] += (float) $row->total;
                }
            }

            $totals['resultado'] = $totals['receitas'] - $totals['despesas'];

            return [
                'detalhes' => $rows,
                'totais' => $totals,
            ];
        }
    ]);

    register_rest_route('beauty/v1', '/reports/fluxo-caixa', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return Beauty_Permissions::is_company() || Beauty_Permissions::is_accountant();
        },
        'callback' => function (WP_REST_Request $request) {
            global $wpdb;

            $company_id = beauty_financial_company_id();
            if (!$company_id) {
                return new WP_Error('forbidden', 'Permissão insuficiente.', ['status' => 403]);
            }

            $start = sanitize_text_field($request->get_param('start'));
            $end = sanitize_text_field($request->get_param('end'));

            $where = "WHERE l.company_id = %d";
            $params = [$company_id];

            if ($start) {
                $where .= " AND l.entry_date >= %s";
                $params[] = $start;
            }

            if ($end) {
                $where .= " AND l.entry_date <= %s";
                $params[] = $end;
            }

            $sql = "
                SELECT l.entry_date,
                    SUM(CASE WHEN l.entry_type = 'receita' THEN l.amount ELSE 0 END) as entradas,
                    SUM(CASE WHEN l.entry_type = 'despesa' THEN l.amount ELSE 0 END) as saidas
                FROM {$wpdb->prefix}beauty_financial_ledger l
                $where
                GROUP BY l.entry_date
                ORDER BY l.entry_date ASC
            ";

            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        }
    ]);

    register_rest_route('beauty/v1', '/reports/comissoes', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return Beauty_Permissions::is_company() || Beauty_Permissions::is_accountant();
        },
        'callback' => function (WP_REST_Request $request) {
            global $wpdb;

            $company_id = beauty_financial_company_id();
            if (!$company_id) {
                return new WP_Error('forbidden', 'Permissão insuficiente.', ['status' => 403]);
            }

            $start = sanitize_text_field($request->get_param('start'));
            $end = sanitize_text_field($request->get_param('end'));

            $where = "WHERE f.company_id = %d";
            $params = [$company_id];

            if ($start) {
                $where .= " AND DATE(f.created_at) >= %s";
                $params[] = $start;
            }

            if ($end) {
                $where .= " AND DATE(f.created_at) <= %s";
                $params[] = $end;
            }

            $sql = "
                SELECT p.id, p.name, p.commission,
                    SUM(f.amount) as total_vendas,
                    SUM(f.amount * (p.commission / 100)) as total_comissao
                FROM {$wpdb->prefix}beauty_financial f
                INNER JOIN {$wpdb->prefix}beauty_professionals p ON p.id = f.professional_id
                $where
                GROUP BY p.id, p.name, p.commission
                ORDER BY p.name ASC
            ";

            return $wpdb->get_results($wpdb->prepare($sql, ...$params));
        }
    ]);
});
