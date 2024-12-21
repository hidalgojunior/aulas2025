<?php
session_start();

// Verificar se já está instalado
if (file_exists('../installed.txt')) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .install-container {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="text-center mb-4">
                <i class="bi bi-pc-display-horizontal text-primary" style="font-size: 3rem;"></i>
                <h1 class="h3 mt-2">Instalação do Sistema</h1>
                <p class="text-muted">Siga os passos para configurar o sistema</p>
            </div>

            <!-- Progresso -->
            <div class="progress mb-4" style="height: 3px;">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>

            <!-- Passo 1: Requisitos -->
            <div class="step active" id="step1">
                <h4 class="mb-3">Verificação de Requisitos</h4>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP >= 7.4
                        <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PDO MySQL
                        <?php if (extension_loaded('pdo_mysql')): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Permissão de Escrita
                        <?php if (is_writable('../')): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        <?php endif; ?>
                    </li>
                </ul>
                <button class="btn btn-primary w-100" onclick="nextStep(2)">Próximo</button>
            </div>

            <!-- Passo 2: Banco de Dados -->
            <div class="step" id="step2">
                <h4 class="mb-3">Configuração do Banco de Dados</h4>
                <form id="dbForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="host" class="form-label">Host *</label>
                        <input type="text" 
                               class="form-control" 
                               id="host" 
                               name="host" 
                               value="localhost" 
                               required
                               autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="dbname" class="form-label">Nome do Banco *</label>
                        <input type="text" 
                               class="form-control" 
                               id="dbname" 
                               name="dbname" 
                               required
                               autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário *</label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               required
                               autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label for="dbPassword" class="form-label">Senha do Banco</label>
                        <input type="password" 
                               class="form-control" 
                               id="dbPassword" 
                               name="dbPassword" 
                               autocomplete="new-password"
                               placeholder="Deixe em branco se não houver senha">
                    </div>
                </form>
            </div>

            <!-- Passo 3: Administrador -->
            <div class="step" id="step3">
                <h4 class="mb-3">Configuração do Administrador</h4>
                <form id="adminForm">
                    <div class="mb-3">
                        <label for="adminName" class="form-label">Nome *</label>
                        <input type="text" 
                               class="form-control" 
                               id="adminName" 
                               name="adminName" 
                               required
                               autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Email *</label>
                        <input type="email" 
                               class="form-control" 
                               id="adminEmail" 
                               name="adminEmail" 
                               required
                               autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Senha *</label>
                        <input type="password" 
                               class="form-control" 
                               id="adminPassword" 
                               name="adminPassword" 
                               required 
                               minlength="6"
                               autocomplete="new-password"
                               placeholder="Mínimo 6 caracteres">
                        <div class="form-text">
                            Esta será a senha do administrador do sistema.
                        </div>
                    </div>
                </form>
            </div>

            <!-- Passo 4: Conclusão -->
            <div class="step" id="step4">
                <div class="text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Instalação Concluída!</h4>
                    <p>O sistema foi instalado com sucesso.</p>
                    <a href="../index.php" class="btn btn-primary w-100">Acessar o Sistema</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function updateProgress() {
            const totalSteps = 4;
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.querySelector('.progress-bar').style.width = progress + '%';
        }

        function validateStep(step) {
            if (step === 1) {
                // Passo 1 sempre válido pois são apenas verificações
                return true;
            }
            
            if (step === 2) {
                // Validar campos do banco de dados
                const requiredFields = ['host', 'dbname', 'username'];
                return requiredFields.every(field => 
                    document.getElementById(field).value.trim() !== ''
                );
            }

            if (step === 3) {
                // Validar campos do administrador
                const requiredFields = ['adminName', 'adminEmail', 'adminPassword'];
                return requiredFields.every(field => 
                    document.getElementById(field).value.trim() !== ''
                );
            }

            return true;
        }

        function showStep(step) {
            // Ocultar todos os passos
            document.querySelectorAll('.step').forEach(el => {
                el.style.display = 'none';
            });
            
            // Mostrar o passo atual
            document.getElementById('step' + step).style.display = 'block';
            
            currentStep = step;
            updateProgress();
        }

        function nextStep(step) {
            if (validateStep(currentStep)) {
                showStep(step);
            } else {
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        }

        function prevStep(step) {
            showStep(step);
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
            
            // Adicionar botões de navegação em cada passo
            document.getElementById('step1').insertAdjacentHTML('beforeend', `
                <button type="button" class="btn btn-primary w-100 mt-3" onclick="nextStep(2)">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
            `);

            document.getElementById('step2').insertAdjacentHTML('beforeend', `
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-secondary w-50" onclick="prevStep(1)">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary w-50" onclick="nextStep(3)">
                        Próximo <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            `);

            document.getElementById('step3').insertAdjacentHTML('beforeend', `
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-secondary w-50" onclick="prevStep(2)">
                        <i class="bi bi-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-success w-50" onclick="install()">
                        <i class="bi bi-check-circle"></i> Instalar
                    </button>
                </div>
            `);
        });

        function install() {
            if (!validateStep(3)) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }

            // Desabilitar todos os botões durante a instalação
            document.querySelectorAll('button').forEach(btn => btn.disabled = true);

            // Mostrar indicador de carregamento
            document.querySelector('.progress-bar').classList.add('progress-bar-striped', 'progress-bar-animated');

            const formData = new FormData();
            
            // Dados do banco
            ['host', 'dbname', 'username', 'dbPassword'].forEach(field => {
                formData.append(field, document.getElementById(field).value.trim());
            });
            
            // Dados do admin
            ['adminName', 'adminEmail', 'adminPassword'].forEach(field => {
                formData.append(field, document.getElementById(field).value.trim());
            });

            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('success')) {
                    showStep(4);
                } else {
                    alert('Erro na instalação: ' + data);
                    // Reabilitar botões em caso de erro
                    document.querySelectorAll('button').forEach(btn => btn.disabled = false);
                }
            })
            .catch(error => {
                alert('Erro na instalação: ' + error);
                // Reabilitar botões em caso de erro
                document.querySelectorAll('button').forEach(btn => btn.disabled = false);
            });
        }
    </script>
</body>
</html>