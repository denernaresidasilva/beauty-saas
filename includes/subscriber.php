<?php
if (!defined('ABSPATH')) exit;

class Beauty_Subscriber {

    public function __construct() {

        // Quando um usuário WP é criado
        add_action('user_register', [$this, 'create_company'], 10, 1);

        // Bloqueia wp-admin para assinantes
        add_action('admin_init', [$this, 'block_admin']);

        // Redireciona login sempre para o front
        add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);
    }

    /**
     * Cria empresa automaticamente ao criar assinante
     */
    public function create_company($user_id) {
        global $wpdb;

        // Evita duplicação
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}beauty_companies WHERE user_id=%d",
                $user_id
            )
        );

        if ($exists) return;

        $user = get_userdata($user_id);

        $wpdb->insert(
            "{$wpdb->prefix}beauty_companies",
            [
                'user_id' => $user_id,
                'name'    => $user->display_name ?: 'Minha Empresa',
            ]
        );

        // Marca o usuário como assinante do sistema
        update_user_meta($user_id, 'beauty_role', 'owner');
    }

    /**
     * Bloqueia acesso ao wp-admin
     */
    public function block_admin() {
        if (!is_user_logged_in()) return;

        if (current_user_can('administrator')) return;

        wp_redirect(site_url('/app-dashboard'));
        exit;
    }

    /**
     * Redireciona login sempre para o front
     */
    public function login_redirect($redirect_to, $requested, $user) {

        if (isset($user->roles) && !in_array('administrator', $user->roles)) {
            return site_url('/app-dashboard');
        }

        return $redirect_to;
    }
}
