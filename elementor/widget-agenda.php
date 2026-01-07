<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_Agenda extends Widget_Base {

    public function get_name() { return 'beauty_agenda'; }
    public function get_title() { return 'Beauty Agenda'; }
    public function get_icon() { return 'eicon-calendar'; }
    public function get_categories() { return ['general']; }

    protected function render() {
        echo '<div>Agenda funcionando</div>';
    }
}
