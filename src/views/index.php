<?php /** @var array $stats */ /** @var array $ultimas */ ?>
<div class="page-header">
    <h1>Dashboard - Sistema NFS-e</h1>
    <a href="nfse_emitir.php" class="btn btn-primary">+ Nova DPS</a>
</div>

<div class="info-box">
    <strong>Ambiente de Homologacao</strong> - Dados estao sendo enviados para o Sefin Nacional NFS-e (ambiente de testes).
    Acesse <a href="https://homologacao.nfse.gov.br" target="_blank" rel="noopener">homologacao.nfse.gov.br</a> para consultar o retorno.
</div>

<div class="cards-grid">
    <div class="card">
        <h3>Total Emitido (mes)</h3>
        <div class="value">R$ <?= number_format($stats['mes'], 2, ',', '.') ?></div>
    </div>
    <div class="card">
        <h3>Total Emitido (ano)</h3>
        <div class="value">R$ <?= number_format($stats['ano'], 2, ',', '.') ?></div>
    </div>
    <div class="card">
        <h3>Total Emitido (geral)</h3>
        <div class="value">R$ <?= number_format($stats['total'], 2, ',', '.') ?></div>
    </div>
    <div class="card">
        <h3>DPS Geradas</h3>
        <div class="value"><?= (int)$stats['total'] ?></div>
    </div>
    <div class="card">
        <h3>NFS-e Autorizadas</h3>
        <div class="value" style="color: var(--color-success);"><?= (int)$stats['emitidas'] ?></div>
    </div>
    <div class="card">
        <h3>Pendentes (rascunho)</h3>
        <div class="value" style="color: var(--color-warning);"><?= (int)$stats['pendentes'] ?></div>
    </div>
    <div class="card">
        <h3>Rejeitadas / Erro</h3>
        <div class="value" style="color: var(--color-danger);"><?= (int)$stats['rejeitadas'] ?></div>
    </div>
    <div class="card">
        <h3>Empresas Emissoras</h3>
        <div class="value"><?= (int)$stats['empresas'] ?></div>
    </div>
    <div class="card">
        <h3>Certificados A1 validos</h3>
        <div class="value"><?= (int)$stats['certificados'] ?></div>
    </div>
</div>

<h2 style="margin: 30px 0 12px; font-size: 1.2rem;">Ultimas DPS</h2>

<table class="table">
    <thead>
        <tr>
            <th>Numero</th>
            <th>Tomador</th>
            <th>Servico</th>
            <th>Valor</th>
            <th>Data Competencia</th>
            <th>Status</th>
            <th>cStat</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ultimas)): ?>
            <tr><td colspan="7" class="muted center">Nenhuma DPS gerada ainda.</td></tr>
        <?php else: foreach ($ultimas as $d): ?>
            <tr>
                <td><strong>#<?= (int)$d['numero'] ?></strong></td>
                <td><?= h($d['tomador_nome'] ?? '-') ?></td>
                <td><?= h($d['servico_descricao'] ?? '-') ?></td>
                <td>R$ <?= number_format((float)($d['valor_servico'] ?? 0), 2, ',', '.') ?></td>
                <td><?= h(dataIsoParaBr($d['data_competencia'] ?? '')) ?></td>
                <td>
                    <?php
                    $badge = ['rascunho' => 'default', 'pronto' => 'warning', 'enviado' => 'info', 'processado' => 'success', 'rejeitado' => 'danger', 'erro' => 'danger'];
                    $cls = $badge[$d['status']] ?? 'default';
                    ?>
                    <span class="badge badge-<?= $cls ?>"><?= h($d['status']) ?></span>
                </td>
                <td>
                    <?php if ($d['codigo_situacao']): ?>
                        <code><?= (int)$d['codigo_situacao'] ?></code>
                    <?php else: ?>
                        <span class="muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<?php if ((int)$stats['certificados'] === 0): ?>
<div class="info-box warning" style="margin-top: 24px;">
    <strong>Atencao:</strong> Nenhum certificado A1 cadastrado. Acesse
    <a href="config_certificado.php">Cadastros -> Certificado A1</a> para configurar.
    Sem certificado, as DPS nao podem ser transmitidas ao ADN.
</div>
<?php endif; ?>

<?php if ((int)$stats['empresas'] === 0): ?>
<div class="info-box warning" style="margin-top: 24px;">
    <strong>Atencao:</strong> Nenhuma empresa emissora cadastrada. Acesse
    <a href="config_empresa.php">Cadastros -> Empresa Emissora</a> para configurar.
</div>
<?php endif; ?>
