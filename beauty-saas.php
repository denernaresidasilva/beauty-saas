<?php
/**
 * Plugin Name: Beauty SaaS
 * Version: 1.1.0
 */

if (!defined('ABSPATH')) exit;

define('BEAUTY_SAAS_PATH', plugin_dir_path(__FILE__));
define('BEAUTY_SAAS_URL', plugin_dir_url(__FILE__));

require_once BEAUTY_SAAS_PATH . 'includes/db.php';
require_once BEAUTY_SAAS_PATH . 'includes/activate.php';

// ✅ Novo sistema de logs
require_once BEAUTY_SAAS_PATH . 'includes/functions/log.php';

register_activation_hook(__FILE__, ['Beauty_Activate', 'run']);

// Carrega o core do plugin. Antes estava dentro de plugins_loaded com um did_action('elementor/loaded')
// que poderia impedir o carregamento caso o hook do Elementor ainda não tivesse sido disparado.
if ( did_action( 'elementor/loaded' ) ) {
    // Se Elementor já estiver carregado, incluir imediatamente
    require_once BEAUTY_SAAS_PATH . 'includes/core.php';
} else {
    // Se não, aguarda o carregamento do Elementor para incluir o core
    add_action('elementor/loaded', function () {
        require_once BEAUTY_SAAS_PATH . 'includes/core.php';
    });
}
