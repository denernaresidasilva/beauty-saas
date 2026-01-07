<?php
if (!defined('ABSPATH')) exit;

class Beauty_Auth {

    public function __construct() {
        add_action('wp_ajax_beauty_login', [$this, 'login']);
        add_action('wp_ajax_nopriv_beauty_login', [$this, 'login']);

        add_action('init', [$this, 'block_wp_admin']);
    }

    /**
     * LOGIN FRONT
     */
    public function login() {
        check_ajax_referer('beauty_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            wp_send_json_error('Preencha todos os campos');
        }

        $user = get_user_by('email', $email);

        if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
            wp_send_json_error('E-mail ou senha inválidos');
        }

        // cria sessão própria
        global $wpdb;
        $token = wp_generate_password(32, false);
        $expires = date('Y-m-d H:i:s', strtotime('+1 day'));

        $wpdb->insert(
            $wpdb->prefix . 'beauty_sessions',
            [
                'user_id'    => $user->ID,
                'token'      => $token,
                'expires_at' => $expires
            ]
        );

        setcookie('beauty_session', $token, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);

        wp_send_json_success([
            'redirect' => site_url('/app-dashboard')
        ]);
    }

    /**
     * BLOQUEIA WP-ADMIN PARA NÃO ADMINS
     */
    public function block_wp_admin() {
        if (is_admin() && !current_user_can('administrator') && !wp_doing_ajax()) {
            wp_redirect(site_url('/login'));
            exit;
        }
    }

    /**
     * UTILIDADE: usuário logado no sistema?
     */
    public static function user() {
        if (empty($_COOKIE['beauty_session'])) return false;

        global $wpdb;
        $token = sanitize_text_field($_COOKIE['beauty_session']);

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_sessions 
                 WHERE token = %s AND expires_at > NOW()",
                $token
            )
        );

        return $session ? get_user_by('id', $session->user_id) : false;
    }
}

new Beauty_Auth();
