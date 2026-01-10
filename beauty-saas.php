<?php
/**
 * Plugin Name: Beauty SaaS
 * Version: 1.1.1
 */

if (!defined('ABSPATH')) exit;

define('BEAUTY_SAAS_PATH', plugin_dir_path(__FILE__));
define('BEAUTY_SAAS_URL', plugin_dir_url(__FILE__));
define('BEAUTY_SAAS_VERSION', '1.1.1');

require_once BEAUTY_SAAS_PATH . 'includes/db.php';
require_once BEAUTY_SAAS_PATH . 'includes/activate.php';

// ✅ Novo sistema de logs
require_once BEAUTY_SAAS_PATH . 'includes/functions/log.php';

register_activation_hook(__FILE__, ['Beauty_Activate', 'run']);

add_action('plugins_loaded', function () {
    if (class_exists('Beauty_DB')) {
        Beauty_DB::maybe_upgrade(BEAUTY_SAAS_VERSION);
    }
});

add_action('plugins_loaded', function () {
    if (!did_action('elementor/loaded')) return;

    require_once BEAUTY_SAAS_PATH . 'includes/core.php';
});
