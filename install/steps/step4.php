<?php
// Definir URL base do sistema de forma simples e direta
$siteUrl = 'http://localhost/hidalgojunior';

// Salvar na sessão
$_SESSION['site_url'] = $siteUrl;
?>

<div class="installation-complete text-center">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
    </div>
    
    <h3 class="mb-4">Instalação Concluída com Sucesso!</h3>
    
    <div class="alert alert-info mb-4">
        <h5 class="alert-heading">Dados de Acesso</h5>
        <p class="mb-0">
            <strong>URL:</strong> <?php echo $siteUrl; ?><br>
            <strong>Email:</strong> hidalgojunior@gmail.com<br>
            <strong>Senha:</strong> jr34139251
        </p>
    </div>

    <div class="alert alert-warning mb-4">
        <h5 class="alert-heading">Próximos Passos</h5>
        <ol class="mb-0 text-start">
            <li>Faça login no sistema com as credenciais acima</li>
            <li>Altere sua senha no primeiro acesso</li>
            <li>Configure os dados da sua instituição</li>
            <li>Comece a usar o sistema!</li>
        </ol>
    </div>

    <div class="mt-4">
        <a href="<?php echo $siteUrl; ?>" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right"></i> Acessar o Sistema
        </a>
    </div>
</div>

<?php
// Limpar dados da instalação após mostrar a página final
if (isset($_SESSION['db_config'])) {
    unset($_SESSION['db_config']);
}
session_destroy();
?>