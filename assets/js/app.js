jQuery(document).ready(function ($) {

    $('.beauty-save-appointment').on('click', function () {

        const data = {
            action: 'beauty_create_appointment',
            nonce: beautyAjax.nonce,
            company_id: $('#company_id').val(),
            client_id: $('#client_id').val(),
            service_id: $('#service_id').val(),
            professional_id: $('#professional_id').val(),
            date: $('#date').val(),
            start_time: $('#start_time').val()
        };

        $.post(beautyAjax.ajaxurl, data, function (response) {
            if (response.success) {
                alert('Agendamento salvo com sucesso!');
                location.reload();
            } else {
                alert(response.data || 'Erro ao agendar.');
            }
        });
    });

});
