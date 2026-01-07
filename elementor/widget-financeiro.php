<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_Financeiro extends Widget_Base {

    public function get_name() { return 'beauty_financeiro'; }
    public function get_title() { return 'Beauty Financeiro'; }
    public function get_icon() { return 'eicon-wallet'; }
    public function get_categories() { return ['general']; }

    protected function render() {
        echo '<div>Financeiro funcionando</div>';
    }
}
