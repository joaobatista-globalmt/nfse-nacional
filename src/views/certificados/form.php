<?php /** @var array $empresas */ ?>
<div class="page-header">
    <h1>Upload de Certificado Digital A1</h1>
    <a href="certificados_listar.php" class="btn">Voltar</a>
</div>

<div class="info-box warning">
    <strong>Seguranca:</strong>
    O arquivo .pfx sera <strong>criptografado em disco</strong> (AES-256-CBC) e a senha do certificado
    NAO sera armazenada. Apenas os metadados (titular, validade, CNPJ) sao salvos.
    A chave mestra de criptografia fica em <code>NFSE_MASTER_KEY</code> nas env vars do PHP-FPM.
</div>

<form method="post" action="certificado_upload.php" enctype="multipart/form-data" class="form">
    <fieldset>
        <legend>Identificacao do Certificado</legend>
        <div class="form-row">
            <div class="form-group" style="flex: 2;">
                <label for="empresa_id">Empresa *</label>
                <select id="empresa_id" name="empresa_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($empresas as $e): ?>
                        <option value="<?= (int)$e['id'] ?>"><?= h($e['razao_social']) ?> (<?= formatarCnpj($e['cnpj']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="alias">Alias *</label>
                <input type="text" id="alias" name="alias" required maxlength="100" placeholder="Ex: Globalmt A1 Principal">
            </div>
            <div class="form-group" style="flex: 0 0 100px;">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="A1">A1 (arquivo)</option>
                    <option value="A3">A3 (token)</option>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Arquivo e Senha</legend>
        <div class="form-group">
            <label for="arquivo_pfx">Arquivo .pfx *</label>
            <input type="file" id="arquivo_pfx" name="arquivo_pfx" required accept=".pfx,.p12">
            <small style="color: var(--color-text-muted);">Tamanho maximo: 5MB. Formato: .pfx ou .p12</small>
        </div>
        <div class="form-group">
            <label for="senha_pfx">Senha do certificado *</label>
            <input type="password" id="senha_pfx" name="senha_pfx" required>
            <small style="color: var(--color-text-muted);">
                Sera usada para validar e extrair metadados do certificado.
                NAO sera armazenada.
            </small>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Upload e Criptografar</button>
        <a href="certificados_listar.php" class="btn">Cancelar</a>
    </div>
</form>
