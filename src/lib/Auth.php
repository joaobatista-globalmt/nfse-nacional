<?php
/**
 * Auth - Autenticação simplificada para o sistema NFSe
 *
 * O sistema NFSe compartilha usuarios com o sistema financeiro.
 * Para isso, valida a sessão PHP via cookie PHPSESSID (mesma sessão)
 * OU re-autentica com login proprio do NFSe.
 *
 * Por enquanto: login proprio (tabela usuarios do NFSe).
 */

declare(strict_types=1);

final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLogged(): bool
    {
        self::start();
        return isset($_SESSION['nfse_usuario_id']);
    }

    public static function require(): void
    {
        if (!self::isLogged()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function user(): ?array
    {
        self::start();
        if (!self::isLogged()) return null;
        $id = (int)$_SESSION['nfse_usuario_id'];
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM nfse_usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public static function login(string $email, string $senha): bool
    {
        self::start();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM nfse_usuarios WHERE email = ? AND ativo = 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u) return false;
        if (!password_verify($senha, $u['senha_hash'])) return false;
        $_SESSION['nfse_usuario_id'] = (int)$u['id'];
        $_SESSION['nfse_usuario_nome'] = $u['nome'];
        return true;
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['nfse_usuario_id'], $_SESSION['nfse_usuario_nome']);
        session_destroy();
    }
}
