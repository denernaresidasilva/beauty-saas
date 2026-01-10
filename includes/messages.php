<?php
if (!defined('ABSPATH')) exit;

function beauty_send_message($slug, $data = []) {
    global $wpdb;

    $company_id = intval($data['company_id'] ?? 0);
    if ($company_id <= 0) {
        $company_id = Beauty_Company::get_current_company_id();
    }

    if ($company_id <= 0) {
        return;
    }

    $msg = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT content FROM {$wpdb->prefix}beauty_messages
             WHERE slug=%s AND active=1 AND company_id = %d",
            $slug,
            $company_id
        )
    );

    if (!$msg) return;

    $text = $msg->content;

    foreach ($data as $k => $v) {
        $text = str_replace('{{'.$k.'}}', $v, $text);
    }

    // FUTURO: WhatsApp API
    error_log('[BEAUTY MESSAGE] ' . $text);
}

add_action('beauty_send_message', function ($message_id, $context = []) {
    global $wpdb;

    $company_id = intval($context['company_id'] ?? 0);
    if ($company_id <= 0) {
        return;
    }

    $message = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT slug FROM {$wpdb->prefix}beauty_messages WHERE id = %d AND company_id = %d",
            $message_id,
            $company_id
        )
    );

    if (!$message) {
        return;
    }

    beauty_send_message($message->slug, $context);
}, 10, 2);
