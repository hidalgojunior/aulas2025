<?php
session_start();
require_once 'functions.php';

$pageTitle = "Sistema de Reservas de Laboratórios";
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
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --bg-light: #f8f9fa;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .hero-section {
            background-color: var(--primary-color);
            color: white;
            padding: 4rem 0;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 0.75rem;
            background-color: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .section {
            padding: 5rem 0;
        }
        
        .section-title {
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        /* Estilos para o calendário */
        .calendar-table {
            table-layout: fixed;
        }
        
        .calendar-day {
            height: 100px;
            vertical-align: top;
            padding: 5px;
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .has-events {
            background-color: rgba(13, 110, 253, 0.1);
        }
        
        .event-indicator {
            margin-top: 5px;
        }
        
        .calendar-header {
            background-color: #fff;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="./">
                <i class="bi bi-pc-display-horizontal me-2"></i>
                Sistema de Reservas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#inicio">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#recursos">Recursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#laboratorios">Laboratórios</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-light me-2">Dashboard</a>
                        <a href="logout.php" class="btn btn-outline-light">Sair</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-light">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Acessar Sistema
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="inicio">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Reserva de Laboratórios</h1>
                    <p class="lead mb-4">
                        Sistema integrado para gerenciamento e reserva dos laboratórios de informática.
                        Simples, eficiente e organizado.
                    </p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-light btn-lg">
                            Começar Agora <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div class="card bg-light p-4">
                        <h3 class="h4 mb-4">Status dos Laboratórios</h3>
                        <?php
                        $mysqli = getConnection();
                        $result = $mysqli->query("SELECT id, nome, em_manutencao FROM laboratorios WHERE ativo = 1 LIMIT 5");
                        while ($lab = $result->fetch_assoc()):
                        ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-pc-display h4 mb-0 <?php echo $lab['em_manutencao'] ? 'text-danger' : 'text-success'; ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo $lab['nome']; ?></h6>
                                <small class="text-muted">
                                    <?php echo $lab['em_manutencao'] ? 'Em Manutenção' : 'Disponível'; ?>
                                </small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recursos -->
    <section class="section bg-light" id="recursos">
        <div class="container">
            <h2 class="section-title">Recursos do Sistema</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="h5 mb-3">Reservas Simplificadas</h3>
                        <p class="text-muted mb-0">
                            Processo intuitivo de reserva com confirmação automática e notificações.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="h5 mb-3">Relatórios e Análises</h3>
                        <p class="text-muted mb-0">
                            Acompanhamento detalhado do uso dos laboratórios e ocupação.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h3 class="h5 mb-3">Gestão Completa</h3>
                        <p class="text-muted mb-0">
                            Controle total sobre usuários, permissões e configurações.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Laboratórios -->
    <section class="section" id="laboratorios">
        <div class="container">
            <h2 class="section-title">Nossos Laboratórios</h2>
            <div class="row g-4">
                <?php
                $result = $mysqli->query("SELECT * FROM laboratorios WHERE ativo = 1");
                while ($lab = $result->fetch_assoc()):
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lab['nome']; ?></h5>
                            <p class="card-text"><?php echo $lab['descricao']; ?></p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-people me-2"></i>Capacidade: <?php echo $lab['capacidade']; ?> alunos</li>
                                <li>
                                    <i class="bi bi-circle-fill me-2 <?php echo $lab['em_manutencao'] ? 'text-danger' : 'text-success'; ?>"></i>
                                    Status: <?php echo $lab['em_manutencao'] ? 'Em Manutenção' : 'Disponível'; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Calendários -->
    <section class="section bg-light" id="calendarios">
        <div class="container">
            <h2 class="section-title">Calendário de Reservas</h2>
            
            <!-- Tabs para diferentes visualizações -->
            <ul class="nav nav-tabs mb-4" id="calendarTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#geral" type="button">
                        Calendário Geral
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#laboratorios" type="button">
                        Por Laboratório
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="calendarTabsContent">
                <!-- Calendário Geral -->
                <div class="tab-pane fade show active" id="geral">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php include 'components/calendar.php'; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Reservas do Dia</h5>
                                    <?php
                                    $hoje = date('Y-m-d');
                                    $reservas = getReservasByDate($mysqli, $hoje);
                                    
                                    if (empty($reservas)) {
                                        echo '<p class="text-muted">Nenhuma reserva para hoje.</p>';
                                    } else {
                                        foreach ($reservas as $reserva) {
                                            echo '<div class="mb-3 p-2 border-bottom">';
                                            echo '<strong>' . $reserva['lab_nome'] . '</strong><br>';
                                            echo '<small class="text-muted">';
                                            echo $reserva['inicio'] . ' - ' . $reserva['fim'] . '<br>';
                                            echo 'Prof. ' . $reserva['professor_nome'];
                                            echo '</small>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Calendário por Laboratório -->
                <div class="tab-pane fade" id="laboratorios">
                    <div class="row">
                        <?php
                        $labs = $mysqli->query("SELECT * FROM laboratorios WHERE ativo = 1");
                        while ($lab = $labs->fetch_assoc()):
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo $lab['nome']; ?></h5>
                                </div>
                                <div class="card-body">
                                    <!-- Mini calendário por laboratório -->
                                    <?php
                                    $reservasLab = $mysqli->query("
                                        SELECT data, COUNT(*) as total 
                                        FROM reservas 
                                        WHERE laboratorio_id = {$lab['id']} 
                                        AND status = 'confirmada' 
                                        AND data >= CURDATE() 
                                        GROUP BY data 
                                        ORDER BY data 
                                        LIMIT 5
                                    ");
                                    
                                    if ($reservasLab->num_rows > 0) {
                                        while ($res = $reservasLab->fetch_assoc()) {
                                            echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                            echo '<span>' . date('d/m/Y', strtotime($res['data'])) . '</span>';
                                            echo '<span class="badge bg-primary">' . $res['total'] . ' reservas</span>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p class="text-muted">Sem reservas próximas</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistema de Reservas</h5>
                    <p class="mb-0">Desenvolvido por Arnaldo Martins Hidalgo Junior</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 - Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    </script>
</body>
</html>