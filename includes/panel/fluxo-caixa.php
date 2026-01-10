<?php
if (!defined('ABSPATH')) exit;

Beauty_Permissions::financial_read_only();

$cashflow_url = rest_url('beauty/v1/reports/fluxo-caixa');
?>

<div class="beauty-report">
    <h2>Fluxo de Caixa</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>Data</th>
                <th>Entradas</th>
                <th>Saídas</th>
                <th>Saldo do Dia</th>
            </tr>
        </thead>
        <tbody id="beauty-cashflow-rows"></tbody>
    </table>
</div>

<script>
(function() {
    const target = document.getElementById('beauty-cashflow-rows');

    fetch('<?php echo esc_url_raw($cashflow_url); ?>')
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                target.innerHTML = '<tr><td colspan="4">Sem dados.</td></tr>';
                return;
            }

            target.innerHTML = data.map(item => {
                const entradas = parseFloat(item.entradas || 0);
                const saidas = parseFloat(item.saidas || 0);
                const saldo = entradas - saidas;

                return `<tr>
                    <td>${item.entry_date}</td>
                    <td>R$ ${entradas.toFixed(2)}</td>
                    <td>R$ ${saidas.toFixed(2)}</td>
                    <td>R$ ${saldo.toFixed(2)}</td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            target.innerHTML = '<tr><td colspan="4">Erro ao carregar relatório.</td></tr>';
        });
})();
</script>
