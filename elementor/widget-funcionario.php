<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_Funcionario extends Widget_Base {

    public function get_name() { return 'beauty_funcionario'; }
    public function get_title() { return 'Beauty Funcionários'; }
    public function get_icon() { return 'eicon-users'; }
    public function get_categories() { return ['general']; }

    protected function render() {
        echo '<div>Funcionários funcionando</div>';
    }
}
