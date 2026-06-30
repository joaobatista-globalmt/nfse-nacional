<?php
/**
 * Database - Conexao com o banco nfse_nacional
 *
 * Usa as env vars do PHP-FPM (DB_NFSE_HOST, DB_NFSE_DB, etc)
 */

declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = getenv('DB_NFSE_HOST') ?: '127.0.0.1';
        $port = getenv('DB_NFSE_PORT') ?: '3306';
        $db   = getenv('DB_NFSE_DB')   ?: 'nfse_nacional';
        $user = getenv('DB_NFSE_USER') ?: 'nfse_app';
        $pass = getenv('DB_NFSE_PASS') ?: 'nfse_app_2026';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('[NFSe Database] Erro: ' . $e->getMessage());
            throw new RuntimeException('Erro de conexao com o banco NFSe.');
        }
        return self::$pdo;
    }
}
