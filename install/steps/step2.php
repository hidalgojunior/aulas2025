<div class="database-config">
    <p class="lead mb-4">Configure a conexão com o banco de dados MySQL/MariaDB.</p>

    <form method="post" action="process.php" class="needs-validation" novalidate>
        <input type="hidden" name="step" value="2">
        
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" 
                           class="form-control" 
                           id="db_host" 
                           name="db_host" 
                           value="localhost" 
                           required 
                           autocomplete="off">
                    <label for="db_host">Host do Banco de Dados</label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" 
                           class="form-control" 
                           id="db_port" 
                           name="db_port" 
                           value="3306" 
                           required 
                           autocomplete="off">
                    <label for="db_port">Porta</label>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" 
                           class="form-control" 
                           id="db_user" 
                           name="db_user" 
                           required 
                           autocomplete="off">
                    <label for="db_user">Usuário</label>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="password" 
                           class="form-control" 
                           id="db_pass" 
                           name="db_pass" 
                           autocomplete="new-password">
                    <label for="db_pass">Senha (opcional)</label>
                </div>
            </div>
        </div>

        <div class="form-floating mb-3">
            <input type="text" 
                   class="form-control" 
                   id="db_name" 
                   name="db_name" 
                   value="hidalgojunior" 
                   required 
                   autocomplete="off">
            <label for="db_name">Nome do Banco de Dados</label>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="test_connection" name="test_connection" checked>
            <label class="form-check-label" for="test_connection">
                Testar conexão antes de prosseguir
            </label>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="?step=1" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Anterior
            </a>
            <button type="submit" class="btn btn-primary">
                Próximo <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
</div> 