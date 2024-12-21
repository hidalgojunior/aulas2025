<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = "Visualizar Componente e CHA";
$currentPage = 'componentes';

// Verificar ID do componente
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = 'Componente não encontrado';
    header('Location: /hidalgojunior/componentes');
    exit;
}

$mysqli = getConnection();

// Buscar dados do componente
$query = "SELECT c.*, cu.nome as curso_nome 
          FROM componentes c 
          JOIN cursos cu ON c.curso_id = cu.id 
          WHERE c.id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$componente = $stmt->get_result()->fetch_assoc();

if (!$componente) {
    $_SESSION['error'] = 'Componente não encontrado';
    header('Location: /hidalgojunior/componentes');
    exit;
}

// Buscar CHA
$queries = [
    'competencias' => "SELECT * FROM competencias WHERE componente_id = ? ORDER BY id",
    'habilidades' => "SELECT * FROM habilidades WHERE componente_id = ? ORDER BY id",
    'atitudes' => "SELECT * FROM atitudes WHERE componente_id = ? ORDER BY id"
];

$cha = [];
foreach ($queries as $key => $query) {
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cha[$key] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navigation.php';
?>

<div class="container-fluid py-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo $componente['nome']; ?> - <?php echo $componente['curso_nome']; ?>
                    </h6>
                    <div>
                        <a href="/hidalgojunior/componentes/importar" class="btn btn-success btn-sm">
                            <i class="bi bi-upload"></i> Importar CHA
                        </a>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal">
                            <i class="bi bi-pencil"></i> Editar CHA
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Competências</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($cha['competencias'] as $comp): ?>
                                            <li class="list-group-item">
                                                <i class="bi bi-check2-circle text-primary"></i>
                                                <?php echo $comp['descricao']; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Habilidades</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($cha['habilidades'] as $hab): ?>
                                            <li class="list-group-item">
                                                <i class="bi bi-gear text-success"></i>
                                                <?php echo $hab['descricao']; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">Atitudes</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($cha['atitudes'] as $at): ?>
                                            <li class="list-group-item">
                                                <i class="bi bi-person-check text-warning"></i>
                                                <?php echo $at['descricao']; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editarModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar CHA - <?php echo $componente['nome']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCHA" action="/hidalgojunior/componentes/salvar_cha.php" method="post">
                    <input type="hidden" name="componente_id" value="<?php echo $id; ?>">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-primary">Competências</h6>
                            <div id="competencias-container">
                                <?php foreach ($cha['competencias'] as $comp): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="competencias[]" 
                                               value="<?php echo htmlspecialchars($comp['descricao']); ?>">
                                        <button type="button" class="btn btn-outline-danger remover-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm adicionar-item" 
                                    data-container="competencias-container">
                                <i class="bi bi-plus-circle"></i> Adicionar Competência
                            </button>
                        </div>
                        
                        <div class="col-md-4">
                            <h6 class="text-success">Habilidades</h6>
                            <div id="habilidades-container">
                                <?php foreach ($cha['habilidades'] as $hab): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="habilidades[]" 
                                               value="<?php echo htmlspecialchars($hab['descricao']); ?>">
                                        <button type="button" class="btn btn-outline-danger remover-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm adicionar-item" 
                                    data-container="habilidades-container">
                                <i class="bi bi-plus-circle"></i> Adicionar Habilidade
                            </button>
                        </div>
                        
                        <div class="col-md-4">
                            <h6 class="text-warning">Atitudes</h6>
                            <div id="atitudes-container">
                                <?php foreach ($cha['atitudes'] as $at): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="atitudes[]" 
                                               value="<?php echo htmlspecialchars($at['descricao']); ?>">
                                        <button type="button" class="btn btn-outline-danger remover-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-warning btn-sm adicionar-item" 
                                    data-container="atitudes-container">
                                <i class="bi bi-plus-circle"></i> Adicionar Atitude
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formCHA" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar novo item
    document.querySelectorAll('.adicionar-item').forEach(button => {
        button.addEventListener('click', function() {
            const container = document.getElementById(this.dataset.container);
            const novoItem = document.createElement('div');
            novoItem.className = 'input-group mb-2';
            novoItem.innerHTML = `
                <input type="text" class="form-control" name="${this.dataset.container.replace('-container', '[]')}">
                <button type="button" class="btn btn-outline-danger remover-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(novoItem);
        });
    });

    // Remover item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remover-item') || e.target.closest('.remover-item')) {
            const button = e.target.classList.contains('remover-item') ? e.target : e.target.closest('.remover-item');
            button.closest('.input-group').remove();
        }
    });
});
</script>

<?php 
$mysqli->close();
require_once __DIR__ . '/../components/footer.php'; 
?> 