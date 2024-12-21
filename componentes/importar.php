<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = "Importar Componente e CHA";
$currentPage = 'componentes';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navigation.php';

// Processar upload de PDF ou texto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli = getConnection();
    
    if (isset($_POST['texto_cha'])) {
        // Processamento do texto colado
        $texto = $_POST['texto_cha'];
        $componente_id = $_POST['componente_id'];
        
        // Identificar padrões no texto
        $competencias = [];
        $habilidades = [];
        $atitudes = [];
        
        // Dividir o texto em linhas
        $linhas = explode("\n", $texto);
        $tipo_atual = '';
        
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if (empty($linha)) continue;
            
            // Identificar seções
            if (stripos($linha, 'competência') !== false || stripos($linha, 'competencias') !== false) {
                $tipo_atual = 'competencia';
                continue;
            }
            if (stripos($linha, 'habilidade') !== false || stripos($linha, 'habilidades') !== false) {
                $tipo_atual = 'habilidade';
                continue;
            }
            if (stripos($linha, 'atitude') !== false || stripos($linha, 'atitudes') !== false) {
                $tipo_atual = 'atitude';
                continue;
            }
            
            // Armazenar item na categoria apropriada
            switch ($tipo_atual) {
                case 'competencia':
                    $competencias[] = $linha;
                    break;
                case 'habilidade':
                    $habilidades[] = $linha;
                    break;
                case 'atitude':
                    $atitudes[] = $linha;
                    break;
            }
        }
        
        // Inserir no banco de dados
        $mysqli->begin_transaction();
        
        try {
            // Inserir competências
            foreach ($competencias as $comp) {
                $stmt = $mysqli->prepare("INSERT INTO competencias (componente_id, descricao) VALUES (?, ?)");
                $stmt->bind_param("is", $componente_id, $comp);
                $stmt->execute();
            }
            
            // Inserir habilidades
            foreach ($habilidades as $hab) {
                $stmt = $mysqli->prepare("INSERT INTO habilidades (componente_id, descricao) VALUES (?, ?)");
                $stmt->bind_param("is", $componente_id, $hab);
                $stmt->execute();
            }
            
            // Inserir atitudes
            foreach ($atitudes as $at) {
                $stmt = $mysqli->prepare("INSERT INTO atitudes (componente_id, descricao) VALUES (?, ?)");
                $stmt->bind_param("is", $componente_id, $at);
                $stmt->execute();
            }
            
            $mysqli->commit();
            $_SESSION['success'] = 'CHA importado com sucesso!';
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $_SESSION['error'] = 'Erro ao importar CHA: ' . $e->getMessage();
        }
        
    } elseif (isset($_FILES['pdf_cha'])) {
        // Processamento do PDF
        require_once __DIR__ . '/../vendor/autoload.php'; // Requer composer require smalot/pdfparser
        
        $parser = new \Smalot\PdfParser\Parser();
        
        try {
            $pdf = $parser->parseFile($_FILES['pdf_cha']['tmp_name']);
            $text = $pdf->getText();
            
            // Redirecionar para o mesmo formulário com o texto extraído
            $_SESSION['pdf_text'] = $text;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao processar PDF: ' . $e->getMessage();
        }
    }
}

// Buscar componentes para o select
$mysqli = getConnection();
$query = "SELECT c.*, cu.nome as curso_nome 
          FROM componentes c 
          JOIN cursos cu ON c.curso_id = cu.id 
          ORDER BY cu.nome, c.nome";
$result = $mysqli->query($query);
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Importar CHA do Componente</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="componente_id" class="form-label">Selecione o Componente</label>
                            <select class="form-select" name="componente_id" id="componente_id" required>
                                <option value="">Selecione...</option>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo $row['curso_nome'] . ' - ' . $row['nome']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pdf_cha" class="form-label">Upload de PDF</label>
                            <input type="file" class="form-control" id="pdf_cha" name="pdf_cha" accept=".pdf">
                            <div class="form-text">Faça upload do PDF do plano de ensino ou documento com CHA</div>
                        </div>

                        <div class="mb-3">
                            <label for="texto_cha" class="form-label">Ou Cole o Texto</label>
                            <textarea class="form-control" id="texto_cha" name="texto_cha" rows="10"
                                    placeholder="Cole aqui o texto com as competências, habilidades e atitudes..."
                            ><?php echo isset($_SESSION['pdf_text']) ? $_SESSION['pdf_text'] : ''; ?></textarea>
                            <?php unset($_SESSION['pdf_text']); ?>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">Dicas para importação:</h6>
                            <ul class="mb-0">
                                <li>Use palavras-chave como "Competências:", "Habilidades:" e "Atitudes:" para separar as seções</li>
                                <li>Cada item deve estar em uma nova linha</li>
                                <li>Remova numerações ou marcadores desnecessários</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Importar CHA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$mysqli->close();
require_once __DIR__ . '/../components/footer.php'; 
?> 