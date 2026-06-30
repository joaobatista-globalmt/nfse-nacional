<?php
/**
 * Flash - Mensagens de feedback entre requests
 */
declare(strict_types=1);

final class Flash
{
    public static function set(string $tipo, string $msg): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['nfse_flash'][] = ['tipo' => $tipo, 'msg' => $msg];
    }

    public static function get(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['nfse_flash'] ?? [];
        unset($_SESSION['nfse_flash']);
        return $flash;
    }
}
