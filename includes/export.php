<?php
if (!defined('ABSPATH')) exit;

class Beauty_Export {

    public function __construct() {
        add_action('admin_post_beauty_export_appointments', [$this, 'appointments']);
    }

    public function appointments() {
        Beauty_Permissions::company_only();

        global $wpdb;

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="agendamentos.csv"');

        $fp = fopen('php://output', 'w');

        fputcsv($fp, ['Cliente', 'Serviço', 'Profissional', 'Data', 'Status']);

        $rows = $wpdb->get_results("
            SELECT c.name, s.name, p.name, a.start_time, a.status
            FROM {$wpdb->prefix}beauty_appointments a
            JOIN {$wpdb->prefix}beauty_clients c ON c.id=a.client_id
            JOIN {$wpdb->prefix}beauty_services s ON s.id=a.service_id
            JOIN {$wpdb->prefix}beauty_professionals p ON p.id=a.professional_id
        ", ARRAY_A);

        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);
        exit;
    }
}

new Beauty_Export();
