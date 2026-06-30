<?php
/** @var array $empresas */ /** @var array $nbs */ /** @var array $lc116 */ /** @var array $cidades */
/** @var array $cst_ibs_cbs */ /** @var array $tipos_retencao */ /** @var array $tipos_destinatario */
/** @var array|null $dps */ /** @var array $dados */
$isEdit = !empty($dps);
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

// Pre-fill
$empresaId   = $old['empresa_id']   ?? ($dps['empresa_id']   ?? ($empresas[0]['id'] ?? 0));
$tomadorTipo = $old['tomador_tipo'] ?? ($dados['tomador']['tipo']      ?? 'J');
$tomadorDoc  = $old['tomador_documento'] ?? ($dados['tomador']['documento'] ?? '');
$tomadorNome = $old['tomador_nome'] ?? ($dados['tomador']['nome']     ?? '');
$tomadorEmail= $old['tomador_email']?? ($dados['tomador']['email']    ?? '');
$tomadorTel  = $old['tomador_telefone'] ?? ($dados['tomador']['telefone'] ?? '');
$tomadorEnd  = $old['tomador_endereco'] ?? ($dados['tomador']['endereco'] ?? '');
$tomadorNum  = $old['tomador_numero']   ?? ($dados['tomador']['numero']   ?? '');
$tomadorBai  = $old['tomador_bairro']   ?? ($dados['tomador']['bairro']   ?? '');
$tomadorCep  = $old['tomador_cep']      ?? ($dados['tomador']['cep']      ?? '');
$tomadorCid  = $old['tomador_cidade_ibge'] ?? ($dados['tomador']['cidade_ibge'] ?? '');
$tomadorUf   = $old['tomador_uf']       ?? ($dados['tomador']['uf']       ?? '');

$servicoNbs   = $old['servico_nbs']    ?? ($dados['servico']['nbs']         ?? '');
$servicoLc116 = $old['servico_lc116']  ?? ($dados['servico']['lc116']       ?? '');
$servicoDesc  = $old['servico_descricao'] ?? ($dados['servico']['descricao'] ?? '');
$servicoCst   = $old['servico_cst_ibs_cbs'] ?? ($dados['servico']['cst_ibs_cbs'] ?? '');
$discriminacao= $old['discriminacao']  ?? ($dados['servico']['discriminacao'] ?? '');

$valorServico = $old['valor_servico']            ?? ($dados['valores']['valor_servico'] ?? '');
$valorDeducoes= $old['valor_deducoes']           ?? ($dados['valores']['valor_deducoes'] ?? '');
$valorDesconto= $old['valor_desconto_incondicional'] ?? ($dados['valores']['valor_desconto_incondicional'] ?? '');
$aliqIss      = $old['aliquota_iss']             ?? ($dados['valores']['aliquota_iss'] ?? '5,00');
$issRetido    = (int)($old['iss_retido']         ?? ($dados['valores']['iss_retido'] ?? 0));
$tipoRetencao = $old['tipo_retencao']            ?? ($dados['valores']['tipo_retencao'] ?? '');
$dataCompet   = $old['data_competencia']         ?? ($dados['competencia'] ?? date('Y-m-d'));

// Formata valores para exibição
$fmtMoeda = function($v) {
    if ($v === '' || $v === null) return '';
    return number_format((float)$v, 2, ',', '.');
};
?>
<div class="page-header">
    <h1><?= $isEdit ? 'Editar DPS #' . (int)$dps['id'] : 'Nova DPS' ?></h1>
    <div>
        <a href="dps_listar.php" class="btn">← Voltar</a>
    </div>
</div>

<?php if ($currentFlash && $currentFlash['tipo'] === 'erro'): ?>
    <div class="alert alert-error"><?= $currentFlash['msg'] ?></div>
<?php endif; ?>

<form method="post" action="dps_salvar.php" id="dps-form">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$dps['id'] : 0 ?>">

    <!-- EMPRESA -->
    <fieldset class="form-section">
        <legend>1. Empresa Emissora</legend>
        <div class="form-group">
            <label for="empresa_id">Empresa *</label>
            <select id="empresa_id" name="empresa_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= (int)$e['id'] ?>" <?= $empresaId == $e['id'] ? 'selected' : '' ?>>
                        <?= h($e['razao_social']) ?> - <?= h(formatarCnpj($e['cnpj'])) ?>
                        (IM: <?= h($e['inscricao_municipal'] ?: 'sem IM') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <!-- TOMADOR -->
    <fieldset class="form-section">
        <legend>2. Tomador (Cliente)</legend>
        <div class="form-row">
            <div class="form-group" style="max-width: 100px;">
                <label for="tomador_tipo">Tipo *</label>
                <select id="tomador_tipo" name="tomador_tipo" required>
                    <option value="J" <?= $tomadorTipo === 'J' ? 'selected' : '' ?>>Juridica</option>
                    <option value="F" <?= $tomadorTipo === 'F' ? 'selected' : '' ?>>Fisica</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="tomador_documento">CPF/CNPJ *</label>
                <input type="text" id="tomador_documento" name="tomador_documento" value="<?= h($tomadorDoc) ?>" placeholder="Apenas numeros" maxlength="14" required>
                <small class="form-hint"><a href="clientes_listar.php" target="_blank">Buscar cliente do Financeiro</a> e copiar CPF/CNPJ.</small>
            </div>
        </div>
        <div class="form-group">
            <label for="tomador_nome">Nome / Razao Social *</label>
            <input type="text" id="tomador_nome" name="tomador_nome" value="<?= h($tomadorNome) ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="tomador_email">E-mail</label>
                <input type="email" id="tomador_email" name="tomador_email" value="<?= h($tomadorEmail) ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="tomador_telefone">Telefone</label>
                <input type="text" id="tomador_telefone" name="tomador_telefone" value="<?= h($tomadorTel) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="tomador_cep">CEP</label>
            <input type="text" id="tomador_cep" name="tomador_cep" value="<?= h($tomadorCep) ?>" maxlength="8" placeholder="Apenas numeros">
        </div>
        <div class="form-group">
            <label for="tomador_endereco">Endereco</label>
            <input type="text" id="tomador_endereco" name="tomador_endereco" value="<?= h($tomadorEnd) ?>">
        </div>
        <div class="form-row">
            <div class="form-group" style="max-width: 120px;">
                <label for="tomador_numero">Numero</label>
                <input type="text" id="tomador_numero" name="tomador_numero" value="<?= h($tomadorNum) ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="tomador_bairro">Bairro</label>
                <input type="text" id="tomador_bairro" name="tomador_bairro" value="<?= h($tomadorBai) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex: 3;">
                <label for="tomador_cidade_select">Cidade (IBGE)</label>
                <select id="tomador_cidade_select" data-target="tomador_cidade_ibge" data-uf="tomador_uf">
                    <option value="">Selecione...</option>
                    <?php foreach ($cidades as $cid): ?>
                        <option value="<?= h($cid['codigo_ibge']) ?>" data-uf="<?= h($cid['uf']) ?>" <?= $tomadorCid == $cid['codigo_ibge'] ? 'selected' : '' ?>>
                            <?= h($cid['nome']) ?> / <?= h($cid['uf']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="tomador_cidade_ibge" name="tomador_cidade_ibge" value="<?= h($tomadorCid) ?>">
            </div>
            <div class="form-group" style="max-width: 100px;">
                <label for="tomador_uf">UF</label>
                <input type="text" id="tomador_uf" name="tomador_uf" value="<?= h($tomadorUf) ?>" maxlength="2" readonly>
            </div>
        </div>
    </fieldset>

    <!-- SERVIÇO -->
    <fieldset class="form-section">
        <legend>3. Servico Prestado</legend>
        <div class="form-group">
            <label for="servico_descricao">Descricao do Servico *</label>
            <input type="text" id="servico_descricao" name="servico_descricao" value="<?= h($servicoDesc) ?>" required placeholder="Ex: Servico de internet banda larga mensal">
        </div>
        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="servico_nbs">Codigo NBS (1.03.x.xx)</label>
                <select id="servico_nbs" name="servico_nbs">
                    <option value="">Selecione...</option>
                    <?php foreach ($nbs as $n): ?>
                        <option value="<?= h($n['codigo']) ?>" <?= $servicoNbs == $n['codigo'] ? 'selected' : '' ?>>
                            <?= h($n['codigo']) ?> - <?= h($n['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-hint">NBS = Nomenclatura Brasileira de Servicos (federacao)</small>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="servico_lc116">Codigo LC 116 (municipal)</label>
                <select id="servico_lc116" name="servico_lc116">
                    <option value="">Selecione...</option>
                    <?php foreach ($lc116 as $l): ?>
                        <option value="<?= h($l['codigo']) ?>" <?= $servicoLc116 == $l['codigo'] ? 'selected' : '' ?>>
                            <?= h($l['codigo']) ?> - <?= h(substr($l['descricao'], 0, 80)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="servico_cst_ibs_cbs">CST IBS/CBS (Reforma Tributaria)</label>
            <select id="servico_cst_ibs_cbs" name="servico_cst_ibs_cbs">
                <option value="">Selecione...</option>
                <?php foreach ($cst_ibs_cbs as $c): ?>
                    <option value="<?= h($c['codigo']) ?>" <?= $servicoCst == $c['codigo'] ? 'selected' : '' ?>>
                        <?= h($c['codigo']) ?> - <?= h(substr($c['descricao'], 0, 80)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="discriminacao">Discriminacao completa (opcional)</label>
            <textarea id="discriminacao" name="discriminacao" rows="3" placeholder="Detalhes adicionais que aparecerao na NFS-e"><?= h($discriminacao) ?></textarea>
        </div>
    </fieldset>

    <!-- VALORES -->
    <fieldset class="form-section">
        <legend>4. Valores e Tributos</legend>
        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="valor_servico">Valor do Servico (R$) *</label>
                <input type="text" id="valor_servico" name="valor_servico" value="<?= h($fmtMoeda($valorServico)) ?>" required class="moeda" placeholder="0,00">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="valor_deducoes">Deducoes (R$)</label>
                <input type="text" id="valor_deducoes" name="valor_deducoes" value="<?= h($fmtMoeda($valorDeducoes)) ?>" class="moeda" placeholder="0,00">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="valor_desconto_incondicional">Desconto Incondicional (R$)</label>
                <input type="text" id="valor_desconto_incondicional" name="valor_desconto_incondicional" value="<?= h($fmtMoeda($valorDesconto)) ?>" class="moeda" placeholder="0,00">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="aliquota_iss">Aliquota ISS (%) *</label>
                <input type="text" id="aliquota_iss" name="aliquota_iss" value="<?= h($aliqIss) ?>" required placeholder="5,00">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="iss_retido">ISS Retido?</label>
                <select id="iss_retido" name="iss_retido">
                    <option value="0" <?= $issRetido === 0 ? 'selected' : '' ?>>Nao</option>
                    <option value="1" <?= $issRetido === 1 ? 'selected' : '' ?>>Sim</option>
                </select>
            </div>
            <div class="form-group" style="flex: 2;">
                <label for="tipo_retencao">Tipo de Retencao</label>
                <select id="tipo_retencao" name="tipo_retencao">
                    <option value="">Sem retencao</option>
                    <?php foreach ($tipos_retencao as $t): ?>
                        <option value="<?= h($t['codigo']) ?>" <?= $tipoRetencao == $t['codigo'] ? 'selected' : '' ?>>
                            <?= h($t['codigo']) ?> - <?= h($t['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group" style="max-width: 250px;">
            <label for="data_competencia">Data de Competencia *</label>
            <input type="date" id="data_competencia" name="data_competencia" value="<?= h($dataCompet) ?>" required>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" name="acao" value="salvar" class="btn">💾 Salvar como Rascunho</button>
        <button type="submit" name="acao" value="finalizar" class="btn btn-primary">✅ Finalizar DPS</button>
        <a href="dps_listar.php" class="btn">Cancelar</a>
    </div>
</form>

<style>
.form-section {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}
.form-section legend {
    font-weight: 600;
    font-size: 1.05rem;
    padding: 0 8px;
    color: var(--color-primary);
}
.form-row {
    display: flex;
    gap: 12px;
    margin-bottom: 0;
    flex-wrap: wrap;
}
.form-row .form-group { flex: 1; min-width: 150px; }
.form-hint { color: var(--color-text-muted); font-size: 12px; display: block; margin-top: 4px; }
.form-actions {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-top: 1px solid var(--color-border);
    margin-top: 16px;
}
.card-stat {
    background: var(--color-surface);
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--color-border);
    text-align: center;
    transition: transform 0.1s;
}
.card-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.card-stat-label { font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.card-stat-value { font-size: 24px; font-weight: 700; margin-top: 4px; }
</style>

<script>
// Preenche UF quando seleciona cidade
document.getElementById('tomador_cidade_select').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('tomador_uf').value = opt.getAttribute('data-uf') || '';
    document.getElementById('tomador_cidade_ibge').value = this.value;
});
</script>
