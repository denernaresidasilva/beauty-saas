public static function send($slug, $data = []) {
    global $wpdb;

    $msg = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT content FROM {$wpdb->prefix}beauty_messages
             WHERE slug=%s AND active=1",
            $slug
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
