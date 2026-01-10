<?php
if (!defined('ABSPATH')) exit;

class Beauty_Activate {

    public static function run() {

        // Instala / atualiza banco de dados
        if (class_exists('Beauty_DB')) {
            Beauty_DB::maybe_upgrade(BEAUTY_SAAS_VERSION);
        }

        // Cria roles do sistema
        self::create_roles();

        // Cria páginas do app
        self::create_pages();

        // Agenda o cron interno
        self::schedule_cron();
    }

    /**
     * Roles do sistema (idempotente)
     */
    private static function create_roles() {

        // Remove se já existir (evita conflito)
        remove_role('beauty_company');
        remove_role('beauty_professional');

        add_role(
            'beauty_company',
            'Empresa Beauty',
            [
                'read' => true
            ]
        );

        add_role(
            'beauty_professional',
            'Profissional Beauty',
            [
                'read' => true
            ]
        );
    }

    /**
     * Páginas internas do sistema
     */
    private static function create_pages() {

        $pages = [

            [
                'title'   => 'Dashboard',
                'slug'    => 'app-dashboard',
                'content' => '[beauty_dashboard]'
            ],

            [
                'title'   => 'Agenda',
                'slug'    => 'app-agenda',
                'content' => '[beauty_agenda]'
            ],

            [
                'title'   => 'Mensagens',
                'slug'    => 'app-mensagens',
                'content' => '[beauty_messages]'
            ],
          
          [
                'title'   => 'painel',
                'slug'    => 'app-painel',
                'content' => '[beauty_panel]'
            ],

        ];

        foreach ($pages as $page) {

            if (!get_page_by_path($page['slug'])) {

                $page_id = wp_insert_post([
                    'post_title'   => $page['title'],
                    'post_name'    => $page['slug'],
                    'post_content' => $page['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page'
                ]);

                // Marca como página do app (importante para segurança futura)
                if ($page_id && !is_wp_error($page_id)) {
                    update_post_meta($page_id, '_beauty_app_page', 1);
                }
            }
        }
    }

    /**
     * Agenda execução periódica do cron interno
     */
    private static function schedule_cron() {
        add_filter('cron_schedules', [__CLASS__, 'register_schedules']);

        if (!wp_next_scheduled('beauty_cron_run')) {
            wp_schedule_event(time() + 300, 'beauty_five_minutes', 'beauty_cron_run');
        }
    }

    public static function register_schedules($schedules) {
        if (!isset($schedules['beauty_five_minutes'])) {
            $schedules['beauty_five_minutes'] = [
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display'  => __('Beauty - A cada 5 minutos', 'beauty-saas'),
            ];
        }

        return $schedules;
    }
}
