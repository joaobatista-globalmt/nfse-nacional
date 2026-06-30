<?php /** @var array $servicos */ /** @var array $lc116Map */ /** @var string $busca */ /** @var bool $apenasNfse */ /** @var int $total */ ?>
<div class="page-header">
    <h1>Servicos CNAE / NBS (do Financeiro)</h1>
</div>

<div class="info-box">
    <strong>Read-only:</strong> Estes dados vem do sistema Financeiro via view SQL cross-database.
    Para editar, acesse <a href="http://192.168.70.45/financeiro/cnae_servicos_listar.php" target="_blank">/financeiro/cnae_servicos_listar.php</a>.
</div>

<form method="get" class="filters-bar" style="background: var(--color-surface); padding: 12px; border-radius: 8px; box-shadow: var(--shadow-sm); margin-bottom: 16px;">
    <input type="search" name="busca" placeholder="Buscar por descricao, CNAE ou NBS..." value="<?= h($busca) ?>" style="flex: 1; padding: 8px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
    <label style="display: flex; align-items: center; gap: 6px;">
        <input type="checkbox" name="apenas_nfse" value="1" <?= $apenasNfse ? 'checked' : '' ?>>
        Apenas com NBS (NFSe)
    </label>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<p style="color: var(--color-text-muted); margin: 8px 0;">Total: <strong><?= $total ?></strong> servico(s)</p>

<table class="table">
    <thead>
        <tr>
            <th>CNAE</th>
            <th>Categoria</th>
            <th>Codigo</th>
            <th>Descricao</th>
            <th>NBS</th>
            <th>LC 116</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($servicos)): ?>
            <tr><td colspan="6" class="muted center">Nenhum servico encontrado.</td></tr>
        <?php else: foreach ($servicos as $s): ?>
            <tr>
                <td><code><?= h($s['cnae']) ?></code></td>
                <td><span class="badge badge-info"><?= h($s['categoria']) ?></span></td>
                <td><code><?= h($s['codigo_servico'] ?? '-') ?></code></td>
                <td><?= h($s['descricao']) ?>
                    <?php if (!empty($s['observacoes_fiscais'])): ?>
                        <br><small style="color: var(--color-text-muted); font-style: italic;"><?= h($s['observacoes_fiscais']) ?></small>
                    <?php endif; ?>
                </td>
                <td><code style="background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px;"><?= h($s['nbs'] ?? '-') ?></code></td>
                <td>
                    <code><?= h($s['lc116_item'] ?? '-') ?></code>
                    <?php if (!empty($s['lc116_item']) && !empty($lc116Map[$s['lc116_item']])): ?>
                        <br><small style="color: var(--color-text-muted);"><?= h(mb_substr($lc116Map[$s['lc116_item']], 0, 80)) ?>...</small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
