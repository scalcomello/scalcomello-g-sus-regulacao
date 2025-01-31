<?php
// Conexão com o banco de dados
require '../includes/dbconnect.php';

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$buscaNome = isset($_GET['buscaNome']) ? $_GET['buscaNome'] : '';
$buscaCRM = isset($_GET['buscaCRM']) ? $_GET['buscaCRM'] : '';
$buscaEspecialidade = isset($_GET['buscaEspecialidade']) ? $_GET['buscaEspecialidade'] : '';

// Base da SQL
$sql = "SELECT idMedico, nome, crm, especialidade FROM medico WHERE 1=1";

// Arrays para armazenar tipos e parâmetros
$types = '';
$params = [];

// Adicionar condições dinamicamente conforme os filtros
if ($buscaNome != '') {
    $sql .= " AND nome LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaNome . '%';
}
if ($buscaCRM != '') {
    $sql .= " AND crm LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaCRM . '%';
}
if ($buscaEspecialidade != '') {
    $sql .= " AND especialidade LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaEspecialidade . '%';
}

// Executar a contagem total de registros para paginação
$sqlCount = "SELECT COUNT(*) as total FROM medico WHERE 1=1";

// Adicionar condições ao SQL da contagem, conforme os filtros
if ($buscaNome != '') {
    $sqlCount .= " AND nome LIKE '%$buscaNome%'";
}
if ($buscaCRM != '') {
    $sqlCount .= " AND crm LIKE '%$buscaCRM%'";
}
if ($buscaEspecialidade != '') {
    $sqlCount .= " AND especialidade LIKE '%$buscaEspecialidade%'";
}

// Executar a contagem total de registros
$resultCount = $conn->query($sqlCount);
$totalRegistros = $resultCount->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Adicionar limite e offset para paginação
$sql .= " LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limiteExibicao;
$offset = ($paginaAtual - 1) * $limiteExibicao;
$params[] = $offset;

// Preparar e executar a consulta com bind dos parâmetros
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $bind_names[] = $types;
    foreach ($params as $key => $value) {
        $bind_name = 'bind' . $key;
        $$bind_name = $value;
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
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
                    <h3 class="mb-0">Médicos</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Médicos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de busca e exibição -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="bi bi-search"></i> Buscar Médicos</h3>
                    <a href="medico.php?action=novo" class="btn btn-primary ms-auto">
                        <i class="bi bi-plus-lg"></i> Cadastrar Médico
                    </a>
                </div>

                <div class="card-body">
                    <form action="" method="get">
                        <div class="row gy-3">
                            <!-- Primeira linha: Nome, CRM e Especialidade -->
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                    <input type="text" id="buscaNome" name="buscaNome" class="form-control" value="<?php echo htmlspecialchars($buscaNome); ?>" placeholder="Nome do Médico">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" id="buscaCRM" name="buscaCRM" class="form-control" value="<?php echo htmlspecialchars($buscaCRM); ?>" placeholder="CRM">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clipboard-pulse"></i></span>
                                    <input type="text" id="buscaEspecialidade" name="buscaEspecialidade" class="form-control" value="<?php echo htmlspecialchars($buscaEspecialidade); ?>" placeholder="Especialidade">
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Filtrar e Limpar -->
                        <div class="row gy-3 mt-2">
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-start">
                                    <!-- Botão Filtrar -->
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-funnel"></i> Filtrar
                                    </button>
                                    <!-- Botão Limpar Filtros -->
                                    <a href="listar_medico.php" class="btn btn-secondary">
                                        <i class="bi bi-eraser"></i> Limpar Filtros
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Médicos -->
                    <table id="medicoTable" class="table table-hover table-striped table-bordered mt-3">
                        <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>CRM</th>
                            <th>Especialidade</th>
                            <th>Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["crm"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["especialidade"]); ?></td>
                                    <td>
                                        <a href="medico.php?action=visualizar&id=<?php echo $row['idMedico']; ?>" class="btn btn-success btn-sm" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="medico.php?action=editar&id=<?php echo $row['idMedico']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum médico encontrado</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Paginação -->
                    <?php if ($totalPaginas > 1): ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../includes/footer.php'; ?>

<script>
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>