<?php
if (!defined('ABSPATH')) exit;

function beauty_api_get_auth_context(WP_REST_Request $request) {
    global $wpdb;

    $token = $request->get_header('x-beauty-token');
    if (!$token) {
        $authorization = $request->get_header('authorization');
        if ($authorization && stripos($authorization, 'Bearer ') === 0) {
            $token = substr($authorization, 7);
        }
    }

    $token = sanitize_text_field($token ?? '');

    if (!$token) {
        return new WP_Error('beauty_auth_missing', 'Token de autenticação não informado.', ['status' => 401]);
    }

    $session = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT s.user_id, c.id AS company_id
             FROM {$wpdb->prefix}beauty_sessions s
             LEFT JOIN {$wpdb->prefix}beauty_companies c ON c.user_id = s.user_id
             WHERE s.token = %s AND s.expires_at > NOW()",
            $token
        )
    );

    if (!$session || empty($session->company_id)) {
        return new WP_Error('beauty_auth_invalid', 'Token inválido ou expirado.', ['status' => 401]);
    }

    wp_set_current_user((int) $session->user_id);

    return [
        'user_id' => (int) $session->user_id,
        'company_id' => (int) $session->company_id,
    ];
}

function beauty_api_require_auth(WP_REST_Request $request) {
    $context = beauty_api_get_auth_context($request);
    return is_wp_error($context) ? $context : true;
}

function beauty_api_issue_token(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');

    if (!$email || !$password) {
        return new WP_Error('beauty_auth_invalid_request', 'E-mail e senha são obrigatórios.', ['status' => 400]);
    }

    $user = get_user_by('email', $email);

    if (!$user || !wp_check_password($password, $user->user_pass, $user->ID)) {
        return new WP_Error('beauty_auth_invalid_credentials', 'Credenciais inválidas.', ['status' => 401]);
    }

    global $wpdb;

    $token = wp_generate_password(48, false);
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

    $wpdb->insert(
        $wpdb->prefix . 'beauty_sessions',
        [
            'user_id' => $user->ID,
            'token' => $token,
            'expires_at' => $expires,
        ]
    );

    return [
        'token' => $token,
        'expires_at' => $expires,
    ];
}

add_action('rest_api_init', function () {
    $versions = ['v1', 'v2'];
    $resources = [
        'appointments' => 'beauty_appointments',
        'clients' => 'beauty_clients',
        'professionals' => 'beauty_professionals',
        'services' => 'beauty_services',
        'products' => 'beauty_products',
        'financial' => 'beauty_financial',
        'automations' => 'beauty_automations',
    ];

    foreach ($versions as $version) {
        register_rest_route("beauty/{$version}", '/auth/token', [
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => 'beauty_api_issue_token',
        ]);

        foreach ($resources as $route => $table) {
            register_rest_route("beauty/{$version}", "/{$route}", [
                'methods' => 'GET',
                'permission_callback' => 'beauty_api_require_auth',
                'callback' => function (WP_REST_Request $request) use ($table) {
                    global $wpdb;

                    $context = beauty_api_get_auth_context($request);
                    if (is_wp_error($context)) {
                        return $context;
                    }

                    $company_id = $context['company_id'];

                    return $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}{$table} WHERE company_id = %d",
                            $company_id
                        )
                    );
                },
            ]);
        }
    }
});
