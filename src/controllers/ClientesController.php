<?php
/**
 * ClientesController - Read-only via view cross-database
 * Lista os clientes do sistema Financeiro que terao NFS-e emitida.
 */

declare(strict_types=1);

final class ClientesController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::getConnection();

        $filtroAtivo = ($_GET['apenas_ativos'] ?? '1') !== '0';
        $busca = trim($_GET['busca'] ?? '');

        $sql = 'SELECT * FROM v_financeiro_clientes WHERE 1=1';
        $params = [];
        if ($filtroAtivo) {
            $sql .= ' AND ativo = 1';
        }
        if ($busca !== '') {
            $sql .= ' AND (razao_social LIKE ? OR cpf_cnpj LIKE ?)';
            $params[] = "%$busca%";
            $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY razao_social LIMIT 200';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll();

        layout('Clientes (do Financeiro)', 'clientes/index.php', [
            'clientes' => $clientes,
            'busca' => $busca,
            'filtroAtivo' => $filtroAtivo,
            'total' => count($clientes),
        ]);
    }

    public function ver(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { redirect('clientes_listar.php'); }
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT * FROM v_financeiro_clientes WHERE cliente_financeiro_id = ?');
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
        if (!$cliente) {
            Flash::set('erro', 'Cliente nao encontrado no Financeiro.');
            redirect('clientes_listar.php');
        }

        // Carrega e-mails NFSe/Boleto
        $emailsNfse = $db->prepare('SELECT email FROM v_financeiro_cliente_emails_nfse WHERE cliente_financeiro_id = ?');
        $emailsNfse->execute([$id]);
        $emailsNfse = $emailsNfse->fetchAll(PDO::FETCH_COLUMN);

        $emailsBoleto = $db->prepare('SELECT email FROM v_financeiro_cliente_emails_boleto WHERE cliente_financeiro_id = ?');
        $emailsBoleto->execute([$id]);
        $emailsBoleto = $emailsBoleto->fetchAll(PDO::FETCH_COLUMN);

        layout('Cliente #' . $id, 'clientes/ver.php', [
            'cliente' => $cliente,
            'emailsNfse' => $emailsNfse,
            'emailsBoleto' => $emailsBoleto,
        ]);
    }
}
