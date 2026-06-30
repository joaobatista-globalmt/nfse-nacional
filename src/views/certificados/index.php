<?php /** @var array $certificados */ ?>
<div class="page-header">
    <h1>Certificados Digitais</h1>
    <a href="certificado_form.php" class="btn btn-primary">+ Novo Certificado</a>
</div>

<?php if (empty($certificados)): ?>
    <div class="info-box warning">
        Nenhum certificado cadastrado. Sem certificado A1 valido, voce nao conseguira transmitir DPS ao ADN.
    </div>
<?php else: ?>

<table class="table">
    <thead>
        <tr>
            <th>Empresa</th>
            <th>Alias</th>
            <th>Tipo</th>
            <th>Titular</th>
            <th>CNPJ/CPF</th>
            <th>Validade</th>
            <th>Status</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($certificados as $c): ?>
            <?php
            $hoje = date('Y-m-d');
            $diasParaVencer = (strtotime($c['validade_fim']) - strtotime($hoje)) / 86400;
            $vencendo = $diasParaVencer < 30 && $diasParaVencer > 0;
            $vencido = $diasParaVencer <= 0;
            ?>
            <tr>
                <td><?= h($c['empresa_nome']) ?></td>
                <td><strong><?= h($c['alias']) ?></strong></td>
                <td><span class="badge badge-info"><?= h($c['tipo']) ?></span></td>
                <td><small><?= h($c['titular']) ?></small></td>
                <td><code><?= h(formatarCnpj($c['cnpj_cpf'] ?? '')) ?></code></td>
                <td>
                    <?= dataIsoParaBr($c['validade_inicio']) ?> ate <strong><?= dataIsoParaBr($c['validade_fim']) ?></strong>
                    <?php if ($vencendo): ?>
                        <br><span class="badge badge-warning">Vence em <?= (int)$diasParaVencer ?> dias</span>
                    <?php elseif ($vencido): ?>
                        <br><span class="badge badge-danger">VENCIDO</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-<?= (int)$c['ativo'] === 1 ? 'success' : 'default' ?>">
                        <?= (int)$c['ativo'] === 1 ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>
                <td>
                    <form method="post" action="certificado_acao.php" style="display:inline;" onsubmit="return confirm('Excluir este certificado e o arquivo criptografado?');">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>
