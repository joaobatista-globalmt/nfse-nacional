<?php
/**
 * CertificadoController - Upload e gestao do Certificado Digital A1/A3
 *
 * O arquivo .pfx e criptografado em disco via Criptografia (AES-256-CBC).
 * A senha do .pfx nao eh armazenada (so eh usada uma vez pra validar e extrair metadados).
 */

declare(strict_types=1);

final class CertificadoController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::getConnection();
        $stmt = $db->query('
            SELECT c.*, e.razao_social AS empresa_nome
            FROM nfse_certificados c
            JOIN nfse_empresas e ON e.id = c.empresa_id
            ORDER BY c.ativo DESC, e.razao_social, c.alias
        ');
        $certs = $stmt->fetchAll();
        layout('Certificados Digitais', 'certificados/index.php', ['certificados' => $certs]);
    }

    public function form(): void
    {
        Auth::require();
        $db = Database::getConnection();
        $empresas = $db->query("SELECT id, razao_social, cnpj FROM nfse_empresas WHERE ativo = 1 ORDER BY razao_social")->fetchAll();
        layout('Upload de Certificado A1', 'certificados/form.php', ['empresas' => $empresas]);
    }

    public function upload(): void
    {
        Auth::require();

        $empresaId = (int)($_POST['empresa_id'] ?? 0);
        $alias     = trim((string)($_POST['alias'] ?? ''));
        $tipo      = ($_POST['tipo'] ?? 'A1') === 'A3' ? 'A3' : 'A1';
        $senhaPfx  = (string)($_POST['senha_pfx'] ?? '');

        if ($empresaId <= 0) { Flash::set('erro', 'Selecione a empresa.'); redirect('certificado_form.php'); }
        if ($alias === '')   { Flash::set('erro', 'Alias obrigatorio.'); redirect('certificado_form.php'); }
        if ($senhaPfx === ''){ Flash::set('erro', 'Senha do certificado obrigatoria.'); redirect('certificado_form.php'); }
        if (empty($_FILES['arquivo_pfx']['tmp_name'])) {
            Flash::set('erro', 'Arquivo .pfx nao enviado.'); redirect('certificado_form.php');
        }
        $tmp = $_FILES['arquivo_pfx']['tmp_name'];
        if ($_FILES['arquivo_pfx']['error'] !== UPLOAD_ERR_OK) {
            Flash::set('erro', 'Erro no upload: ' . $_FILES['arquivo_pfx']['error']); redirect('certificado_form.php');
        }
        if ($_FILES['arquivo_pfx']['size'] > 5 * 1024 * 1024) {
            Flash::set('erro', 'Arquivo maior que 5MB.'); redirect('certificado_form.php');
        }

        // Tenta abrir com a senha pra validar e extrair metadados
        $conteudo = file_get_contents($tmp);
        if ($conteudo === false) {
            Flash::set('erro', 'Nao foi possivel ler o arquivo.'); redirect('certificado_form.php');
        }
        $certs = [];
        if (!openssl_pkcs12_read($conteudo, $certs, $senhaPfx)) {
            Flash::set('erro', 'Senha do certificado invalida ou arquivo corrompido.');
            redirect('certificado_form.php');
        }

        // Extrai metadados
        $info = openssl_x509_parse($certs['cert']);
        if ($info === false) {
            Flash::set('erro', 'Nao foi possivel ler dados do certificado.');
            redirect('certificado_form.php');
        }
        $titular       = $info['subject']['CN'] ?? $info['subject']['O'] ?? '';
        $cnpjCpf       = preg_replace('/\D/', '', $info['subject']['serialNumber'] ?? '');
        $emissor       = $info['issuer']['CN'] ?? $info['issuer']['O'] ?? '';
        $validadeIni   = date('Y-m-d', $info['validFrom_time_t']);
        $validadeFim   = date('Y-m-d', $info['validTo_time_t']);
        $serialNumber  = $info['serialNumber'] ?? '';

        // Criptografa o arquivo
        try {
            $arquivoRel = Criptografia::criptografarArquivo($tmp, $empresaId, $alias);
        } catch (Throwable $e) {
            Flash::set('erro', 'Erro ao criptografar: ' . $e->getMessage());
            redirect('certificado_form.php');
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO nfse_certificados
                (empresa_id, alias, tipo, arquivo_path, titular, cnpj_cpf, emissor,
                 validade_inicio, validade_fim, serial_number, ativo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ');
        $stmt->execute([
            $empresaId, $alias, $tipo, $arquivoRel, $titular, $cnpjCpf, $emissor,
            $validadeIni, $validadeFim, $serialNumber
        ]);

        Flash::set('sucesso', 'Certificado importado e criptografado em disco. Valido ate ' . dataIsoParaBr($validadeFim));
        redirect('certificados_listar.php');
    }

    public function excluir(): void
    {
        Auth::require();
        $id = (int)($_POST['id'] ?? 0);
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT arquivo_path FROM nfse_certificados WHERE id = ?');
        $stmt->execute([$id]);
        $cert = $stmt->fetch();
        if ($cert) {
            $caminho = NFSE_ROOT . '/certs/' . $cert['arquivo_path'];
            if (file_exists($caminho)) @unlink($caminho);
            $db->prepare('DELETE FROM nfse_certificados WHERE id = ?')->execute([$id]);
            Flash::set('sucesso', 'Certificado removido.');
        }
        redirect('certificados_listar.php');
    }
}
