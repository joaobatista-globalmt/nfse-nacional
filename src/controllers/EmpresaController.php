<?php
/**
 * EmpresaController - CRUD da empresa emissora de NFS-e
 *
 * Configuracoes por empresa:
 *  - CNPJ (do financeiro)
 *  - Inscricao Municipal (IM) - chave para a DPS
 *  - Endereco do emitente
 *  - Regime tributario (Simples Nacional, etc)
 *  - Ambiente (homologacao/producao)
 */

declare(strict_types=1);

final class EmpresaController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::getConnection();
        $stmt = $db->query('SELECT * FROM nfse_empresas ORDER BY ativo DESC, razao_social');
        $empresas = $stmt->fetchAll();
        layout('Empresas Emissoras', 'empresas/index.php', ['empresas' => $empresas]);
    }

    public function form(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $empresa = null;
        $db = Database::getConnection();
        if ($id > 0) {
            $stmt = $db->prepare('SELECT * FROM nfse_empresas WHERE id = ?');
            $stmt->execute([$id]);
            $empresa = $stmt->fetch();
            if (!$empresa) {
                Flash::set('erro', 'Empresa nao encontrada.');
                redirect('empresas_listar.php');
            }
        }
        // Carrega lista de empresas do financeiro (para o select)
        $financeiroEmpresas = $db->query("
            SELECT id, razao_social, cnpj
            FROM financeiro.empresas
            WHERE ativo = 1
            ORDER BY razao_social
        ")->fetchAll();
        layout($empresa ? 'Editar Empresa' : 'Nova Empresa', 'empresas/form.php', [
            'empresa' => $empresa,
            'financeiroEmpresas' => $financeiroEmpresas,
        ]);
    }

    public function salvar(): void
    {
        Auth::require();
        $id = (int)($_POST['id'] ?? 0);

        // Validacoes
        $cnpj = preg_replace('/\D/', '', (string)($_POST['cnpj'] ?? ''));
        if (strlen($cnpj) !== 14 || !validarCnpj($cnpj)) {
            Flash::set('erro', 'CNPJ invalido: ' . $cnpj);
            redirect('empresa_form.php' . ($id ? "?id=$id" : ''));
        }
        if (empty(trim($_POST['razao_social'] ?? ''))) {
            Flash::set('erro', 'Razao Social obrigatoria.');
            redirect('empresa_form.php' . ($id ? "?id=$id" : ''));
        }
        $empresaFinId = (int)($_POST['empresa_financeiro_id'] ?? 0);
        if ($empresaFinId <= 0) {
            Flash::set('erro', 'Selecione a empresa do sistema financeiro correspondente.');
            redirect('empresa_form.php' . ($id ? "?id=$id" : ''));
        }
        error_log('[NFSe Empresa] POST dados: ' . json_encode($_POST));

        $dados = [
            'empresa_financeiro_id'  => $empresaFinId,
            'cnpj'                    => $cnpj,
            'razao_social'            => trim($_POST['razao_social']),
            'nome_fantasia'           => trim($_POST['nome_fantasia'] ?? '') ?: null,
            'inscricao_municipal'     => trim($_POST['inscricao_municipal'] ?? '') ?: null,
            'cnae_principal'          => trim($_POST['cnae_principal'] ?? '') ?: null,
            'logradouro'              => trim($_POST['logradouro'] ?? '') ?: null,
            'numero'                  => trim($_POST['numero'] ?? '') ?: null,
            'complemento'             => trim($_POST['complemento'] ?? '') ?: null,
            'bairro'                  => trim($_POST['bairro'] ?? '') ?: null,
            'cMunicipio_ibge'         => trim($_POST['cMunicipio_ibge'] ?? '') ?: null,
            'uf'                      => strtoupper(trim($_POST['uf'] ?? '')) ?: null,
            'cep'                     => preg_replace('/\D/', '', (string)($_POST['cep'] ?? '')) ?: null,
            'optante_simples'         => isset($_POST['optante_simples']) ? 1 : 0,
            'regime_especial_tributacao' => (int)($_POST['regime_especial_tributacao'] ?? 0),
            'ambiente'                => ($_POST['ambiente'] ?? 'homologacao') === 'producao' ? 'producao' : 'homologacao',
            'ativo'                   => isset($_POST['ativo']) ? 1 : 0,
        ];

        $db = Database::getConnection();
        try {
            if ($id > 0) {
                $sql = 'UPDATE nfse_empresas SET ' . implode(', ', array_map(fn($k) => "$k=?", array_keys($dados))) .
                       ' WHERE id=?';
                $params = array_values($dados);
                $params[] = $id;
                $db->prepare($sql)->execute($params);
                Flash::set('sucesso', 'Empresa atualizada.');
            } else {
                $cols = implode(', ', array_keys($dados));
                $placeholders = implode(', ', array_fill(0, count($dados), '?'));
                $db->prepare("INSERT INTO nfse_empresas ($cols) VALUES ($placeholders)")
                    ->execute(array_values($dados));
                Flash::set('sucesso', 'Empresa criada.');
            }
        } catch (PDOException $e) {
            error_log('[NFSe Empresa] PDO Erro: ' . $e->getMessage());
            if (str_contains($e->getMessage(), 'uk_cnpj')) {
                Flash::set('erro', 'Ja existe uma empresa com este CNPJ.');
            } else {
                Flash::set('erro', 'Erro ao salvar: ' . $e->getMessage());
            }
        }
        redirect('empresas_listar.php');
    }

    public function excluir(): void
    {
        Auth::require();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('empresas_listar.php'); }
        $db = Database::getConnection();
        try {
            $db->prepare('DELETE FROM nfse_empresas WHERE id = ?')->execute([$id]);
            Flash::set('sucesso', 'Empresa removida (e todos os certificados/dps relacionados).');
        } catch (PDOException $e) {
            Flash::set('erro', 'Erro ao excluir.');
        }
        redirect('empresas_listar.php');
    }
}
