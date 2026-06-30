<?php /** @var array $empresas */ ?>
<div class="page-header">
    <h1>Empresas Emissoras de NFS-e</h1>
    <a href="empresa_form.php" class="btn btn-primary">+ Nova Empresa</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Razao Social</th>
            <th>CNPJ</th>
            <th>IM</th>
            <th>CNAE</th>
            <th>Regime</th>
            <th>Ambiente</th>
            <th>Status</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($empresas)): ?>
            <tr><td colspan="8" class="muted center">Nenhuma empresa cadastrada.</td></tr>
        <?php else: foreach ($empresas as $e): ?>
            <tr>
                <td><strong><?= h($e['razao_social']) ?></strong><br><small style="color: var(--color-text-muted);"><?= h($e['nome_fantasia'] ?? '') ?></small></td>
                <td><?= h(formatarCnpj($e['cnpj'])) ?></td>
                <td><?= h($e['inscricao_municipal'] ?? '-') ?></td>
                <td><code><?= h($e['cnae_principal'] ?? '-') ?></code></td>
                <td><?= (int)$e['optante_simples'] === 1 ? '<span class="badge badge-info">Simples</span>' : '<span class="badge badge-default">Outro</span>' ?></td>
                <td>
                    <span class="badge badge-<?= $e['ambiente'] === 'producao' ? 'danger' : 'warning' ?>">
                        <?= h($e['ambiente']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-<?= (int)$e['ativo'] === 1 ? 'success' : 'default' ?>">
                        <?= (int)$e['ativo'] === 1 ? 'Ativa' : 'Inativa' ?>
                    </span>
                </td>
                <td>
                    <a href="empresa_form.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm">Editar</a>
                    <form method="post" action="empresa_acao.php" style="display:inline;" onsubmit="return confirm('Excluir esta empresa e TODOS os certificados/DPS relacionados? Esta acao nao pode ser desfeita.');">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
