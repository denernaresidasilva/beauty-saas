<?php
if (!defined('ABSPATH')) exit;

class Beauty_Security {

    public function __construct() {

        // Bloqueia wp-admin
        add_action('admin_init', [$this, 'block_admin']);

        // Remove barra admin
        add_filter('show_admin_bar', '__return_false');
    }

    public function block_admin() {

        if (!is_user_logged_in()) {
            return;
        }

        if ($this->should_allow_admin_request()) {
            return;
        }

        $user = wp_get_current_user();

        $allowed_roles = apply_filters('beauty_security_admin_allowed_roles', ['administrator']);

        // Admin pode tudo (VOCÊ)
        if (array_intersect($allowed_roles, $user->roles)) {
            return;
        }

        // Qualquer outro é bloqueado
        wp_redirect(home_url('/app-dashboard'));
        exit;
    }

    private function should_allow_admin_request() {
        if (wp_doing_ajax() || wp_doing_cron()) {
            return true;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        global $pagenow;
        $allowed_pages = [
            'admin-ajax.php',
            'admin-post.php',
            'async-upload.php',
        ];

        return in_array($pagenow, $allowed_pages, true);
    }
}
