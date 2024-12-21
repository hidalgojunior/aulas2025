<?php
session_start();

// Verificar instalação
if (!file_exists('installed.txt')) {
    header('Location: install/');
    exit;
}

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
require_once 'functions.php';

try {
    $mysqli = getConnection();
    
    // Obter dados do usuário
    $stmt = $mysqli->prepare("
        SELECT nome, papel 
        FROM usuarios 
        WHERE id = ? AND ativo = 1 
        LIMIT 1
    ");
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }

    // Obter estatísticas
    $stats = [
        'total_labs' => $mysqli->query("SELECT COUNT(*) FROM laboratorios WHERE ativo = 1")->fetch_row()[0],
        'labs_manutencao' => $mysqli->query("SELECT COUNT(*) FROM laboratorios WHERE em_manutencao = 1")->fetch_row()[0],
        'minhas_reservas' => $mysqli->query("SELECT COUNT(*) FROM reservas WHERE usuario_id = {$_SESSION['user_id']}")->fetch_row()[0],
        'reservas_hoje' => $mysqli->query("SELECT COUNT(*) FROM reservas WHERE data = CURDATE()")->fetch_row()[0]
    ];

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas - Início</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .nav-link {
            border-radius: 0.5rem;
            margin: 0.2rem 0;
        }
        .nav-link:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .nav-link.active {
            background-color: var(--bs-primary);
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" style="min-height: 100vh;">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-pc-display-horizontal text-primary" style="font-size: 2rem;"></i>
                        <h5 class="mt-2">Sistema de Reservas</h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-house-door me-2"></i>
                                Início
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservas.php">
                                <i class="bi bi-calendar-check me-2"></i>
                                Reservas
                            </a>
                        </li>
                        <?php if ($usuario['papel'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="laboratorios.php">
                                <i class="bi bi-pc-display me-2"></i>
                                Laboratórios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="bi bi-people me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="perfil.php">
                                <i class="bi bi-person-circle me-2"></i>
                                Meu Perfil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h1>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estatísticas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-pc-display me-2"></i>
                                    Laboratórios
                                </h5>
                                <h2 class="mt-3 mb-0"><?php echo $stats['total_labs']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-tools me-2"></i>
                                    Em Manutenção
                                </h5>
                                <h2 class="mt-3 mb-0"><?php echo $stats['labs_manutencao']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    Minhas Reservas
                                </h5>
                                <h2 class="mt-3 mb-0"><?php echo $stats['minhas_reservas']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-day me-2"></i>
                                    Reservas Hoje
                                </h5>
                                <h2 class="mt-3 mb-0"><?php echo $stats['reservas_hoje']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Ações Rápidas</h5>
                                <div class="d-grid gap-2">
                                    <a href="nova-reserva.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Nova Reserva
                                    </a>
                                    <a href="minhas-reservas.php" class="btn btn-outline-primary">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        Minhas Reservas
                                    </a>
                                    <?php if ($usuario['papel'] === 'admin'): ?>
                                    <a href="novo-laboratorio.php" class="btn btn-outline-primary">
                                        <i class="bi bi-pc-display me-2"></i>
                                        Novo Laboratório
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Próximas Reservas</h5>
                                <div class="list-group list-group-flush">
                                    <?php
                                    $reservas = $mysqli->query("
                                        SELECT r.*, l.nome as lab_nome 
                                        FROM reservas r 
                                        JOIN laboratorios l ON r.laboratorio_id = l.id 
                                        WHERE r.usuario_id = {$_SESSION['user_id']} 
                                        AND r.data >= CURDATE() 
                                        ORDER BY r.data, r.hora_inicio 
                                        LIMIT 5
                                    ");
                                    
                                    if ($reservas->num_rows > 0):
                                        while ($reserva = $reservas->fetch_assoc()):
                                    ?>
                                        <div class="list-group-item">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($reserva['lab_nome']); ?></h6>
                                            <p class="mb-1">
                                                <small>
                                                    <?php 
                                                    echo date('d/m/Y', strtotime($reserva['data']));
                                                    echo ' - ';
                                                    echo date('H:i', strtotime($reserva['hora_inicio']));
                                                    echo ' às ';
                                                    echo date('H:i', strtotime($reserva['hora_fim']));
                                                    ?>
                                                </small>
                                            </p>
                                        </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="text-center text-muted py-3">
                                            Nenhuma reserva próxima
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>