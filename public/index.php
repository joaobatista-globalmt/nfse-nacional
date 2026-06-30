<?php
require __DIR__ . '/bootstrap.php';
Auth::require();

$db = Database::getConnection();

// Cards: total emitida no mes, no ano, total geral
$stats = [];
$stats['mes']  = (float)$db->query("SELECT COALESCE(SUM(valor_servico), 0) FROM nfse_dps WHERE DATE_FORMAT(data_competencia, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn();
$stats['ano']  = (float)$db->query("SELECT COALESCE(SUM(valor_servico), 0) FROM nfse_dps WHERE YEAR(data_competencia) = YEAR(CURDATE())")->fetchColumn();
$stats['total'] = (int)$db->query("SELECT COUNT(*) FROM nfse_dps")->fetchColumn();
$stats['emitidas'] = (int)$db->query("SELECT COUNT(*) FROM nfse_emitidas WHERE codigo_situacao IN (100, 102, 103, 107)")->fetchColumn();
$stats['pendentes'] = (int)$db->query("SELECT COUNT(*) FROM nfse_dps WHERE status IN ('rascunho','pronto')")->fetchColumn();
$stats['rejeitadas'] = (int)$db->query("SELECT COUNT(*) FROM nfse_dps WHERE status IN ('rejeitado','erro')")->fetchColumn();
$stats['empresas'] = (int)$db->query("SELECT COUNT(*) FROM nfse_empresas WHERE ativo = 1")->fetchColumn();
$stats['certificados'] = (int)$db->query("SELECT COUNT(*) FROM nfse_certificados WHERE ativo = 1 AND validade_fim >= CURDATE()")->fetchColumn();

$ultimas = $db->query("
    SELECT d.id, d.numero, d.tomador_nome, d.servico_descricao, d.valor_servico, d.status, d.data_competencia,
           e.codigo_situacao
    FROM nfse_dps d
    LEFT JOIN nfse_emitidas e ON e.dps_id = d.id
    ORDER BY d.id DESC
    LIMIT 10
")->fetchAll();

layout('Dashboard', 'index.php', [
    'stats' => $stats,
    'ultimas' => $ultimas,
]);
?>
