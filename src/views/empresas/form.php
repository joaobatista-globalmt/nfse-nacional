<?php /** @var array|null $empresa */ /** @var array $financeiroEmpresas */ ?>
<div class="page-header">
    <h1><?= $empresa ? 'Editar' : 'Nova' ?> Empresa Emissora</h1>
    <a href="empresas_listar.php" class="btn">Voltar</a>
</div>

<form method="post" action="empresa_salvar.php" class="form">
    <input type="hidden" name="id" value="<?= (int)($empresa['id'] ?? 0) ?>">

    <fieldset>
        <legend>Identificacao</legend>
        <div class="form-group">
            <label for="empresa_financeiro_id">Empresa do sistema Financeiro *</label>
            <select id="empresa_financeiro_id" name="empresa_financeiro_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($financeiroEmpresas as $fe): ?>
                    <option value="<?= (int)$fe['id'] ?>"
                        <?= (int)($empresa['empresa_financeiro_id'] ?? 0) === (int)$fe['id'] ? 'selected' : '' ?>>
                        <?= h($fe['razao_social']) ?> - CNPJ <?= formatarCnpj($fe['cnpj']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: var(--color-text-muted);">Vincula esta empresa NFSe a uma empresa ja cadastrada no sistema Financeiro.</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cnpj">CNPJ *</label>
                <input type="text" id="cnpj" name="cnpj" required maxlength="18" value="<?= h($empresa['cnpj'] ?? '') ?>" placeholder="00.000.000/0000-00">
            </div>
            <div class="form-group">
                <label for="razao_social">Razao Social *</label>
                <input type="text" id="razao_social" name="razao_social" required maxlength="200" value="<?= h($empresa['razao_social'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nome_fantasia">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" maxlength="100" value="<?= h($empresa['nome_fantasia'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="inscricao_municipal">Inscricao Municipal (IM)</label>
                <input type="text" id="inscricao_municipal" name="inscricao_municipal" maxlength="15" value="<?= h($empresa['inscricao_municipal'] ?? '') ?>" placeholder="IM de Rondonopolis/MT">
            </div>
        </div>

        <div class="form-group">
            <label for="cnae_principal">CNAE Principal</label>
            <input type="text" id="cnae_principal" name="cnae_principal" maxlength="10" value="<?= h($empresa['cnae_principal'] ?? '61.10-8-03') ?>" placeholder="61.10-8-03">
            <small style="color: var(--color-text-muted);">CNAE fiscal principal (10 chars: XX.XX-X-XX).</small>
        </div>
    </fieldset>

    <fieldset>
        <legend>Endereco do Emitente</legend>
        <div class="form-row">
            <div class="form-group" style="flex: 3;">
                <label for="logradouro">Logradouro</label>
                <input type="text" id="logradouro" name="logradouro" maxlength="255" value="<?= h($empresa['logradouro'] ?? '') ?>" placeholder="Av. Brasil, Rua X">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="numero">Numero</label>
                <input type="text" id="numero" name="numero" maxlength="60" value="<?= h($empresa['numero'] ?? '') ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento" maxlength="156" value="<?= h($empresa['complemento'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex: 2;">
                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro" maxlength="60" value="<?= h($empresa['bairro'] ?? '') ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="cMunicipio_ibge">Cod. IBGE (7 digitos)</label>
                <input type="text" id="cMunicipio_ibge" name="cMunicipio_ibge" maxlength="7" value="<?= h($empresa['cMunicipio_ibge'] ?? '5107602') ?>" placeholder="5107602 (Rondonopolis)">
                <small style="color: var(--color-text-muted);">5107602 = Rondonopolis/MT</small>
            </div>
            <div class="form-group" style="flex: 0 0 80px;">
                <label for="uf">UF</label>
                <input type="text" id="uf" name="uf" maxlength="2" value="<?= h($empresa['uf'] ?? 'MT') ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" maxlength="8" value="<?= h($empresa['cep'] ?? '') ?>" placeholder="78700000">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Regime Tributario e Ambiente</legend>
        <div class="form-row">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="optante_simples" value="1" <?= (!$empresa || (int)$empresa['optante_simples'] === 1) ? 'checked' : '' ?>>
                    Optante pelo Simples Nacional
                </label>
            </div>
            <div class="form-group">
                <label for="regime_especial_tributacao">Regime Especial de Tributacao Municipal</label>
                <select id="regime_especial_tributacao" name="regime_especial_tributacao">
                    <option value="0" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 0 ? 'selected' : '' ?>>0 - Nenhum</option>
                    <option value="1" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 1 ? 'selected' : '' ?>>1 - Ato Cooperado</option>
                    <option value="2" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 2 ? 'selected' : '' ?>>2 - Estimativa</option>
                    <option value="3" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 3 ? 'selected' : '' ?>>3 - Microempresa Municipal</option>
                    <option value="4" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 4 ? 'selected' : '' ?>>4 - Estimativa + Cooperativa</option>
                    <option value="5" <?= (int)($empresa['regime_especial_tributacao'] ?? 0) === 5 ? 'selected' : '' ?>>5 - Sociedade de Profissionais</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="ambiente">Ambiente</label>
                <select id="ambiente" name="ambiente">
                    <option value="homologacao" <?= ($empresa['ambiente'] ?? 'homologacao') === 'homologacao' ? 'selected' : '' ?>>Homologacao (testes)</option>
                    <option value="producao" <?= ($empresa['ambiente'] ?? '') === 'producao' ? 'selected' : '' ?>>Producao (real)</option>
                </select>
                <small style="color: var(--color-warning);"><strong>ATENCAO:</strong> Producao so apos todos os testes em homologacao.</small>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="ativo" value="1" <?= (!$empresa || $empresa['ativo']) ? 'checked' : '' ?>>
                    Ativa (pode emitir DPS)
                </label>
            </div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="empresas_listar.php" class="btn">Cancelar</a>
    </div>
</form>
