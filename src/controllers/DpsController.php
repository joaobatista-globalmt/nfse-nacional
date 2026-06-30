<?php
/**
 * DpsController - Fase 3: Emissao de DPS (Declaracao de Prestacao de Servico)
 *
 * Sem certificado/transmissao ainda (Fase 4). Cria/atualiza/exclui DPS
 * no banco como RASCUNHO. A estrutura completa vai no campo `dados_json`
 * (longtext) - formato flexivel para acomodar todas as tags do leiaute ADN.
 *
 * Status:
 *  - rascunho   : sendo editado
 *  - pronto     : validado, aguardando transmissao (Fase 4)
 *  - enviado    : DPS enviado a ADN (Fase 4)
 *  - processado : NFS-e autorizada
 *  - rejeitado  : ADN rejeitou
 *  - erro       : falha tecnica
 */

declare(strict_types=1);

final class DpsController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::getConnection();

        $status = $_GET['status'] ?? '';
        $busca = trim($_GET['busca'] ?? '');

        $sql = 'SELECT d.*, e.razao_social AS empresa_razao_social, e.cnpj AS empresa_cnpj
                FROM nfse_dps d
                JOIN nfse_empresas e ON e.id = d.empresa_id
                WHERE 1=1';
        $params = [];
        if ($status !== '' && in_array($status, ['rascunho','pronto','enviado','processado','rejeitado','erro'], true)) {
            $sql .= ' AND d.status = ?';
            $params[] = $status;
        }
        if ($busca !== '') {
            $sql .= ' AND (d.tomador_nome LIKE ? OR d.tomador_documento LIKE ? OR d.servico_descricao LIKE ? OR d.chave_acesso LIKE ?)';
            $params[] = "%$busca%";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY d.created_at DESC LIMIT 200';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $dps = $stmt->fetchAll();

        // Contadores por status
        $contadores = $db->query("SELECT status, COUNT(*) AS qtd FROM nfse_dps GROUP BY status")->fetchAll();

        layout('DPS - Declaracao de Prestacao de Servico', 'nfse/dps_listar.php', [
            'dps' => $dps,
            'busca' => $busca,
            'status' => $status,
            'contadores' => $contadores,
            'total' => count($dps),
        ]);
    }

    public function novo(): void
    {
        Auth::require();
        $db = Database::getConnection();

        $empresas = $db->query("SELECT id, cnpj, razao_social, inscricao_municipal, optante_simples, regime_especial_tributacao, ambiente FROM nfse_empresas WHERE ativo = 1 ORDER BY razao_social")->fetchAll();
        if (empty($empresas)) {
            Flash::set('erro', 'Cadastre uma empresa emissora antes de criar DPS.');
            redirect('empresas_listar.php');
        }

        // Carrega dados auxiliares para o form
        $nbs = $db->query("SELECT codigo, descricao FROM nbs_codigos ORDER BY codigo")->fetchAll();
        $lc116 = $db->query("SELECT codigo, descricao FROM lc116_tributacao_nacional ORDER BY codigo")->fetchAll();
        $cidades = $db->query("SELECT codigo AS codigo_ibge, nome, uf FROM ibge_municipios WHERE uf IN ('MT','SP','RJ','MG','PR','SC','RS','GO','MS','DF','BA','PE','CE','PA') ORDER BY nome LIMIT 5000")->fetchAll();
        $cst_ibs_cbs = $db->query("SELECT codigo, descricao FROM cst_ibs_cbs ORDER BY codigo")->fetchAll();
        $tipos_retencao = $db->query("SELECT codigo, descricao FROM tipos_retencao ORDER BY codigo")->fetchAll();
        $tipos_destinatario = $db->query("SELECT codigo, descricao FROM tipos_destinatario ORDER BY codigo")->fetchAll();

        layout('Nova DPS', 'nfse/dps_form.php', [
            'empresas' => $empresas,
            'nbs' => $nbs,
            'lc116' => $lc116,
            'cidades' => $cidades,
            'cst_ibs_cbs' => $cst_ibs_cbs,
            'tipos_retencao' => $tipos_retencao,
            'tipos_destinatario' => $tipos_destinatario,
            'dps' => null,
            'dados' => [],
        ]);
    }

    public function editar(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { redirect('dps_listar.php'); }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM nfse_dps WHERE id = ?');
        $stmt->execute([$id]);
        $dps = $stmt->fetch();
        if (!$dps) { Flash::set('erro', 'DPS nao encontrada.'); redirect('dps_listar.php'); }

        if (!in_array($dps['status'], ['rascunho', 'rejeitado'], true)) {
            Flash::set('erro', 'Apenas DPS em rascunho ou rejeitada podem ser editadas (status atual: ' . $dps['status'] . ').');
            redirect('dps_listar.php');
        }

        $empresas = $db->query("SELECT id, cnpj, razao_social, inscricao_municipal, optante_simples, regime_especial_tributacao, ambiente FROM nfse_empresas WHERE ativo = 1 ORDER BY razao_social")->fetchAll();
        $nbs = $db->query("SELECT codigo, descricao FROM nbs_codigos ORDER BY codigo")->fetchAll();
        $lc116 = $db->query("SELECT codigo, descricao FROM lc116_tributacao_nacional ORDER BY codigo")->fetchAll();
        $cidades = $db->query("SELECT codigo AS codigo_ibge, nome, uf FROM ibge_municipios WHERE uf IN ('MT','SP','RJ','MG','PR','SC','RS','GO','MS','DF','BA','PE','CE','PA') ORDER BY nome LIMIT 5000")->fetchAll();
        $cst_ibs_cbs = $db->query("SELECT codigo, descricao FROM cst_ibs_cbs ORDER BY codigo")->fetchAll();
        $tipos_retencao = $db->query("SELECT codigo, descricao FROM tipos_retencao ORDER BY codigo")->fetchAll();
        $tipos_destinatario = $db->query("SELECT codigo, descricao FROM tipos_destinatario ORDER BY codigo")->fetchAll();

        $dados = jsonDecode($dps['dados_json']) ?? [];

        layout('Editar DPS #' . $dps['id'], 'nfse/dps_form.php', [
            'empresas' => $empresas,
            'nbs' => $nbs,
            'lc116' => $lc116,
            'cidades' => $cidades,
            'cst_ibs_cbs' => $cst_ibs_cbs,
            'tipos_retencao' => $tipos_retencao,
            'tipos_destinatario' => $tipos_destinatario,
            'dps' => $dps,
            'dados' => $dados,
        ]);
    }

    public function salvar(): void
    {
        Auth::require();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('dps_listar.php');
        }

        $db = Database::getConnection();
        $id = (int)($_POST['id'] ?? 0);
        $acao = $_POST['acao'] ?? 'salvar'; // 'salvar' (rascunho) ou 'finalizar' (pronto)

        // Coleta dados
        $empresaId = (int)($_POST['empresa_id'] ?? 0);
        $tomadorDoc = preg_replace('/\D/', '', (string)($_POST['tomador_documento'] ?? ''));
        $tomadorTipo = $_POST['tomador_tipo'] ?? 'J'; // F ou J
        $tomadorNome = trim((string)($_POST['tomador_nome'] ?? ''));
        $tomadorEmail = trim((string)($_POST['tomador_email'] ?? ''));
        $tomadorTelefone = trim((string)($_POST['tomador_telefone'] ?? ''));
        $tomadorCidade = trim((string)($_POST['tomador_cidade_ibge'] ?? ''));
        $tomadorUf = trim((string)($_POST['tomador_uf'] ?? ''));
        $tomadorEndereco = trim((string)($_POST['tomador_endereco'] ?? ''));
        $tomadorNumero = trim((string)($_POST['tomador_numero'] ?? ''));
        $tomadorBairro = trim((string)($_POST['tomador_bairro'] ?? ''));
        $tomadorCep = preg_replace('/\D/', '', (string)($_POST['tomador_cep'] ?? ''));

        $servicoNbs = trim((string)($_POST['servico_nbs'] ?? ''));
        $servicoLc116 = trim((string)($_POST['servico_lc116'] ?? ''));
        $servicoDescricao = trim((string)($_POST['servico_descricao'] ?? ''));
        $servicoCstIbsCbs = trim((string)($_POST['servico_cst_ibs_cbs'] ?? ''));
        $valorServico = (float)str_replace(['.', ','], ['', '.'], (string)($_POST['valor_servico'] ?? '0'));
        $valorDeducoes = (float)str_replace(['.', ','], ['', '.'], (string)($_POST['valor_deducoes'] ?? '0'));
        $valorDesconto = (float)str_replace(['.', ','], ['', '.'], (string)($_POST['valor_desconto_incondicional'] ?? '0'));
        $aliqIss = (float)str_replace(',', '.', (string)($_POST['aliquota_iss'] ?? '0'));
        $issRetido = (int)($_POST['iss_retido'] ?? 0);
        $tipoRetencao = trim((string)($_POST['tipo_retencao'] ?? ''));
        $dataCompetencia = $_POST['data_competencia'] ?? date('Y-m-d');
        $discriminacao = trim((string)($_POST['discriminacao'] ?? ''));

        // Validação
        $erros = [];
        if ($empresaId <= 0) $erros[] = 'Selecione a empresa emissora.';
        if ($tomadorNome === '') $erros[] = 'Informe o nome/razao social do tomador.';
        if ($tomadorDoc === '' || ($tomadorTipo === 'J' && !validarCnpj($tomadorDoc)) || ($tomadorTipo === 'F' && !validarCpf($tomadorDoc))) {
            $erros[] = 'Documento do tomador (CPF/CNPJ) invalido.';
        }
        if ($servicoDescricao === '') $erros[] = 'Informe a descricao do servico.';
        if ($valorServico <= 0) $erros[] = 'Valor do servico deve ser maior que zero.';
        if ($aliqIss < 0 || $aliqIss > 100) $erros[] = 'Aliquota ISS invalida.';
        if ($dataCompetencia === '') $erros[] = 'Data de competencia obrigatoria.';

        if ($acao === 'finalizar' && empty($erros)) {
            // Validações extras para finalizar
            if ($tomadorCidade === '' || $tomadorUf === '') $erros[] = 'Cidade e UF do tomador obrigatorias para finalizar.';
            if ($servicoNbs === '') $erros[] = 'Codigo NBS obrigatorio para finalizar.';
        }

        if (!empty($erros)) {
            Flash::set('erro', implode('<br>', $erros));
            $_SESSION['form_old'] = $_POST;
            redirect($id > 0 ? "dps_form.php?id=$id" : 'dps_form.php');
        }

        // Monta o JSON completo
        $dados = [
            'empresa' => [
                'id' => $empresaId,
            ],
            'tomador' => [
                'tipo' => $tomadorTipo,
                'documento' => $tomadorDoc,
                'nome' => $tomadorNome,
                'email' => $tomadorEmail,
                'telefone' => $tomadorTelefone,
                'endereco' => $tomadorEndereco,
                'numero' => $tomadorNumero,
                'bairro' => $tomadorBairro,
                'cep' => $tomadorCep,
                'cidade_ibge' => $tomadorCidade,
                'uf' => $tomadorUf,
            ],
            'servico' => [
                'nbs' => $servicoNbs,
                'lc116' => $servicoLc116,
                'descricao' => $servicoDescricao,
                'cst_ibs_cbs' => $servicoCstIbsCbs,
                'discriminacao' => $discriminacao,
            ],
            'valores' => [
                'valor_servico' => $valorServico,
                'valor_deducoes' => $valorDeducoes,
                'valor_desconto_incondicional' => $valorDesconto,
                'base_calculo' => $valorServico - $valorDeducoes - $valorDesconto,
                'aliquota_iss' => $aliqIss,
                'valor_iss' => round(($valorServico - $valorDeducoes - $valorDesconto) * $aliqIss / 100, 2),
                'iss_retido' => $issRetido,
                'tipo_retencao' => $tipoRetencao,
            ],
            'competencia' => $dataCompetencia,
            'versao' => '1.0',
        ];

        $novoStatus = ($acao === 'finalizar') ? 'pronto' : 'rascunho';

        if ($id > 0) {
            // Update
            $upd = $db->prepare('
                UPDATE nfse_dps SET
                    empresa_id = ?, status = ?, dados_json = ?,
                    tomador_documento = ?, tomador_nome = ?,
                    servico_codigo = ?, servico_descricao = ?,
                    valor_servico = ?, data_competencia = ?,
                    updated_at = NOW()
                WHERE id = ? AND status IN ("rascunho", "rejeitado")
            ');
            $upd->execute([
                $empresaId, $novoStatus, jsonEncode($dados),
                $tomadorDoc, $tomadorNome,
                $servicoNbs, $servicoDescricao,
                $valorServico, $dataCompetencia,
                $id,
            ]);
            $dpsId = $id;
        } else {
            // Insert - gera numero sequencial
            $numero = (int)$db->query('SELECT COALESCE(MAX(numero), 0) + 1 FROM nfse_dps WHERE empresa_id = ' . (int)$empresaId)->fetchColumn();
            $user = Auth::user();

            $ins = $db->prepare('
                INSERT INTO nfse_dps
                (empresa_id, serie, numero, status, dados_json,
                 tomador_documento, tomador_nome, servico_codigo, servico_descricao,
                 valor_servico, data_competencia, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $ins->execute([
                $empresaId, '1', $numero, $novoStatus, jsonEncode($dados),
                $tomadorDoc, $tomadorNome, substr($servicoLc116 ?: $servicoNbs, 0, 6), $servicoDescricao,
                $valorServico, $dataCompetencia, $user['id'] ?? null,
            ]);
            $dpsId = (int)$db->lastInsertId();
        }

        unset($_SESSION['form_old']);
        Flash::set('sucesso', $id > 0 ? 'DPS atualizada.' : 'DPS criada.');
        redirect('dps_ver.php?id=' . $dpsId);
    }

    public function excluir(): void
    {
        Auth::require();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('dps_listar.php'); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('dps_listar.php'); }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT status FROM nfse_dps WHERE id = ?');
        $stmt->execute([$id]);
        $dps = $stmt->fetch();
        if (!$dps) { Flash::set('erro', 'DPS nao encontrada.'); redirect('dps_listar.php'); }

        if (!in_array($dps['status'], ['rascunho', 'rejeitado'], true)) {
            Flash::set('erro', 'Apenas DPS em rascunho ou rejeitada podem ser excluidas.');
            redirect('dps_listar.php');
        }

        $db->prepare('DELETE FROM nfse_dps WHERE id = ?')->execute([$id]);
        Flash::set('sucesso', 'DPS #' . $id . ' excluida.');
        redirect('dps_listar.php');
    }

    public function ver(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { redirect('dps_listar.php'); }

        $db = Database::getConnection();
        $stmt = $db->prepare('
            SELECT d.*, e.razao_social AS empresa_razao_social, e.cnpj AS empresa_cnpj,
                   e.inscricao_municipal AS empresa_im
            FROM nfse_dps d
            JOIN nfse_empresas e ON e.id = d.empresa_id
            WHERE d.id = ?
        ');
        $stmt->execute([$id]);
        $dps = $stmt->fetch();
        if (!$dps) { Flash::set('erro', 'DPS nao encontrada.'); redirect('dps_listar.php'); }

        $dados = jsonDecode($dps['dados_json']) ?? [];

        layout('DPS #' . $dps['id'], 'nfse/dps_ver.php', [
            'dps' => $dps,
            'dados' => $dados,
        ]);
    }
}
