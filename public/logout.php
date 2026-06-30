<?php
require __DIR__ . '/bootstrap.php';
Auth::logout();
header('Location: login.php');
exit;
