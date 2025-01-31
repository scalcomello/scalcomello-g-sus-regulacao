<?php
// Conexão com o banco de dados
require '../includes/dbconnect.php';

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Base da SQL
$sql = "SELECT idProcedimento, codigo, tipo, procedimento, procedimento_especifico 
        FROM procedimento 
        WHERE procedimento LIKE ? OR codigo LIKE ?";

// Parâmetros da busca
$buscaParam = '%' . $busca . '%';

// Executar a contagem total de registros para paginação
$sqlCount = "SELECT COUNT(*) as total 
             FROM procedimento 
             WHERE procedimento LIKE ? OR codigo LIKE ?";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param('ss', $buscaParam, $buscaParam);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalRegistros = $resultCount->fetch_assoc()['total'];

// Calcular o total de páginas
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Adicionar limite e offset para paginação
$sql .= " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$offset = ($paginaAtual - 1) * $limiteExibicao;
$stmt->bind_param('ssii', $buscaParam, $buscaParam, $limiteExibicao, $offset);

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Bootstrap Icons CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Main Content -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Procedimentos</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Procedimentos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de busca e exibição -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="bi bi-search"></i> Buscar Procedimentos</h3>
                            <a href="procedimento.php" class="btn btn-primary ms-auto">
                                <i class="bi bi-plus-lg"></i> Cadastrar Procedimento
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="" method="get">
                                <div class="row gy-3">
                                    <!-- Campo de busca -->
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                            <input type="text" name="busca" class="form-control" placeholder="Código ou Procedimento" value="<?php echo htmlspecialchars($busca); ?>">
                                        </div>
                                    </div>

                                    <!-- Filtros de exibição e busca -->
                                    <div class="col-lg-6 col-md-6 col-12 d-flex align-items-end">
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-funnel"></i> Filtrar
                                            </button>
                                            <!-- Botão Limpar Filtros -->
                                            <a href="listar_procedimento.php" class="btn btn-secondary">
                                                <i class="bi bi-eraser"></i> Limpar Filtros
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Tabela de Procedimentos -->
                            <table id="procedimentosTable" class="table table-hover table-striped table-bordered mt-3">
                                <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Tipo</th>
                                    <th>Procedimento</th>
                                    <th>Procedimento Específico</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row["codigo"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["tipo"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["procedimento"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["procedimento_especifico"]); ?></td>
                                            <td>
                                                <!-- Botão para Visualizar o Procedimento -->
                                                <a href="procedimento.php?action=visualizar&id=<?php echo $row['idProcedimento']; ?>" class="btn btn-success btn-sm" title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <!-- Botão para Editar o Procedimento -->
                                                <a href="procedimento.php?action=editar&id=<?php echo $row['idProcedimento']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum procedimento encontrado</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Paginação -->
                            <?php if ($totalPaginas > 1): ?>
                                <div class="row">
                                    <div class="col-12">
                                        <ul class="pagination pagination-sm justify-content-center">
                                            <li class="page-item <?php if($paginaAtual == 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=1"><i class="bi bi-chevron-double-left"></i> Primeira</a>
                                            </li>
                                            <li class="page-item <?php if($paginaAtual == 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
                                            </li>
                                            <?php for ($i = max(1, $paginaAtual - 2); $i <= min($paginaAtual + 2, $totalPaginas); $i++): ?>
                                                <li class="page-item <?php echo $paginaAtual == $i ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>">Próxima <i class="bi bi-chevron-right"></i></a>
                                            </li>
                                            <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $totalPaginas; ?>">Última <i class="bi bi-chevron-double-right"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../includes/footer.php'; ?>

<script>
    // Ocultar mensagens de sucesso após 3 segundos
    setTimeout(function() {
        var successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 3000);

    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
