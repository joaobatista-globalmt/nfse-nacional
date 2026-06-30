<?php
require __DIR__ . '/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    (new DpsController)->editar();
} else {
    (new DpsController)->novo();
}
