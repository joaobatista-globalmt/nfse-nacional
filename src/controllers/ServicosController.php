<?php
/**
 * ServicosController - Read-only via view cross-database
 * Lista os servicos CNAE/NBS do sistema Financeiro.
 */

declare(strict_types=1);

final class ServicosController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::getConnection();

        $busca = trim($_GET['busca'] ?? '');
        $apenasNfse = ($_GET['apenas_nfse'] ?? '1') !== '0';

        $sql = 'SELECT * FROM v_financeiro_servicos WHERE ativo = 1';
        $params = [];
        if ($apenasNfse) {
            // Por enquanto, nao ha filtro por emite_nfse aqui (e o servico nao tem essa coluna)
            // Futuramente filtraremos pelos servicos com nbs preenchido (apenas NBS aplicavel a NFSe)
            $sql .= ' AND nbs IS NOT NULL';
        }
        if ($busca !== '') {
            $sql .= ' AND (descricao LIKE ? OR cnae LIKE ? OR nbs LIKE ?)';
            $params[] = "%$busca%";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
        }
        $sql .= ' ORDER BY cnae, codigo_servico';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $servicos = $stmt->fetchAll();

        // Enriquece com info do LC116 (fazendo join in-memory)
        $codigos = array_filter(array_column($servicos, 'lc116_item'));
        $lc116Map = [];
        if (!empty($codigos)) {
            $placeholders = implode(',', array_fill(0, count($codigos), '?'));
            $stmt2 = $db->prepare("SELECT codigo, descricao FROM lc116_tributacao_nacional WHERE codigo IN ($placeholders)");
            $stmt2->execute(array_values($codigos));
            foreach ($stmt2->fetchAll() as $r) {
                $lc116Map[$r['codigo']] = $r['descricao'];
            }
        }

        layout('Servicos CNAE/NBS (do Financeiro)', 'servicos/index.php', [
            'servicos' => $servicos,
            'lc116Map' => $lc116Map,
            'busca' => $busca,
            'apenasNfse' => $apenasNfse,
            'total' => count($servicos),
        ]);
    }
}
