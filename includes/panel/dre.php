<?php
if (!defined('ABSPATH')) exit;

Beauty_Permissions::financial_read_only();

$dre_url = rest_url('beauty/v1/reports/dre');
?>

<div class="beauty-report">
    <h2>DRE</h2>
    <div id="beauty-dre-summary"></div>
    <table class="widefat">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="beauty-dre-rows"></tbody>
    </table>
</div>

<script>
(function() {
    const target = document.getElementById('beauty-dre-rows');
    const summary = document.getElementById('beauty-dre-summary');

    fetch('<?php echo esc_url_raw($dre_url); ?>')
        .then(response => response.json())
        .then(data => {
            if (!data || !data.detalhes) {
                target.innerHTML = '<tr><td colspan="3">Sem dados.</td></tr>';
                return;
            }

            target.innerHTML = data.detalhes.map(item => {
                const category = item.name || 'Sem categoria';
                return `<tr>
                    <td>${category}</td>
                    <td>${item.entry_type}</td>
                    <td>R$ ${parseFloat(item.total).toFixed(2)}</td>
                </tr>`;
            }).join('');

            if (data.totais) {
                summary.innerHTML = `
                    <p><strong>Receitas:</strong> R$ ${parseFloat(data.totais.receitas).toFixed(2)}</p>
                    <p><strong>Despesas:</strong> R$ ${parseFloat(data.totais.despesas).toFixed(2)}</p>
                    <p><strong>Resultado:</strong> R$ ${parseFloat(data.totais.resultado).toFixed(2)}</p>
                `;
            }
        })
        .catch(() => {
            target.innerHTML = '<tr><td colspan="3">Erro ao carregar relatório.</td></tr>';
        });
})();
</script>
