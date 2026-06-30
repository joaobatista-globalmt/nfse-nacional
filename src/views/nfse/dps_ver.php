<?php
/** @var array $dps */ /** @var array $dados */
$statusLabel = [
    'rascunho' => 'Rascunho', 'pronto' => 'Pronto', 'enviado' => 'Enviado',
    'processado' => 'Processado', 'rejeitado' => 'Rejeitado', 'erro' => 'Erro',
];
$statusBadge = [
    'rascunho' => 'default', 'pronto' => 'info', 'enviado' => 'info',
    'processado' => 'success', 'rejeitado' => 'danger', 'erro' => 'warning',
];
$fmt = fn($v) => $v !== null && $v !== '' ? 'R$ ' . number_format((float)$v, 2, ',', '.') : '-';
?>
<div class="page-header">
    <h1>DPS #<?= (int)$dps['id'] ?> <small style="color: var(--color-text-muted); font-weight: 400; font-size: 0.6em;"><?= h(($dps['serie'] ?? '1') . '-' . str_pad((string)$dps['numero'], 6, '0', STR_PAD_LEFT)) ?></small></h1>
    <div>
        <span class="badge badge-<?= $statusBadge[$dps['status']] ?? 'default' ?>" style="font-size: 14px; padding: 6px 12px;"><?= $statusLabel[$dps['status']] ?? $dps['status'] ?></span>
        <?php if (in_array($dps['status'], ['rascunho', 'rejeitado'], true)): ?>
            <a href="dps_form.php?id=<?= (int)$dps['id'] ?>" class="btn btn-primary">Editar</a>
            <form method="post" action="dps_excluir.php" style="display: inline;" onsubmit="return confirm('Excluir esta DPS?')">
                <input type="hidden" name="id" value="<?= (int)$dps['id'] ?>">
                <button type="submit" class="btn btn-danger">Excluir</button>
            </form>
        <?php endif; ?>
        <a href="dps_listar.php" class="btn">← Voltar</a>
    </div>
</div>

<?php if ($currentFlash && $currentFlash['tipo'] === 'sucesso'): ?>
    <div class="alert alert-success"><?= $currentFlash['msg'] ?></div>
<?php endif; ?>

<!-- Resumo -->
<div class="cards-grid">
    <div class="card-stat">
        <div class="card-stat-label">Valor do Servico</div>
        <div class="card-stat-value"><?= h($fmt($dados['valores']['valor_servico'] ?? null)) ?></div>
    </div>
    <div class="card-stat">
        <div class="card-stat-label">Base de Calculo</div>
        <div class="card-stat-value"><?= h($fmt($dados['valores']['base_calculo'] ?? null)) ?></div>
    </div>
    <div class="card-stat">
        <div class="card-stat-label">Aliquota ISS</div>
        <div class="card-stat-value"><?= h(number_format((float)($dados['valores']['aliquota_iss'] ?? 0), 2, ',', '.')) ?>%</div>
    </div>
    <div class="card-stat">
        <div class="card-stat-label">ISS <?= ((int)($dados['valores']['iss_retido'] ?? 0)) === 1 ? 'Retido' : 'a Recolher' ?></div>
        <div class="card-stat-value"><?= h($fmt($dados['valores']['valor_iss'] ?? null)) ?></div>
    </div>
</div>

<!-- Empresa + Tomador -->
<div class="form-section">
    <h3>Empresa Emissora</h3>
    <p>
        <strong><?= h($dps['empresa_razao_social']) ?></strong><br>
        CNPJ: <?= h(formatarCnpj($dps['empresa_cnpj'])) ?> | IM: <?= h($dps['empresa_im'] ?: 'sem IM') ?>
    </p>
</div>

<div class="form-section">
    <h3>Tomador</h3>
    <p>
        <strong><?= h($dados['tomador']['nome'] ?? '-') ?></strong>
        (<?= ($dados['tomador']['tipo'] ?? 'J') === 'F' ? 'CPF' : 'CNPJ' ?>: <?= h($dados['tomador']['documento'] ?? '-') ?>)<br>
        <?php if (!empty($dados['tomador']['endereco'])): ?>
            <?= h($dados['tomador']['endereco']) ?>, <?= h($dados['tomador']['numero'] ?? 's/n') ?><br>
            <?= h($dados['tomador']['bairro'] ?? '') ?><br>
            CEP: <?= h($dados['tomador']['cep'] ?? '-') ?> | Cod. IBGE: <?= h($dados['tomador']['cidade_ibge'] ?? '-') ?> / <?= h($dados['tomador']['uf'] ?? '-') ?>
        <?php endif; ?>
        <?php if (!empty($dados['tomador']['email'])): ?><br>Email: <?= h($dados['tomador']['email']) ?><?php endif; ?>
        <?php if (!empty($dados['tomador']['telefone'])): ?><br>Tel: <?= h($dados['tomador']['telefone']) ?><?php endif; ?>
    </p>
</div>

<div class="form-section">
    <h3>Servico</h3>
    <p>
        <strong><?= h($dados['servico']['descricao'] ?? '-') ?></strong><br>
        NBS: <?= h($dados['servico']['nbs'] ?: '-') ?> |
        LC 116: <?= h($dados['servico']['lc116'] ?: '-') ?> |
        CST IBS/CBS: <?= h($dados['servico']['cst_ibs_cbs'] ?: '-') ?>
    </p>
    <?php if (!empty($dados['servico']['discriminacao'])): ?>
        <p style="white-space: pre-wrap; color: var(--color-text-muted);"><?= h($dados['servico']['discriminacao']) ?></p>
    <?php endif; ?>
</div>

<div class="form-section">
    <h3>Valores Detalhados</h3>
    <table class="table">
        <tr><td>Valor do Servico</td><td style="text-align: right;"><?= h($fmt($dados['valores']['valor_servico'] ?? null)) ?></td></tr>
        <tr><td>(-) Deducoes</td><td style="text-align: right;"><?= h($fmt($dados['valores']['valor_deducoes'] ?? null)) ?></td></tr>
        <tr><td>(-) Desconto Incondicional</td><td style="text-align: right;"><?= h($fmt($dados['valores']['valor_desconto_incondicional'] ?? null)) ?></td></tr>
        <tr style="font-weight: 600; background: var(--color-surface-alt);">
            <td>(=) Base de Calculo</td>
            <td style="text-align: right;"><?= h($fmt($dados['valores']['base_calculo'] ?? null)) ?></td>
        </tr>
        <tr><td>Aliquota ISS</td><td style="text-align: right;"><?= h(number_format((float)($dados['valores']['aliquota_iss'] ?? 0), 2, ',', '.')) ?>%</td></tr>
        <tr style="font-weight: 600; background: var(--color-surface-alt);">
            <td>(=) Valor ISS <?= ((int)($dados['valores']['iss_retido'] ?? 0)) === 1 ? 'Retido' : 'a Recolher' ?></td>
            <td style="text-align: right;"><?= h($fmt($dados['valores']['valor_iss'] ?? null)) ?></td>
        </tr>
    </table>
</div>

<div class="form-section">
    <h3>Metadados</h3>
    <p>
        <strong>Data de Competencia:</strong> <?= h($dps['data_competencia'] ? date('d/m/Y', strtotime($dps['data_competencia'])) : '-') ?><br>
        <strong>Tipo de Retencao:</strong> <?= h($dados['valores']['tipo_retencao'] ?: 'Sem retencao') ?><br>
        <strong>Criada em:</strong> <?= h($dps['created_at'] ? date('d/m/Y H:i:s', strtotime($dps['created_at'])) : '-') ?><br>
        <strong>Atualizada em:</strong> <?= h($dps['updated_at'] ? date('d/m/Y H:i:s', strtotime($dps['updated_at'])) : '-') ?><br>
        <?php if (!empty($dps['chave_acesso'])): ?>
            <strong>Chave de Acesso:</strong> <code><?= h($dps['chave_acesso']) ?></code><br>
        <?php endif; ?>
    </p>
</div>

<!-- JSON cru (debug) -->
<details style="margin-top: 24px;">
    <summary style="cursor: pointer; color: var(--color-text-muted);">Ver JSON completo (debug)</summary>
    <pre style="background: var(--color-surface-alt); padding: 12px; border-radius: 6px; overflow: auto;"><?= h(json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</details>

<style>
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin: 16px 0;
}
</style>
