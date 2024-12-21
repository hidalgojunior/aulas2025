<?php
session_start();
header('Content-Type: text/plain; charset=utf-8');

try {
    // Debug - remover em produção
    error_log('POST recebido: ' . print_r($_POST, true));

    // Validar dados recebidos
    $required = ['host', 'dbname', 'username', 'adminName', 'adminEmail', 'adminPassword'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("Campo obrigatório não preenchido: $field");
        }
    }

    // Dados do banco (senha pode ser vazia)
    $host = trim($_POST['host']);
    $dbname = trim($_POST['dbname']);
    $username = trim($_POST['username']);
    $dbpassword = $_POST['dbPassword'] ?? ''; // Opcional

    // Dados do admin
    $adminName = trim($_POST['adminName']);
    $adminEmail = trim($_POST['adminEmail']);
    
    // Validar senha do admin
    if (strlen($_POST['adminPassword']) < 6) {
        throw new Exception("A senha do administrador deve ter no mínimo 6 caracteres");
    }
    $adminPassword = password_hash($_POST['adminPassword'], PASSWORD_DEFAULT);

    // Validar formato do email
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }

    // Tentar conectar ao MySQL
    $mysqli = @new mysqli($host, $username, $dbpassword);
    
    if ($mysqli->connect_error) {
        throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }

    // Criar banco de dados se não existir
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($dbname);

    // Criar tabelas
    $tables = [
        "CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nome` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `senha` VARCHAR(255) NOT NULL,
            `papel` ENUM('admin', 'professor') NOT NULL DEFAULT 'professor',
            `ativo` BOOLEAN NOT NULL DEFAULT true,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS `laboratorios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nome` VARCHAR(100) NOT NULL,
            `capacidade` INT NOT NULL,
            `descricao` TEXT,
            `em_manutencao` BOOLEAN NOT NULL DEFAULT false,
            `ativo` BOOLEAN NOT NULL DEFAULT true,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS `reservas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `laboratorio_id` INT NOT NULL,
            `usuario_id` INT NOT NULL,
            `data` DATE NOT NULL,
            `hora_inicio` TIME NOT NULL,
            `hora_fim` TIME NOT NULL,
            `descricao` TEXT,
            `status` ENUM('pendente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendente',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`laboratorio_id`) REFERENCES `laboratorios`(`id`),
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        )",
        "CREATE TABLE IF NOT EXISTS `tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `token` VARCHAR(64) NOT NULL UNIQUE,
            `expira` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        )",
        "CREATE TABLE IF NOT EXISTS `logs_acesso` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `ip` VARCHAR(45) NOT NULL,
            `data_hora` DATETIME NOT NULL,
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
        )"
    ];

    // Executar criação das tabelas
    foreach ($tables as $sql) {
        if (!$mysqli->query($sql)) {
            throw new Exception('Erro ao criar tabela: ' . $mysqli->error);
        }
    }

    // Criar usuário administrador
    $stmt = $mysqli->prepare("
        INSERT INTO usuarios (nome, email, senha, papel) 
        VALUES (?, ?, ?, 'admin')
    ");
    $stmt->bind_param("sss", $adminName, $adminEmail, $adminPassword);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao criar usuário administrador: ' . $stmt->error);
    }

    // Criar arquivo de configuração com verificação de constantes
    $config = "<?php\n";
    $config .= "if (!defined('DB_HOST')) define('DB_HOST', '" . addslashes($host) . "');\n";
    $config .= "if (!defined('DB_NAME')) define('DB_NAME', '" . addslashes($dbname) . "');\n";
    $config .= "if (!defined('DB_USER')) define('DB_USER', '" . addslashes($username) . "');\n";
    $config .= "if (!defined('DB_PASS')) define('DB_PASS', '" . addslashes($dbpassword) . "');\n";

    // Verificar se o arquivo de configuração já existe
    if (file_exists('../config.php')) {
        // Fazer backup do arquivo existente
        $backup = '../config.backup.' . date('Y-m-d-H-i-s') . '.php';
        copy('../config.php', $backup);
    }

    // Salvar nova configuração
    if (!@file_put_contents('../config.php', $config)) {
        throw new Exception('Erro ao criar arquivo de configuração');
    }

    // Criar arquivo de instalação concluída
    if (!file_put_contents('../installed.txt', date('Y-m-d H:i:s'))) {
        throw new Exception('Erro ao criar arquivo de instalação');
    }

    // Criar pasta de uploads se não existir
    $uploadsDir = '../uploads';
    if (!file_exists($uploadsDir) && !mkdir($uploadsDir, 0755, true)) {
        throw new Exception('Erro ao criar diretório de uploads');
    }

    // Responder sucesso
    echo "success";

} catch (Exception $e) {
    // Responder erro
    http_response_code(500);
    echo $e->getMessage();
}