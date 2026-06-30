<?php /** @var array $cliente */ /** @var array $emailsNfse */ /** @var array $emailsBoleto */ ?>
<div class="page-header">
    <h1><?= h($cliente['razao_social']) ?></h1>
    <a href="clientes_listar.php" class="btn">Voltar</a>
</div>

<div class="info-box">
    <strong>Read-only:</strong> Visualizacao do cliente a partir do Financeiro. Edicao via
    <a href="http://192.168.70.45/financeiro/cliente_form.php?id=<?= (int)$cliente['cliente_financeiro_id'] ?>" target="_blank">/financeiro</a>.
</div>

<div class="cards-grid">
    <div class="card">
        <h3>Documento</h3>
        <div class="value" style="font-size: 18px;"><?= h($cliente['cpf_cnpj']) ?></div>
        <div class="label"><?= $cliente['tipo_pessoa'] === 'F' ? 'CPF (Pessoa Fisica)' : 'CNPJ (Pessoa Juridica)' ?></div>
    </div>
    <div class="card">
        <h3>Tipo</h3>
        <div class="value" style="font-size: 18px;"><?= $cliente['tipo_pessoa'] === 'F' ? 'Fisica' : 'Juridica' ?></div>
    </div>
    <div class="card">
        <h3>Endereco</h3>
        <div class="value" style="font-size: 14px;"><?= h(($cliente['endereco'] ?? '-') . ', ' . ($cliente['numero'] ?? 'S/N')) ?></div>
        <div class="label"><?= h(($cliente['bairro'] ?? '') . ' - ' . ($cliente['cidade'] ?? '') . '/' . ($cliente['uf'] ?? '')) ?></div>
    </div>
    <div class="card">
        <h3>CEP</h3>
        <div class="value" style="font-size: 18px;"><?= h($cliente['cep'] ?? '-') ?></div>
    </div>
    <div class="card">
        <h3>Telefone</h3>
        <div class="value" style="font-size: 18px;"><?= h($cliente['telefone'] ?? '-') ?></div>
    </div>
    <div class="card">
        <h3>Contato</h3>
        <div class="value" style="font-size: 14px;"><?= h($cliente['contato'] ?? '-') ?></div>
    </div>
    <div class="card">
        <h3>Emite NFSe</h3>
        <div class="value" style="font-size: 18px;"><?= (int)$cliente['emite_nfse'] === 1 ? 'Sim' : 'Nao' ?></div>
    </div>
    <div class="card">
        <h3>Emite Boleto</h3>
        <div class="value" style="font-size: 18px;"><?= (int)$cliente['emite_boleto'] === 1 ? 'Sim' : 'Nao' ?></div>
    </div>
    <div class="card">
        <h3>Vencimento</h3>
        <div class="value" style="font-size: 18px;">
            <?php if ($cliente['dia_vencimento']): ?>
                Dia <?= (int)$cliente['dia_vencimento'] ?>
                <small>(<?= h($cliente['tipo_vencimento'] ?? '') ?>)</small>
            <?php else: ?>
                -
            <?php endif; ?>
        </div>
    </div>
</div>

<h2 style="margin-top: 30px; font-size: 1.1rem;">E-mails para envio de NFSe (<?= count($emailsNfse) ?>)</h2>
<?php if (empty($emailsNfse)): ?>
    <p style="color: var(--color-text-muted);">Nenhum e-mail cadastrado para NFSe.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($emailsNfse as $email): ?>
            <li style="padding: 4px 0;"><?= h($email) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2 style="margin-top: 20px; font-size: 1.1rem;">E-mails para envio de Boleto (<?= count($emailsBoleto) ?>)</h2>
<?php if (empty($emailsBoleto)): ?>
    <p style="color: var(--color-text-muted);">Nenhum e-mail cadastrado para Boleto.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($emailsBoleto as $email): ?>
            <li style="padding: 4px 0;"><?= h($email) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
