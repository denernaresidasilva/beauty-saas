<?php if (!defined('ABSPATH')) exit; ?>

<div class="beauty-editor">
    <div class="beauty-header">
        <h3><?php esc_html_e('Mensagens Automáticas', 'beauty-saas'); ?></h3>
        <button type="button" class="beauty-new"><?php esc_html_e('Nova mensagem', 'beauty-saas'); ?></button>
    </div>

    <div class="beauty-tabs">
        <button type="button" class="active" data-slug="lembrete_manual">Lembrete Manual</button>
        <button type="button" data-slug="aniversario">Aniversário</button>
        <button type="button" data-slug="followup">Follow-up</button>
    </div>

    <textarea id="beauty-message-text" placeholder="<?php esc_attr_e('Escreva sua mensagem aqui...', 'beauty-saas'); ?>"></textarea>

    <div class="beauty-variables">
        <span><?php esc_html_e('Variáveis disponíveis:', 'beauty-saas'); ?></span>
        <button type="button" data-var="{{nome}}">{{nome}}</button>
        <button type="button" data-var="{{servico}}">{{servico}}</button>
        <button type="button" data-var="{{empresa}}">{{empresa}}</button>
        <button type="button" data-var="{{profissional}}">{{profissional}}</button>
        <button type="button" data-var="{{data}}">{{data}}</button>
    </div>

    <button type="button" class="beauty-save"><?php esc_html_e('Salvar Mensagem', 'beauty-saas'); ?></button>
</div>
