<nav class="navbar">
    <div class="navbar-brand">
        <a href="index.php">SISTEMA NFS-e</a>
    </div>
    <ul class="navbar-menu">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="nfse_listar.php">NFS-e Emitidas</a></li>
        <li><a href="nfse_emitir.php">Nova DPS</a></li>
        <li class="dropdown">
            <a href="#">Cadastros ▾</a>
            <ul class="dropdown-menu">
                <li><a href="clientes_listar.php">Clientes (read-only)</a></li>
                <li><a href="servicos_listar.php">Servicos CNAE (read-only)</a></li>
                <li><a href="empresas_listar.php">Empresas Emissoras</a></li>
                <li><a href="certificados_listar.php">Certificados A1</a></li>
            </ul>
        </li>
        <li><a href="relatorios.php">Relatorios</a></li>
    </ul>
    <div class="navbar-right">
        <span class="user-info">
            <?= h($currentUser['nome'] ?? '') ?>
            <small>(<?= h($currentUser['perfil'] ?? '') ?>)</small>
        </span>
        <a href="logout.php" class="btn btn-sm">Sair</a>
    </div>
</nav>
