<?php
session_start();
require_once 'functions.php';

// Ativar logs de erro para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log para debug
error_log("Verificando sessão: " . print_r($_SESSION, true));

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não está logado - redirecionando para login");
    header('Location: login.php');
    exit;
}

// Log para debug
error_log("Usuário logado - ID: " . $_SESSION['user_id']);

// Obter dados do usuário e estatísticas
$mysqli = getConnection();
$user_id = $_SESSION['user_id'];

// Estatísticas gerais
$stats = [
    'total_labs' => $mysqli->query("SELECT COUNT(*) FROM laboratorios WHERE ativo = 1")->fetch_row()[0],
    'labs_manutencao' => $mysqli->query("SELECT COUNT(*) FROM laboratorios WHERE em_manutencao = 1")->fetch_row()[0],
    'reservas_hoje' => $mysqli->query("SELECT COUNT(*) FROM reservas WHERE data = CURDATE()")->fetch_row()[0],
    'reservas_semana' => $mysqli->query("SELECT COUNT(*) FROM reservas WHERE data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_row()[0]
];

// Últimas reservas
$ultimas_reservas = $mysqli->query("
    SELECT r.*, l.nome as lab_nome, u.nome as professor_nome 
    FROM reservas r 
    JOIN laboratorios l ON r.laboratorio_id = l.id 
    JOIN usuarios u ON r.usuario_id = u.id 
    ORDER BY r.data DESC, r.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Dashboard - Sistema de Reservas";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .navbar {
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        main {
            padding-top: 48px;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pc-display-horizontal"></i> Sistema de Reservas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">
                                <i class="bi bi-calendar-check"></i> Reservas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laboratorios.php">
                                <i class="bi bi-pc-display"></i> Laboratórios
                            </a>
                        </li>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="relatorios.php">
                                <i class="bi bi-graph-up"></i> Relatórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="configuracoes.php">
                                <i class="bi bi-gear"></i> Configurações
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="nova-reserva.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Nova Reserva
                        </a>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Laboratórios</h5>
                                <p class="card-text display-6"><?php echo $stats['total_labs']; ?></p>
                                <small>Ativos no sistema</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Em Manutenção</h5>
                                <p class="card-text display-6"><?php echo $stats['labs_manutencao']; ?></p>
                                <small>Laboratórios indisponíveis</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Reservas Hoje</h5>
                                <p class="card-text display-6"><?php echo $stats['reservas_hoje']; ?></p>
                                <small>Agendamentos do dia</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Próxima Semana</h5>
                                <p class="card-text display-6"><?php echo $stats['reservas_semana']; ?></p>
                                <small>Reservas agendadas</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendário e Últimas Reservas -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Calendário de Reservas</h5>
                                <?php include 'components/calendar.php'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Últimas Reservas</h5>
                                <?php if (empty($ultimas_reservas)): ?>
                                    <p class="text-muted">Nenhuma reserva encontrada</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                    <?php foreach ($ultimas_reservas as $reserva): ?>
                                        <div class="list-group-item">
                                            <h6 class="mb-1"><?php echo $reserva['lab_nome']; ?></h6>
                                            <p class="mb-1">
                                                <small class="text-muted">
                                                    <?php 
                                                    echo date('d/m/Y', strtotime($reserva['data']));
                                                    echo ' - Prof. ' . $reserva['professor_nome'];
                                                    ?>
                                                </small>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>