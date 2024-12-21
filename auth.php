<?php
session_start();
require_once 'functions.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Ativar logs de erro para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obter e limpar dados do formulário
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';
$lembrar = isset($_POST['lembrar']);

try {
    // Log para debug
    error_log("Tentativa de login - Email: $email");

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // Validar senha
    if (empty($senha)) {
        throw new Exception('Senha é obrigatória');
    }

    // Conectar ao banco
    $mysqli = getConnection();
    
    // Log para debug
    error_log("Conexão com banco estabelecida");

    // Buscar usuário
    $stmt = $mysqli->prepare("
        SELECT id, nome, email, senha, papel, ativo 
        FROM usuarios 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // Log para debug
    error_log("Usuário encontrado: " . print_r($usuario, true));

    // Verificar se usuário existe e está ativo
    if (!$usuario || !$usuario['ativo']) {
        throw new Exception('Usuário não encontrado ou inativo');
    }

    // Log para debug - senha fornecida e hash
    error_log("Senha fornecida (MD5): " . md5($senha));
    error_log("Senha no banco: " . $usuario['senha']);

    // Verificar senha
    if (md5($senha) !== $usuario['senha']) {
        throw new Exception('Senha incorreta');
    }

    // Log para debug
    error_log("Senha correta - criando sessão");

    // Criar sessão
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_name'] = $usuario['nome'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_role'] = $usuario['papel'];

    // Log para debug
    error_log("Sessão criada: " . print_r($_SESSION, true));

    // Se marcou "lembrar-me", criar cookie
    if ($lembrar) {
        $token = bin2hex(random_bytes(32));
        $expira = time() + (30 * 24 * 60 * 60); // 30 dias
        
        // Salvar token no banco
        $stmt = $mysqli->prepare("
            INSERT INTO tokens (usuario_id, token, expira) 
            VALUES (?, ?, FROM_UNIXTIME(?))
        ");
        $stmt->bind_param("isi", $usuario['id'], $token, $expira);
        $stmt->execute();

        // Criar cookie
        setcookie('auth_token', $token, $expira, '/', '', true, true);
    }

    // Registrar log de acesso
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $mysqli->prepare("
        INSERT INTO logs_acesso (usuario_id, ip, data_hora) 
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("is", $usuario['id'], $ip);
    $stmt->execute();

    // Log final para debug
    error_log("Redirecionando para dashboard");

    // Redirecionar para o dashboard
    header('Location: dashboard.php');
    exit;

} catch (Exception $e) {
    error_log("Erro no login: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: login.php');
    exit;
} 