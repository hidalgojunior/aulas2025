<div class="installation">
    <p class="lead mb-4">O sistema está sendo instalado. Por favor, aguarde...</p>

    <div class="progress mb-4">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
             role="progressbar" 
             style="width: 0%" 
             aria-valuenow="0" 
             aria-valuemin="0" 
             aria-valuemax="100">0%</div>
    </div>

    <div class="installation-log border rounded p-3 bg-light">
        <div class="log-content" style="height: 200px; overflow-y: auto;"></div>
    </div>

    <div class="mt-4 text-center">
        <div class="installation-error alert alert-danger" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="error-message"></span>
            <hr>
            <button type="button" class="btn btn-danger" onclick="retryInstallation()">
                <i class="bi bi-arrow-clockwise"></i> Tentar Novamente
            </button>
        </div>

        <form method="post" action="process.php" id="completeInstallForm" style="display: none;">
            <input type="hidden" name="step" value="3">
            <input type="hidden" name="complete_installation" value="1">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-lg"></i> Finalizar Instalação
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    const logContent = document.querySelector('.log-content');
    const errorDiv = document.querySelector('.installation-error');
    const errorMessage = document.querySelector('.error-message');
    const completeForm = document.getElementById('completeInstallForm');

    async function performInstallation() {
        try {
            // Limpar logs anteriores
            logContent.innerHTML = '';
            errorDiv.style.display = 'none';
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-primary');

            // Iniciar instalação
            updateProgress(10, 'Iniciando instalação...');
            
            const formData = new FormData();
            formData.append('step', '3');
            formData.append('execute_installation', '1');

            const response = await fetch('process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.status) {
                throw new Error(result.message || 'Erro desconhecido na instalação');
            }

            // Atualizar progresso com sucesso
            await updateInstallationProgress();

            // Mostrar botão de finalização
            completeForm.style.display = 'block';

            // Após instalação bem-sucedida
            const completeResponse = await fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'step=3&complete_installation=1'
            });

            const completeResult = await completeResponse.json();
            
            if (completeResult.status && completeResult.redirect) {
                window.location.href = completeResult.redirect;
            }

        } catch (error) {
            console.error('Erro:', error);
            progressBar.classList.remove('bg-primary');
            progressBar.classList.add('bg-danger');
            errorDiv.style.display = 'block';
            errorMessage.textContent = error.message;
            
            // Adicionar erro ao log
            addLogEntry('error', `Erro na instalação: ${error.message}`);
        }
    }

    async function updateInstallationProgress() {
        const steps = [
            { progress: 20, message: 'Criando banco de dados...' },
            { progress: 40, message: 'Criando tabelas...' },
            { progress: 60, message: 'Inserindo dados iniciais...' },
            { progress: 80, message: 'Configurando sistema...' },
            { progress: 100, message: 'Instalação concluída!' }
        ];

        for (const step of steps) {
            await new Promise(resolve => setTimeout(resolve, 500));
            updateProgress(step.progress, step.message);
        }

        addLogEntry('success', 'Instalação concluída com sucesso!');
    }

    function updateProgress(progress, message) {
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.textContent = `${progress}%`;
        addLogEntry('info', message);
    }

    function addLogEntry(type, message) {
        const entry = document.createElement('div');
        entry.className = `log-entry ${type} mb-2`;
        
        let icon = 'arrow-right';
        if (type === 'success') icon = 'check-circle-fill';
        if (type === 'error') icon = 'x-circle-fill';
        
        entry.innerHTML = `<i class="bi bi-${icon}"></i> ${message}`;
        logContent.appendChild(entry);
        logContent.scrollTop = logContent.scrollHeight;
    }

    window.retryInstallation = performInstallation;

    // Iniciar instalação
    performInstallation();
});
</script>

<style>
.log-entry {
    font-family: monospace;
    font-size: 0.9rem;
}
.log-entry.info { color: #0d6efd; }
.log-entry.success { color: #198754; }
.log-entry.error { color: #dc3545; }
.log-entry i { margin-right: 8px; }
</style> 