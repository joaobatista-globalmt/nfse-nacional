<?php /** @var array $clientes */ /** @var string $busca */ /** @var bool $filtroAtivo */ /** @var int $total */ ?>
<div class="page-header">
    <h1>Clientes (do Financeiro)</h1>
</div>

<div class="info-box">
    <strong>Read-only:</strong> Estes dados vem do sistema Financeiro via view SQL cross-database.
    Para editar, acesse <a href="http://192.168.70.45/financeiro/clientes.php" target="_blank">/financeiro/clientes.php</a>.
</div>

<form method="get" class="filters-bar" style="background: var(--color-surface); padding: 12px; border-radius: 8px; box-shadow: var(--shadow-sm); margin-bottom: 16px;">
    <input type="search" name="busca" placeholder="Buscar por razao social ou CPF/CNPJ..." value="<?= h($busca) ?>" style="flex: 1; padding: 8px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
    <label style="display: flex; align-items: center; gap: 6px;">
        <input type="checkbox" name="apenas_ativos" value="1" <?= $filtroAtivo ? 'checked' : '' ?>>
        Apenas ativos
    </label>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<p style="color: var(--color-text-muted); margin: 8px 0;">Total: <strong><?= $total ?></strong> cliente(s)</p>

<table class="table">
    <thead>
        <tr>
            <th>Razao Social</th>
            <th>CPF/CNPJ</th>
            <th>Tipo</th>
            <th>Cidade/UF</th>
            <th>Email</th>
            <th>NFSe</th>
            <th>Boleto</th>
            <th>Status</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($clientes)): ?>
            <tr><td colspan="9" class="muted center">Nenhum cliente encontrado.</td></tr>
        <?php else: foreach ($clientes as $c): ?>
            <tr>
                <td><strong><?= h($c['razao_social']) ?></strong><br><small style="color: var(--color-text-muted);"><?= h($c['nome_fantasia'] ?? '') ?></small></td>
                <td><?= h($c['cpf_cnpj'] ?? '-') ?></td>
                <td><?= $c['tipo_pessoa'] === 'F' ? 'Fisica' : 'Juridica' ?></td>
                <td><?= h(($c['cidade'] ?? '-') . '/' . ($c['uf'] ?? '-')) ?></td>
                <td><small><?= h($c['email'] ?? '-') ?></small></td>
                <td><?= (int)$c['emite_nfse'] === 1 ? '<span class="badge badge-success">Sim</span>' : '<span class="badge badge-default">Nao</span>' ?></td>
                <td><?= (int)$c['emite_boleto'] === 1 ? '<span class="badge badge-info">Sim</span>' : '<span class="badge badge-default">Nao</span>' ?></td>
                <td>
                    <span class="badge badge-<?= (int)$c['ativo'] === 1 ? 'success' : 'default' ?>">
                        <?= (int)$c['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td><a href="cliente_ver.php?id=<?= (int)$c['cliente_financeiro_id'] ?>" class="btn btn-sm">Ver</a></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
