<?php
if (!defined('ABSPATH')) exit;

class Beauty_Pages {

    public function __construct() {
        add_action('init', [$this, 'shortcodes']);
    }

    public function shortcodes() {

        add_shortcode('beauty_login', function () {
            ob_start(); ?>
            
            <div class="beauty-login">
                <h2>Acessar Sistema</h2>

                <input type="email" id="beauty-email" placeholder="E-mail">
                <input type="password" id="beauty-password" placeholder="Senha">

                <button id="beauty-login-btn">Entrar</button>

                <p class="beauty-error"></p>
            </div>

            <script>
            jQuery('#beauty-login-btn').on('click', function () {
                jQuery.post(beautyAjax.ajaxurl, {
                    action: 'beauty_login',
                    nonce: beautyAjax.nonce,
                    email: jQuery('#beauty-email').val(),
                    password: jQuery('#beauty-password').val()
                }, function (res) {
                    if (res.success) {
                        window.location = res.data.redirect;
                    } else {
                        jQuery('.beauty-error').text(res.data);
                    }
                });
            });
            </script>

            <?php
            return ob_get_clean();
        });
    }
}

new Beauty_Pages();
