<?php
require __DIR__ . '/app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $_SESSION = [];
    session_destroy();
}
redirecionar('login.php');
