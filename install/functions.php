<?php

function checkSystemRequirements() {
    return [
        'PHP Version >= 7.4' => [
            'required' => true,
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'MySQLi Extension' => [
            'required' => true,
            'current' => extension_loaded('mysqli') ? 'Instalado' : 'Não instalado',
            'status' => extension_loaded('mysqli')
        ],
        'PDO Extension' => [
            'required' => true,
            'current' => extension_loaded('pdo') ? 'Instalado' : 'Não instalado',
            'status' => extension_loaded('pdo')
        ],
        'PDO MySQL Extension' => [
            'required' => true,
            'current' => extension_loaded('pdo_mysql') ? 'Instalado' : 'Não instalado',
            'status' => extension_loaded('pdo_mysql')
        ],
        'Permissão de Escrita' => [
            'required' => true,
            'current' => is_writable('../') ? 'Permitido' : 'Negado',
            'status' => is_writable('../')
        ]
    ];
}

function installDatabase($config) {
    try {
        $mysqli = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass']
        );

        if ($mysqli->connect_error) {
            throw new Exception("Erro de conexão: " . $mysqli->connect_error);
        }

        // Criar banco de dados
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` 
                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        if ($mysqli->error) {
            throw new Exception("Erro ao criar banco de dados: " . $mysqli->error);
        }

        $mysqli->select_db($config['dbname']);

        // Executar script SQL
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $result = $mysqli->query($statement);
                if (!$result) {
                    throw new Exception("Erro ao executar SQL: " . $mysqli->error);
                }
            }
        }

        return [
            'status' => true,
            'message' => 'Banco de dados instalado com sucesso!'
        ];

    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}

function createConfigFile($config) {
    $template = <<<EOT
<?php
// Configurações do Banco de Dados
define('DB_HOST', '{$config['host']}');
define('DB_USER', '{$config['user']}');
define('DB_PASS', '{$config['pass']}');
define('DB_NAME', '{$config['dbname']}');

// Configurações do Sistema
define('SITE_URL', '{$config['url']}');
define('SITE_NAME', 'Sistema de Controle de Aulas ETEC');
define('TIMEZONE', 'America/Sao_Paulo');

// Configurações de Email
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', '');
define('MAIL_PASS', '');
define('MAIL_FROM', '');
define('MAIL_NAME', 'Sistema ETEC');

// Versão do Sistema
define('SYSTEM_VERSION', '1.0.0');
EOT;

    return file_put_contents('../config/config.php', $template) !== false;
} 