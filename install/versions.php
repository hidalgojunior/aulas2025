<?php
// Array com todas as versões e suas atualizações
$versions = [
    '1.0.0' => [
        'description' => 'Instalação inicial do sistema',
        'release_date' => '2024-12-21',
        'sql_file' => 'database.sql',
        'required' => true
    ],
    '1.0.1' => [
        'description' => 'Adição de campos para controle de manutenção de laboratórios',
        'release_date' => '2024-12-21',
        'sql_file' => 'updates/1.0.1.sql',
        'required' => true
    ],
    '1.0.2' => [
        'description' => 'Implementação do sistema de logs',
        'release_date' => '2024-12-21',
        'sql_file' => 'updates/1.0.2.sql',
        'required' => true
    ]
];

// Função para verificar versão atual do banco
function getCurrentVersion($mysqli) {
    $result = $mysqli->query("SELECT versao FROM versao_db ORDER BY id DESC LIMIT 1");
    return $result ? $result->fetch_assoc()['versao'] : '0.0.0';
}

// Função para verificar e aplicar atualizações
function checkAndApplyUpdates($mysqli) {
    global $versions;
    $currentVersion = getCurrentVersion($mysqli);
    $updates = [];
    $errors = [];

    foreach ($versions as $version => $info) {
        if (version_compare($version, $currentVersion, '>')) {
            try {
                // Carregar arquivo SQL da versão
                $sqlFile = __DIR__ . '/' . $info['sql_file'];
                if (!file_exists($sqlFile)) {
                    throw new Exception("Arquivo SQL não encontrado para versão {$version}");
                }

                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', explode(';', $sql)));

                $mysqli->begin_transaction();

                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        if (!$mysqli->query($statement)) {
                            throw new Exception($mysqli->error);
                        }
                    }
                }

                // Registrar atualização
                $stmt = $mysqli->prepare("INSERT INTO versao_db (versao, descricao) VALUES (?, ?)");
                $stmt->bind_param("ss", $version, $info['description']);
                $stmt->execute();

                $mysqli->commit();

                $updates[] = [
                    'version' => $version,
                    'status' => 'success',
                    'message' => "Versão {$version} instalada com sucesso"
                ];

            } catch (Exception $e) {
                $mysqli->rollback();
                $errors[] = [
                    'version' => $version,
                    'status' => 'error',
                    'message' => "Erro ao instalar versão {$version}: " . $e->getMessage()
                ];

                // Se esta versão é requerida, interromper o processo
                if ($info['required']) {
                    break;
                }
            }
        }
    }

    return [
        'current_version' => getCurrentVersion($mysqli),
        'updates' => $updates,
        'errors' => $errors
    ];
}

// Função para verificar se há atualizações disponíveis
function hasAvailableUpdates($currentVersion) {
    global $versions;
    foreach ($versions as $version => $info) {
        if (version_compare($version, $currentVersion, '>')) {
            return true;
        }
    }
    return false;
} 