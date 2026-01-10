<?php
if (!defined('ABSPATH')) exit;

use Elementor\Plugin as ElementorPlugin;

class Beauty_Elementor {

    public function __construct() {
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets'], 20);
    }

    public function register_widgets() {

        $base = plugin_dir_path(__FILE__);

        require_once $base . 'widget-dashboard.php';
        require_once $base . 'widget-agenda.php';
        require_once $base . 'widget-automacoes.php';
        require_once $base . 'widget-financeiro.php';
        require_once $base . 'widget-erp.php';
        require_once $base . 'widget-funcionario.php';
        require_once $base . 'widget-messages-editor.php';

        $widgets = [
            'Beauty_Widget_Dashboard',
            'Beauty_Widget_Agenda',
            'Beauty_Widget_Automacoes',
            'Beauty_Widget_Financeiro',
            'Beauty_Widget_ERP',
            'Beauty_Widget_Funcionario',
            'Beauty_Widget_Messages_Editor',
        ];

        foreach ($widgets as $class) {
            if (class_exists($class)) {
                ElementorPlugin::instance()->widgets_manager->register_widget_type(new $class());
            }
        }
    }
}

// Só instancia se o Elementor estiver realmente carregado
add_action('elementor/init', function () {
    new Beauty_Elementor();
});
