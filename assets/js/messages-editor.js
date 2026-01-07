jQuery(document).ready(function ($) {

    $('.beauty-tabs button').on('click', function () {
        $('.beauty-tabs button').removeClass('active');
        $(this).addClass('active');
    });

    $('.beauty-variables button').on('click', function () {
        const variable = $(this).data('var');
        const textarea = $('#beauty-message-text');
        textarea.val(textarea.val() + variable);
    });

    $('.beauty-save').on('click', function () {
        const message = $('#beauty-message-text').val();
        const slug = $('.beauty-tabs .active').data('slug');

        if (!message || !slug) {
            alert('Preencha todos os campos.');
            return;
        }

        $.post(beautyAjax.ajaxurl, {
            action: 'beauty_save_message',
            nonce: beautyAjax.nonce,
            slug: slug,
            content: message
        }, function (res) {
            if (res.success) {
                alert('Mensagem salva com sucesso!');
            } else {
                alert('Erro: ' + res.data);
            }
        });
    });
});
