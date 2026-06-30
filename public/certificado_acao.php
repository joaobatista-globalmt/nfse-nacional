<?php
require __DIR__ . '/bootstrap.php';
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$controller = new CertificadoController();
switch ($acao) {
    case 'excluir': $controller->excluir(); break;
    default: redirect('certificados_listar.php', 'erro', 'Acao invalida.');
}
