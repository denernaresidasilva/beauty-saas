<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_ERP extends Widget_Base {

    public function get_name() { return 'beauty_erp'; }
    public function get_title() { return 'Beauty ERP'; }
    public function get_icon() { return 'eicon-kit-details'; }
    public function get_categories() { return ['general']; }

    protected function render() {
        echo '<div>ERP Beauty ativo</div>';
    }
}
