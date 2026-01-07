<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_Automacoes extends Widget_Base {

    public function get_name() { return 'beauty_automacoes'; }
    public function get_title() { return 'Beauty Automações'; }
    public function get_icon() { return 'eicon-flash'; }
    public function get_categories() { return ['general']; }

    protected function render() {
        echo '<div>Automações funcionando</div>';
    }
}
