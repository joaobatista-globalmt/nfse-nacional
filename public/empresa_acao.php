<?php
require __DIR__ . '/bootstrap.php';
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$controller = new EmpresaController();
switch ($acao) {
    case 'excluir': $controller->excluir(); break;
    default: redirect('empresas_listar.php', 'erro', 'Acao invalida.');
}
