<?php

/**
 * Estabelece conexão com o banco de dados
 * @return mysqli Conexão com o banco de dados
 */
function getConnection() {
    // Carregar configurações
    require_once 'config/config.php';
    
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            throw new Exception('Erro de conexão com o banco de dados: ' . $mysqli->connect_error);
        }
        
        // Configurar charset
        $mysqli->set_charset('utf8mb4');
        
        return $mysqli;
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw new Exception('Erro ao conectar com o banco de dados. Por favor, verifique as configurações.');
    }
}

/**
 * Verifica se o usuário está logado
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Verifica se o usuário tem permissão de administrador
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redireciona para a página principal se não estiver autenticado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ./');
        exit;
    }
}

/**
 * Redireciona para a página inicial se não tiver permissão de administrador
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Escapa strings para prevenir XSS
 * @param string $str String para escapar
 * @return string String escapada
 */
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Formata data para o padrão brasileiro
 * @param string $date Data no formato Y-m-d
 * @return string Data no formato d/m/Y
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Formata hora para o padrão brasileiro
 * @param string $time Hora no formato H:i:s
 * @return string Hora no formato H:i
 */
function formatTime($time) {
    return date('H:i', strtotime($time));
}

/**
 * Gera log de atividade no sistema
 * @param string $acao Ação realizada
 * @param string $tabela Tabela afetada
 * @param int $registro_id ID do registro
 * @param array $dados_antigos Dados antes da alteração
 * @param array $dados_novos Dados após a alteração
 */
function gerarLog($acao, $tabela, $registro_id, $dados_antigos = null, $dados_novos = null) {
    try {
        $mysqli = getConnection();
        $usuario_id = $_SESSION['user_id'] ?? null;
        
        $stmt = $mysqli->prepare("INSERT INTO logs (usuario_id, acao, tabela, registro_id, dados_antigos, dados_novos) VALUES (?, ?, ?, ?, ?, ?)");
        $dados_antigos = $dados_antigos ? json_encode($dados_antigos) : null;
        $dados_novos = $dados_novos ? json_encode($dados_novos) : null;
        
        $stmt->bind_param("ississ", $usuario_id, $acao, $tabela, $registro_id, $dados_antigos, $dados_novos);
        $stmt->execute();
        
    } catch (Exception $e) {
        error_log('Erro ao gerar log: ' . $e->getMessage());
    }
}

/**
 * Verifica se uma data é válida
 * @param string $date Data no formato Y-m-d
 * @return bool
 */
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Verifica se um horário é válido
 * @param string $time Horário no formato H:i
 * @return bool
 */
function isValidTime($time) {
    $t = DateTime::createFromFormat('H:i', $time);
    return $t && $t->format('H:i') === $time;
}

/**
 * Retorna o nome do papel do usuário
 * @param string $role Papel do usuário (admin, professor, coordenador)
 * @return string Nome do papel em português
 */
function getRoleName($role) {
    $roles = [
        'admin' => 'Administrador',
        'professor' => 'Professor',
        'coordenador' => 'Coordenador'
    ];
    return $roles[$role] ?? $role;
}

/**
 * Verifica se o sistema está em manutenção
 * @return bool
 */
function isMaintenanceMode() {
    return defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true;
} 