<?php
/** @var array $dps */ /** @var string $busca */ /** @var string $status */ /** @var array $contadores */ /** @var int $total */
$statusLabel = [
    'rascunho'   => 'Rascunho',
    'pronto'     => 'Pronto',
    'enviado'    => 'Enviado',
    'processado' => 'Processado',
    'rejeitado'  => 'Rejeitado',
    'erro'       => 'Erro',
];
$statusBadge = [
    'rascunho'   => 'default',
    'pronto'     => 'info',
    'enviado'    => 'info',
    'processado' => 'success',
    'rejeitado'  => 'danger',
    'erro'       => 'warning',
];
$contMap = [];
foreach ($contadores as $c) $contMap[$c['status']] = (int)$c['qtd'];
?>
<div class="page-header">
    <h1>DPS - Declaracao de Prestacao de Servico</h1>
    <div>
        <a href="nfse_emitir.php" class="btn btn-primary">+ Nova DPS</a>
    </div>
</div>

<div class="info-box">
    <strong>Fase 3:</strong> Emissao de DPS (sem transmissao). A DPS fica como RASCUNHO ate voce finalizar. Apos finalizar, vai pra status PRONTO e podera ser transmitida (Fase 4 - depende do certificado A1).
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 16px;">
    <?php foreach ($statusLabel as $s => $label): ?>
        <a href="dps_listar.php?status=<?= $s ?>" class="card-stat" style="text-decoration: none;">
            <div class="card-stat-label"><?= $label ?></div>
            <div class="card-stat-value" style="color: var(--color-<?= $statusBadge[$s] === 'default' ? 'text-muted' : $statusBadge[$s] ?>);">
                <?= $contMap[$s] ?? 0 ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<form method="get" class="filters-bar" style="background: var(--color-surface); padding: 12px; border-radius: 8px; box-shadow: var(--shadow-sm); margin-bottom: 16px;">
    <input type="search" name="busca" placeholder="Buscar por tomador, servico ou chave de acesso..." value="<?= h($busca) ?>" style="flex: 1; padding: 8px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
    <select name="status" style="padding: 8px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
        <option value="">Todos os status</option>
        <?php foreach ($statusLabel as $s => $label): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <?php if ($busca !== '' || $status !== ''): ?>
        <a href="dps_listar.php" class="btn">Limpar</a>
    <?php endif; ?>
</form>

<p style="color: var(--color-text-muted); margin: 8px 0;">Total: <strong><?= $total ?></strong> DPS(s)</p>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Emissao</th>
            <th>Competencia</th>
            <th>Empresa</th>
            <th>Tomador</th>
            <th>Servico</th>
            <th style="text-align: right;">Valor</th>
            <th>Status</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($dps)): ?>
            <tr><td colspan="9" class="muted center">Nenhuma DPS encontrada. <a href="nfse_emitir.php">Criar a primeira</a>.</td></tr>
        <?php else: foreach ($dps as $d): ?>
            <tr>
                <td><strong>#<?= (int)$d['id'] ?></strong><br><small style="color: var(--color-text-muted);"><?= h(($d['serie'] ?? '1') . '-' . str_pad((string)$d['numero'], 6, '0', STR_PAD_LEFT)) ?></small></td>
                <td><small><?= h($d['created_at'] ? date('d/m/Y H:i', strtotime($d['created_at'])) : '-') ?></small></td>
                <td><?= h($d['data_competencia'] ? date('d/m/Y', strtotime($d['data_competencia'])) : '-') ?></td>
                <td><small><?= h($d['empresa_razao_social']) ?><br><?= h(formatarCnpj($d['empresa_cnpj'])) ?></small></td>
                <td><?= h($d['tomador_nome'] ?? '-') ?><br><small style="color: var(--color-text-muted);"><?= h($d['tomador_documento'] ?? '-') ?></small></td>
                <td><small><?= h($d['servico_descricao'] ?? '-') ?></small></td>
                <td style="text-align: right; font-variant-numeric: tabular-nums;"><?= $d['valor_servico'] !== null ? 'R$ ' . number_format((float)$d['valor_servico'], 2, ',', '.') : '-' ?></td>
                <td><span class="badge badge-<?= $statusBadge[$d['status']] ?? 'default' ?>"><?= $statusLabel[$d['status']] ?? $d['status'] ?></span></td>
                <td>
                    <a href="dps_ver.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm">Ver</a>
                    <?php if (in_array($d['status'], ['rascunho', 'rejeitado'], true)): ?>
                        <a href="dps_form.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
