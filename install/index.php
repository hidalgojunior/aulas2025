<?php
session_start();
require_once 'functions.php';

$step = $_SESSION['install_step'] ?? 1;
$totalSteps = 4;

// Verificar requisitos do sistema
$requirements = checkSystemRequirements();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Controle de Aulas ETEC</title>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <style>
        :root {
            --primary-color: #fb6304;
            --primary-gradient: linear-gradient(135deg, #fb6304 0%, #ff8534 100%);
            --dark-gradient: linear-gradient(135deg, #212529 0%, #343a40 100%);
        }

        body {
            min-height: 100vh;
            background: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .installer-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .installer-header {
            background: var(--primary-gradient);
            padding: 2rem 0;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .installer-logo {
            max-width: 150px;
            height: auto;
        }

        .installer-steps {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
            padding: 0;
            list-style: none;
            position: relative;
        }

        .installer-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .installer-step.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .installer-step.completed {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .installer-step-connector {
            flex: 1;
            height: 2px;
            background: #dee2e6;
            margin: 20px 10px;
        }

        .installer-step-connector.active {
            background: var(--primary-color);
        }

        .installer-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .installer-card .card-header {
            background: var(--dark-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .progress-bar {
            background: var(--primary-gradient);
            transition: width 0.5s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(251, 99, 4, 0.25);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e55a03 0%, #f07b2d 100%);
        }

        .installation-progress {
            display: none;
            margin-top: 2rem;
        }

        .installation-log {
            height: 200px;
            overflow-y: auto;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.875rem;
        }

        .log-entry {
            margin-bottom: 0.5rem;
        }

        .log-entry.success {
            color: #28a745;
        }

        .log-entry.error {
            color: #dc3545;
        }

        .log-entry.info {
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="installer-wrapper">
        <header class="installer-header">
            <div class="container text-center">
                <img src="logo.png" alt="ETEC Logo" class="installer-logo mb-3">
                <h1 class="display-5 fw-bold">Sistema de Controle de Aulas ETEC</h1>
                <p class="lead">Assistente de Instalação</p>
            </div>
        </header>

        <main class="container py-5">
            <!-- Progress Steps -->
            <div class="installer-steps mb-5">
                <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                    <?php if ($i > 1): ?>
                        <div class="installer-step-connector <?php echo $step > $i ? 'active' : ''; ?>"></div>
                    <?php endif; ?>
                    <div class="installer-step <?php 
                        echo $step > $i ? 'completed' : ($step == $i ? 'active' : ''); 
                    ?>">
                        <?php echo $step > $i ? '<i class="bi bi-check"></i>' : $i; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="installer-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <?php
                        switch($step) {
                            case 1:
                                echo '<i class="bi bi-shield-check me-2"></i>Verificação de Requisitos';
                                break;
                            case 2:
                                echo '<i class="bi bi-database me-2"></i>Configuração do Banco de Dados';
                                break;
                            case 3:
                                echo '<i class="bi bi-gear me-2"></i>Instalação do Sistema';
                                break;
                            case 4:
                                echo '<i class="bi bi-check-circle me-2"></i>Conclusão';
                                break;
                        }
                        ?>
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php include "steps/step{$step}.php"; ?>
                </div>
            </div>
        </main>

        <footer class="py-4 text-center text-muted">
            <div class="container">
                <small>&copy; <?php echo date('Y'); ?> ETEC. Todos os direitos reservados.</small>
            </div>
        </footer>
    </div>

    <script type="module">
        // ... (código JavaScript otimizado anterior) ...
    </script>
</body>
</html>