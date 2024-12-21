<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');

require_once 'functions.php';
require_once 'versions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

try {
    $step = $_POST['step'] ?? 1;

    switch ($step) {
        case 1:
            $_SESSION['install_step'] = 2;
            echo json_encode([
                'status' => true,
                'redirect' => 'index.php'
            ]);
            break;

        case 2:
            $config = [
                'host' => $_POST['db_host'] ?? 'localhost',
                'port' => $_POST['db_port'] ?? '3306',
                'user' => $_POST['db_user'] ?? '',
                'pass' => $_POST['db_pass'] ?? '',
                'dbname' => $_POST['db_name'] ?? 'hidalgojunior'
            ];

            if (isset($_POST['test_connection'])) {
                $mysqli = new mysqli(
                    $config['host'],
                    $config['user'],
                    $config['pass']
                );

                if ($mysqli->connect_error) {
                    throw new Exception($mysqli->connect_error);
                }

                $_SESSION['db_config'] = $config;
                $_SESSION['install_step'] = 3;
                
                echo json_encode([
                    'status' => true,
                    'redirect' => 'index.php'
                ]);
            }
            break;

        case 3:
            if (isset($_POST['execute_installation'])) {
                if (!isset($_SESSION['db_config'])) {
                    throw new Exception('Configuração do banco de dados não encontrada');
                }

                $config = $_SESSION['db_config'];
                
                // Tentar conectar ao MySQL
                $mysqli = new mysqli($config['host'], $config['user'], $config['pass']);
                if ($mysqli->connect_error) {
                    throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
                }

                // Criar banco de dados
                if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                    throw new Exception('Erro ao criar banco de dados: ' . $mysqli->error);
                }

                // Selecionar banco de dados
                if (!$mysqli->select_db($config['dbname'])) {
                    throw new Exception('Erro ao selecionar banco de dados: ' . $mysqli->error);
                }

                // Executar SQL de instalação
                $sql = file_get_contents(__DIR__ . '/database.sql');
                if (!$sql) {
                    throw new Exception('Erro ao ler arquivo SQL');
                }

                // Executar cada comando SQL separadamente
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if (!empty($statement) && !$mysqli->query($statement)) {
                        throw new Exception('Erro ao executar SQL: ' . $mysqli->error . "\nQuery: " . $statement);
                    }
                }

                // Criar arquivo de configuração
                if (!createConfigFile($config)) {
                    throw new Exception('Erro ao criar arquivo de configuração');
                }

                echo json_encode([
                    'status' => true,
                    'message' => 'Instalação concluída com sucesso'
                ]);
            }

            if (isset($_POST['complete_installation'])) {
                $_SESSION['install_step'] = 4;
                
                // Redirecionar para a raiz do projeto
                echo json_encode([
                    'status' => true,
                    'redirect' => '../'  // Redireciona para a pasta raiz
                ]);
            }
            break;

        default:
            throw new Exception('Passo inválido');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}

exit;