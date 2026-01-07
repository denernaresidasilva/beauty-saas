jQuery(function ($) {
    function loadAgenda() {
        $('#agenda-list').html('Carregando agenda...');

        $.post(beautyAjax.ajaxurl, {
            action: 'beauty_load_agenda',
            date: $('#agenda-date').val()
        }, function (rows) {
            if (!rows.length) {
                $('#agenda-list').html('<p>Nenhum agendamento</p>');
                return;
            }

            let html = '';
            rows.forEach(a => {
                html += `
                  <div class="agenda-item ${a.status}">
                    <strong>${a.start_time.substr(11,5)}</strong>
                    <span>${a.client_name}</span>
                    <em>${a.service_name}</em>
                  </div>`;
            });

            $('#agenda-list').html(html);
        });
    }

    loadAgenda();

    $('#agenda-date').on('change', loadAgenda);

    $('#agenda-add').on('click', function () {
        const client = prompt('Cliente:');
        if (!client) return;

        const service = prompt('Serviço:');
        const time = prompt('Hora início (HH:MM):');
        const date = $('#agenda-date').val();

        $.post(beautyAjax.ajaxurl, {
            action: 'beauty_add_appointment',
            client: client,
            service: service,
            start: date + ' ' + time + ':00',
            end: date + ' ' + time + ':59'
        }, loadAgenda);
    });
});
