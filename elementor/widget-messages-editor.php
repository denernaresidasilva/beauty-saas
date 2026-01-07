<?php
if (!defined('ABSPATH')) exit;

use Elementor\Widget_Base;

class Beauty_Widget_Messages_Editor extends Widget_Base {

    public function get_name() {
        return 'beauty_messages_editor';
    }

    public function get_title() {
        return 'Beauty – Editor de Mensagens';
    }

    public function get_icon() {
        return 'eicon-editor-bold';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_script_depends() {
        return ['beauty-messages-editor'];
    }

    public function get_style_depends() {
        return ['beauty-messages-editor'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Editor de Mensagens', 'beauty-saas'),
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <div class="beauty-editor">

            <div class="beauty-header">
                <h3><?php esc_html_e('Mensagens Automáticas', 'beauty-saas'); ?></h3>
            </div>

            <div class="beauty-tabs">
                <button type="button" class="active" data-slug="lembrete_manual">Lembrete Manual</button>
                <button type="button" data-slug="aniversario">Aniversário</button>
                <button type="button" data-slug="followup">Follow‑up</button>
            </div>

            <textarea id="beauty-message-text" placeholder="<?php esc_attr_e('Escreva sua mensagem…', 'beauty-saas'); ?>"></textarea>

            <div class="beauty-variables">
                <span><?php esc_html_e('Variáveis:', 'beauty-saas'); ?></span>
                <button type="button" data-var="{{nome}}">{{nome}}</button>
                <button type="button" data-var="{{servico}}">{{servico}}</button>
                <button type="button" data-var="{{empresa}}">{{empresa}}</button>
                <button type="button" data-var="{{data}}">{{data}}</button>
            </div>

            <button type="button" class="beauty-save"><?php esc_html_e('Salvar Mensagem', 'beauty-saas'); ?></button>

        </div>
        <?php
    }
}
