<?php
if (!defined('ABSPATH')) exit;

$current_view = sanitize_text_field($_GET['view'] ?? 'dashboard');

$views = [
    'dashboard'  => 'panel/dashboard.php',
    'clientes'   => 'panel/clientes.php',
    'servicos'   => 'panel/servicos.php',
    'mensagens'  => 'panel/mensagens.php',
    'dre'        => 'panel/dre.php',
    'fluxo-caixa'=> 'panel/fluxo-caixa.php',
    'comissoes'  => 'panel/comissoes.php',
    // ... adicione outras views aqui
];

$path = $views[$current_view] ?? 'panel/dashboard.php';
$file = BEAUTY_SAAS_PATH . 'includes/' . $path;

if (file_exists($file)) {
    require_once $file;
} else {
    echo '<p>View não encontrada.</p>';
}
?>
