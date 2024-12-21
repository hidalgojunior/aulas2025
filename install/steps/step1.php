<?php
$allRequirementsMet = true;
?>

<div class="requirements-check">
    <p class="lead mb-4">O sistema verificará se seu servidor atende aos requisitos mínimos de instalação.</p>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Requisito</th>
                    <th>Necessário</th>
                    <th>Atual</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $name => $requirement): ?>
                    <?php $allRequirementsMet = $allRequirementsMet && $requirement['status']; ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $requirement['required'] ? 'Sim' : 'Não'; ?></td>
                        <td><?php echo $requirement['current']; ?></td>
                        <td>
                            <?php if ($requirement['status']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> OK</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Erro</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Verificar Novamente
        </button>
        
        <?php if ($allRequirementsMet): ?>
            <form method="post" action="process.php">
                <input type="hidden" name="step" value="1">
                <button type="submit" class="btn btn-primary">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-primary" disabled>
                Próximo <i class="bi bi-arrow-right"></i>
            </button>
        <?php endif; ?>
    </div>
</div> 