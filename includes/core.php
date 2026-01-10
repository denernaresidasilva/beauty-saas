<?php
if (!defined('ABSPATH')) exit;

class Beauty_Core {

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'register_admin_pages']);
    }

    public function init() {
        $this->load_dependencies();

        // Registra widgets Elementor apenas se Elementor estiver ativo
        if (did_action('elementor/loaded')) {
            require_once BEAUTY_SAAS_PATH . 'elementor/elementor.php';
        }
    }

    private function load_dependencies() {
        $base = BEAUTY_SAAS_PATH . 'includes/';

        require_once $base . 'db.php';
        require_once $base . 'company.php';
        require_once $base . 'security.php';
        require_once $base . 'security-permissions.php';
        require_once $base . 'auth.php';
        require_once $base . 'clients.php';
        require_once $base . 'services.php';
        require_once $base . 'products.php';
        require_once $base . 'beauty-professionals.php';
        require_once $base . 'professional-services.php';
        require_once $base . 'appointments.php';
        require_once $base . 'finalize.php';
        require_once $base . 'messages.php';
        require_once $base . 'messages-api.php';
        require_once $base . 'automation-events.php';
        require_once $base . 'cron.php';
        require_once $base . 'logs.php';
        require_once $base . 'export.php';
        require_once $base . 'rest-api.php';
    }

    public function enqueue_assets() {
        // Disponibiliza URL do AJAX para scripts
        wp_localize_script('jquery', 'beautyAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('beauty_nonce'),
        ]);

        // Carrega apenas no admin ou no modo de visualização do Elementor
        if (
            is_admin() ||
            (defined('ELEMENTOR_VERSION') && \Elementor\Plugin::instance()->preview->is_preview())
        ) {
            // Scripts
            wp_register_script(
                'beauty-messages-editor',
                BEAUTY_SAAS_URL . 'assets/js/messages-editor.js',
                ['jquery'],
                '1.0',
                true
            );

            wp_register_script(
                'beauty-app',
                BEAUTY_SAAS_URL . 'assets/js/app.js',
                ['jquery'],
                '1.0',
                true
            );

            wp_register_script(
                'beauty-agenda',
                BEAUTY_SAAS_URL . 'assets/js/agenda.js',
                ['jquery'],
                '1.0',
                true
            );

            // Estilos
            wp_register_style(
                'beauty-messages-editor',
                BEAUTY_SAAS_URL . 'assets/css/messages-editor.css',
                [],
                '1.0'
            );

            wp_register_style(
                'beauty-agenda',
                BEAUTY_SAAS_URL . 'assets/css/agenda.css',
                [],
                '1.0'
            );

            // Executa os enqueues apenas se Elementor estiver no modo preview/admin
            wp_enqueue_script('beauty-messages-editor');
            wp_enqueue_style('beauty-messages-editor');
        }
    }

    public function register_admin_pages() {
        if (!is_user_logged_in()) return;

        if (current_user_can('manage_options')) {
            add_menu_page(
                'Logs do Sistema',
                'Logs do Sistema',
                'manage_options',
                'beauty_logs',
                function () {
                    require_once BEAUTY_SAAS_PATH . 'admin/logs-page.php';
                },
                'dashicons-clipboard',
                80
            );
        }

        if (current_user_can('read')) {
            add_menu_page(
                'Painel Beauty',
                'Painel Beauty',
                'read',
                'beauty-panel',
                function () {
                    require_once BEAUTY_SAAS_PATH . 'includes/page-panel.php';
                },
                'dashicons-store',
                6
            );
        }
    }
}

new Beauty_Core();
