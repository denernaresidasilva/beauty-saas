<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Beauty_Widget_Dashboard extends Widget_Base {

    public function get_name() { return 'beauty_dashboard'; }
    public function get_title() { return 'Beauty Dashboard'; }
    public function get_icon() { return 'eicon-dashboard'; }
    public function get_categories() { return ['general']; }

    // Antes: _register_controls() -> corrigido para register_controls()
    protected function register_controls() {

        $this->start_controls_section('content', [
            'label' => 'Conteúdo'
        ]);

        $this->add_control('title', [
            'label' => 'Título',
            'type' => Controls_Manager::TEXT,
            'default' => 'Dashboard Beauty SaaS'
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo '<h3>' . esc_html($settings['title']) . '</h3>';
    }
}
