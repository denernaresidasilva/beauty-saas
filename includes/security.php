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

        $user = wp_get_current_user();

        // Admin pode tudo (VOCÊ)
        if (in_array('administrator', $user->roles)) {
            return;
        }

        // Qualquer outro é bloqueado
        wp_redirect(home_url('/app-dashboard'));
        exit;
    }
}
