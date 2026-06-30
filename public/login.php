<?php
require __DIR__ . '/bootstrap.php';
Auth::start();

if (Auth::isLogged()) {
    header('Location: index.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (Auth::login($email, $senha)) {
        header('Location: index.php');
        exit;
    }
    $erro = 'E-mail ou senha invalidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Login - Sistema NFS-e</title>
    <link rel="stylesheet" href="assets/nfse.css?v=1">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-header">
            <h1>SISTEMA NFS-e</h1>
            <p style="font-size: 12px; opacity: 0.9; margin-top: 4px;">Ambiente de HOMOLOGACAO</p>
        </div>
        <div class="login-body">
            <?php if ($erro): ?>
                <div class="flash flash-erro"><?= h($erro) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" required autofocus value="<?= h($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required>
                </div>
                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>
        <div style="text-align: center; padding: 1rem; color: var(--color-text-muted); font-size: 12px;">
            Homologacao - Sefin Nacional NFS-e
        </div>
    </div>
</body>
</html>
