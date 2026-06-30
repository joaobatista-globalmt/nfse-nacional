<?php
/**
 * Layout: header comum
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titulo ?? 'NFSe Nacional') ?> - Sistema NFS-e</title>
    <link rel="stylesheet" href="assets/nfse.css?v=1">
</head>
<body>
    <?php if (!empty($currentFlash)): ?>
        <div id="flash-container">
            <?php foreach ($currentFlash as $f): ?>
                <div class="flash flash-<?= h($f['tipo']) ?>">
                    <?= h($f['msg']) ?>
                    <button type="button" class="flash-close" onclick="this.parentElement.remove()">x</button>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
            setTimeout(() => {
                document.querySelectorAll('.flash').forEach(el => el.remove());
            }, 5000);
        </script>
    <?php endif; ?>
    <main class="content">
