<?php
session_start();

// Remover cookie de "lembrar-me"
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, '/');
}

// Destruir sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;