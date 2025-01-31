<?php
// Conexão com o banco de dados
require '../includes/dbconnect.php';

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$buscaUnidade = isset($_GET['buscaUnidade']) ? $_GET['buscaUnidade'] : '';
$buscaCNPJ = isset($_GET['buscaCNPJ']) ? $_GET['buscaCNPJ'] : '';

// Consulta SQL base
$sql = "SELECT id_prestador, unidade_prestadora, cnpj, cidade FROM prestadores WHERE 1=1";

// Arrays para armazenar tipos e parâmetros para consultas
$types = '';
$params = [];

// Filtros de busca dinamicamente
if ($buscaUnidade != '') {
    $sql .= " AND unidade_prestadora LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaUnidade . '%';
}
if ($buscaCNPJ != '') {
    $sql .= " AND cnpj LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaCNPJ . '%';
}

// Contagem total de registros para paginação
$sqlCount = "SELECT COUNT(*) as total FROM prestadores WHERE 1=1";

// Filtros para a contagem de registros
if ($buscaUnidade != '') {
    $sqlCount .= " AND unidade_prestadora LIKE '%$buscaUnidade%'";
}
if ($buscaCNPJ != '') {
    $sqlCount .= " AND cnpj LIKE '%$buscaCNPJ%'";
}

$resultCount = $conn->query($sqlCount);
$totalRegistros = $resultCount->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Adicionar limite e offset para paginação
$sql .= " LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limiteExibicao;
$offset = ($paginaAtual - 1) * $limiteExibicao;
$params[] = $offset;

// Preparar e executar consulta
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<!-- Bootstrap Icons CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Main Content -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Unidades Prestadoras</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Unidades Prestadoras</li>
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
                            <h3 class="card-title"><i class="bi bi-search"></i> Buscar Unidades Prestadoras</h3>
                            <a href="unidade_prestadora.php?action=novo" class="btn btn-primary ms-auto">
                                <i class="bi bi-plus-lg"></i> Cadastrar Unidade
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="" method="get">
                                <div class="row">
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                            <input type="text" id="buscaUnidade" name="buscaUnidade" class="form-control" value="<?php echo htmlspecialchars($buscaUnidade); ?>" placeholder="Nome da Unidade Prestadora">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                            <input type="text" id="buscaCNPJ" name="buscaCNPJ" class="form-control" value="<?php echo htmlspecialchars($buscaCNPJ); ?>" placeholder="CNPJ">
                                        </div>
                                    </div>

                                    <!-- Botões de Filtrar e Limpar Filtros -->
                                    <div class="col-lg-4 col-md-4 col-12 d-flex align-items-center">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-funnel"></i> Filtrar
                                        </button>
                                        <a href="listar_unidade_prestadora.php" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i> Limpar Filtros
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Tabela de Unidades Prestadoras -->
                            <table class="table table-hover table-striped table-bordered mt-3">
                                <thead class="table-dark">
                                <tr>
                                    <th>Unidade Prestadora</th>
                                    <th>CNPJ</th>
                                    <th>Cidade</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['unidade_prestadora']); ?></td>
                                            <td><?= htmlspecialchars($row['cnpj']); ?></td>
                                            <td><?= htmlspecialchars($row['cidade']); ?></td>
                                            <td>
                                                <a href="unidade_prestadora.php?action=visualizar&id=<?= $row['id_prestador'] ?>" class="btn btn-success btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="unidade_prestadora.php?action=editar&id=<?= $row['id_prestador'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhuma unidade prestadora encontrada</td>
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
                                                                    <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
                                                                </li>
                                                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                                                    <li class="page-item <?= $paginaAtual == $i ? 'active' : '' ?>">
                                                                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                                                    </li>
                                                                <?php endfor; ?>
                                                                <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                                    <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>">Próxima <i class="bi bi-chevron-right"></i></a>
                                                                </li>
                                                                <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?= $totalPaginas ?>">Última <i class="bi bi-chevron-double-right"></i></a>
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

<?php include '../includes/footer.php'; ?>

<script>
    setTimeout(function() {
        var successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 3000);

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
