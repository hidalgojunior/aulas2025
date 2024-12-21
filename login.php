<?php
session_start();

// Verificar se já está instalado
if (!file_exists('installed.txt')) {
    header('Location: install/');
    exit;
}

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        if (!$email) {
            throw new Exception('Email inválido');
        }

        if (empty($senha)) {
            throw new Exception('Senha é obrigatória');
        }

        $mysqli = getConnection();
        
        // Buscar usuário pelo email
        $stmt = $mysqli->prepare("
            SELECT id, nome, senha, ativo 
            FROM usuarios 
            WHERE email = ? 
            LIMIT 1
        ");
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        // Verificar se o usuário existe e está ativo
        if (!$usuario) {
            throw new Exception('Usuário não encontrado');
        }

        if (!$usuario['ativo']) {
            throw new Exception('Usuário inativo. Entre em contato com o administrador.');
        }

        // Verificar senha
        if (!password_verify($senha, $usuario['senha'])) {
            throw new Exception('Senha incorreta');
        }

        // Login bem sucedido
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nome'];

        // Registrar log de acesso
        $stmt = $mysqli->prepare("
            INSERT INTO logs_acesso (usuario_id, ip, data_hora) 
            VALUES (?, ?, NOW())
        ");
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("is", $usuario['id'], $ip);
        $stmt->execute();

        // Redirecionar para o dashboard
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        }
        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            margin: 0 auto;
        }
        .form-floating > label {
            padding-left: 1rem;
        }
        .form-floating > .form-control {
            padding-left: 1rem;
        }
        .btn-login {
            height: 3.5rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <i class="bi bi-pc-display-horizontal text-primary" style="font-size: 3rem;"></i>
                <h1 class="h3 mt-2">Sistema de Reservas</h1>
                <p class="text-muted">Entre com suas credenciais</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-floating mb-3">
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="nome@exemplo.com" 
                           required 
                           autocomplete="email">
                    <label for="email">Email</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" 
                           class="form-control" 
                           id="senha" 
                           name="senha" 
                           placeholder="Senha" 
                           required 
                           autocomplete="current-password">
                    <label for="senha">Senha</label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="lembrar"
                           name="lembrar" 
                           value="1">
                    <label class="form-check-label" for="lembrar">
                        Lembrar-me
                    </label>
                </div>

                <button class="btn btn-primary w-100 btn-login" type="submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Entrar
                </button>

                <div class="text-center mt-3">
                    <a href="recuperar-senha.php" class="text-decoration-none">
                        Esqueceu sua senha?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Usar requestAnimationFrame em vez de setTimeout
        document.addEventListener('DOMContentLoaded', () => {
            // Remover alertas automaticamente
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length) {
                requestAnimationFrame(() => {
                    alerts.forEach(alert => {
                        const bsAlert = new bootstrap.Alert(alert);
                        requestAnimationFrame(() => {
                            setTimeout(() => {
                                bsAlert.close();
                            }, 5000);
                        });
                    });
                });
            }

            // Otimizar transições
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Entrando...';
                    }
                });
            }
        });

        // Prevenir múltiplos submits
        let isSubmitting = false;
        document.querySelector('form')?.addEventListener('submit', (e) => {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
        });
    </script>
</body>
</html> 