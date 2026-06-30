<?php
/**
 * Helper - Funções globais e autoloader
 *
 * Define constantes e funções utilitárias comuns em todo o sistema NFSe.
 * Espelha o Helper.php do sistema financeiro, adaptado.
 */

declare(strict_types=1);

// Constantes de path
define('NFSE_ROOT', dirname(__DIR__, 2));
define('NFSE_SRC', NFSE_ROOT . '/src');
define('NFSE_PUBLIC', NFSE_ROOT . '/public');

// Autoloader simples
spl_autoload_register(function (string $classe): void {
    $paths = [
        NFSE_SRC . '/lib/' . $classe . '.php',
        NFSE_SRC . '/controllers/' . $classe . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

/**
 * Inclui uma view.
 */
function view(string $caminho, array $vars = []): void
{
    $arquivo = NFSE_SRC . '/views/' . $caminho;
    if (!file_exists($arquivo)) {
        throw new RuntimeException("View não encontrada: $caminho");
    }
    extract($vars, EXTR_SKIP);
    require $arquivo;
}

/**
 * Layout: header + navbar + content + footer.
 */
function layout(string $titulo, string $view, array $vars = []): void
{
    extract($vars, EXTR_OVERWRITE);
    $currentUser = Auth::user();
    $currentFlash = Flash::get();
    require NFSE_SRC . '/views/layout/header.php';
    require NFSE_SRC . '/views/layout/navbar.php';
    require NFSE_SRC . '/views/' . $view;
    require NFSE_SRC . '/views/layout/footer.php';
}

/**
 * Redirect com flash opcional.
 */
function redirect(string $url, ?string $flashTipo = null, ?string $flashMsg = null): void
{
    if ($flashTipo && $flashMsg) {
        Flash::set($flashTipo, $flashMsg);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitiza input.
 */
function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Data BR (dd/mm/yyyy) -> ISO (yyyy-mm-dd).
 */
function dataBrParaIso(?string $data): ?string
{
    if (!$data) return null;
    $partes = explode('/', $data);
    if (count($partes) !== 3) return null;
    return $partes[2] . '-' . str_pad($partes[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($partes[0], 2, '0', STR_PAD_LEFT);
}

/**
 * Data ISO (yyyy-mm-dd) -> BR (dd/mm/yyyy).
 */
function dataIsoParaBr(?string $data): ?string
{
    if (!$data) return null;
    $partes = explode('-', $data);
    if (count($partes) !== 3) return null;
    return $partes[2] . '/' . $partes[1] . '/' . $partes[0];
}

/**
 * Formata CNPJ: 00.000.000/0000-00
 */
function formatarCnpj(?string $cnpj): string
{
    if (!$cnpj) return '-';
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14) return $cnpj;
    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

/**
 * Formata CPF: 000.000.000-00
 */
function formatarCpf(?string $cpf): string
{
    if (!$cpf) return '-';
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11) return $cpf;
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Valida CNPJ (algoritmo mod-11).
 */
function validarCnpj(string $cnpj): bool
{
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14) return false;
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
    $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($i = 0; $i < 12; $i++) $soma += $cnpj[$i] * $pesos1[$i];
    $resto = $soma % 11;
    $dv1 = $resto < 2 ? 0 : 11 - $resto;
    if ((int)$cnpj[12] !== $dv1) return false;
    $soma = 0;
    for ($i = 0; $i < 13; $i++) $soma += $cnpj[$i] * $pesos2[$i];
    $resto = $soma % 11;
    $dv2 = $resto < 2 ? 0 : 11 - $resto;
    return (int)$cnpj[13] === $dv2;
}

/**
 * Valida CPF.
 */
function validarCpf(string $cpf): bool
{
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) $soma += $cpf[$i] * ($t + 1 - $i);
        $resto = ($soma * 10) % 11;
        $digito = $resto === 10 ? 0 : $resto;
        if ((int)$cpf[$t] !== $digito) return false;
    }
    return true;
}

/**
 * JSON encode seguro.
 */
function jsonEncode($v): string
{
    return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * JSON decode que retorna array (ou null).
 */
function jsonDecode(string $s): ?array
{
    $r = json_decode($s, true);
    return is_array($r) ? $r : null;
}
