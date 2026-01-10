<?php
if (!defined('ABSPATH')) exit;

Beauty_Permissions::financial_read_only();

$commissions_url = rest_url('beauty/v1/reports/comissoes');
?>

<div class="beauty-report">
    <h2>Comissões</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>Profissional</th>
                <th>Comissão (%)</th>
                <th>Total de Vendas</th>
                <th>Total de Comissão</th>
            </tr>
        </thead>
        <tbody id="beauty-commissions-rows"></tbody>
    </table>
</div>

<script>
(function() {
    const target = document.getElementById('beauty-commissions-rows');

    fetch('<?php echo esc_url_raw($commissions_url); ?>')
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                target.innerHTML = '<tr><td colspan="4">Sem dados.</td></tr>';
                return;
            }

            target.innerHTML = data.map(item => {
                const totalVendas = parseFloat(item.total_vendas || 0);
                const totalComissao = parseFloat(item.total_comissao || 0);

                return `<tr>
                    <td>${item.name}</td>
                    <td>${parseFloat(item.commission).toFixed(2)}%</td>
                    <td>R$ ${totalVendas.toFixed(2)}</td>
                    <td>R$ ${totalComissao.toFixed(2)}</td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            target.innerHTML = '<tr><td colspan="4">Erro ao carregar relatório.</td></tr>';
        });
})();
</script>
