<?php
if (!defined('ABSPATH')) exit;

// Lista mensagens
add_action('wp_ajax_beauty_list_messages', function () {
    if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce inválido.');
    }

    Beauty_Permissions::company_only();

    global $wpdb;
    $company_id = Beauty_Company::get_company_id();

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT slug, slug AS title 
             FROM {$wpdb->prefix}beauty_messages 
             WHERE company_id = %d AND active = 1",
            $company_id
        )
    );

    wp_send_json_success($rows);
});

// Retorna conteúdo de uma mensagem
add_action('wp_ajax_beauty_get_message', function () {
    if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce inválido.');
    }

    Beauty_Permissions::company_only();

    global $wpdb;
    $slug = sanitize_text_field($_POST['slug'] ?? '');

    $row = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT content 
             FROM {$wpdb->prefix}beauty_messages 
             WHERE slug = %s",
            $slug
        )
    );

    wp_send_json_success($row);
});

// Salva mensagem
add_action('wp_ajax_beauty_save_message', function () {
    if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce inválido.');
    }

    Beauty_Permissions::company_only();

    global $wpdb;

    $slug    = sanitize_text_field($_POST['slug'] ?? '');
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    $company_id = Beauty_Company::get_company_id();

    if (!$slug) {
        wp_send_json_error('Slug inválido.');
    }

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}beauty_messages 
             WHERE slug = %s AND company_id = %d",
            $slug,
            $company_id
        )
    );

    if ($exists) {
        $wpdb->update(
            "{$wpdb->prefix}beauty_messages",
            ['content' => $content],
            ['id' => $exists]
        );
    } else {
        $wpdb->insert(
            "{$wpdb->prefix}beauty_messages",
            [
                'slug'       => $slug,
                'company_id' => $company_id,
                'content'    => $content,
                'active'     => 1
            ]
        );
    }

    wp_send_json_success('Mensagem salva com sucesso.');
});
