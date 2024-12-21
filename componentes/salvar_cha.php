<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /hidalgojunior/componentes');
    exit;
}

$componente_id = filter_input(INPUT_POST, 'componente_id', FILTER_VALIDATE_INT);
if (!$componente_id) {
    $_SESSION['error'] = 'Componente inválido';
    header('Location: /hidalgojunior/componentes');
    exit;
}

$mysqli = getConnection();
$mysqli->begin_transaction();

try {
    // Limpar CHA existente
    $tables = ['competencias', 'habilidades', 'atitudes'];
    foreach ($tables as $table) {
        $mysqli->query("DELETE FROM $table WHERE componente_id = $componente_id");
    }

    // Inserir novos dados
    $stmt_comp = $mysqli->prepare("INSERT INTO competencias (componente_id, descricao) VALUES (?, ?)");
    $stmt_hab = $mysqli->prepare("INSERT INTO habilidades (componente_id, descricao) VALUES (?, ?)");
    $stmt_at = $mysqli->prepare("INSERT INTO atitudes (componente_id, descricao) VALUES (?, ?)");

    // Processar competências
    if (isset($_POST['competencias'])) {
        foreach ($_POST['competencias'] as $comp) {
            if (trim($comp) !== '') {
                $stmt_comp->bind_param("is", $componente_id, $comp);
                $stmt_comp->execute();
            }
        }
    }

    // Processar habilidades
    if (isset($_POST['habilidades'])) {
        foreach ($_POST['habilidades'] as $hab) {
            if (trim($hab) !== '') {
                $stmt_hab->bind_param("is", $componente_id, $hab);
                $stmt_hab->execute();
            }
        }
    }

    // Processar atitudes
    if (isset($_POST['atitudes'])) {
        foreach ($_POST['atitudes'] as $at) {
            if (trim($at) !== '') {
                $stmt_at->bind_param("is", $componente_id, $at);
                $stmt_at->execute();
            }
        }
    }

    $mysqli->commit();
    $_SESSION['success'] = 'CHA atualizado com sucesso!';

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['error'] = 'Erro ao salvar CHA: ' . $e->getMessage();
}

$mysqli->close();
header("Location: /hidalgojunior/componentes/visualizar.php?id=$componente_id");
exit; 