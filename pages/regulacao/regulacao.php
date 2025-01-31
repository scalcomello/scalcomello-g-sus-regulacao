<?php
require '../../includes/dbconnect.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit;
}

// Definir o fuso horário para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// Variáveis para filtros
$filtroCPF = $_POST['filtroCPF'] ?? '';
$filtroNome = $_POST['filtroNome'] ?? ($_GET['filtroNome'] ?? '');
$filtroProcedimento = $_POST['filtroProcedimento'] ?? ($_GET['filtroProcedimento'] ?? '');
$filtroDataSolicitacao = $_POST['filtroDataSolicitacao'] ?? ($_GET['filtroDataSolicitacao'] ?? '');

// Paginação
$limiteExibicao = $_GET['limiteExibicao'] ?? 10;
$paginaAtual = $_GET['pagina'] ?? 1;
$offset = ($paginaAtual - 1) * $limiteExibicao;

// Montar a query SQL com base nos filtros
$sql = "SELECT s.idSolicitacao, c.no_cidadao AS nome_cidadao, c.dt_nascimento AS data_nascimento, 
        p.procedimento, s.classificacao, s.data_recebido_secretaria
        FROM solicitacao s
        JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE s.regulacao = 1";

// Aplicar filtros
$params = [];
$types = '';

if (!empty($filtroNome)) {
    $sql .= " AND c.no_cidadao LIKE ?";
    $params[] = '%' . $filtroNome . '%';
    $types .= 's';
}

if (!empty($filtroProcedimento)) {
    $sql .= " AND p.procedimento LIKE ?";
    $params[] = '%' . $filtroProcedimento . '%';
    $types .= 's';
}

if (!empty($filtroDataSolicitacao)) {
    $sql .= " AND DATE(s.data_recebido_secretaria) = ?";
    $params[] = $filtroDataSolicitacao;
    $types .= 's';
}

$sql .= " ORDER BY s.data_recebido_secretaria DESC, s.classificacao DESC LIMIT ? OFFSET ?";
$params[] = $limiteExibicao;
$params[] = $offset;
$types .= 'ii';

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Erro na preparação da query: ' . $conn->error);
}

$stmt->bind_param($types, ...$params);

// Executar e verificar se a execução foi bem-sucedida
if (!$stmt->execute()) {
    die('Erro na execução da query: ' . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die('Erro ao obter o resultado: ' . $stmt->error);
}

// Contagem de registros totais para paginação
$sqlCount = "SELECT COUNT(*) as total FROM solicitacao s 
            JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
            JOIN procedimento p ON s.procedimento_id = p.idProcedimento
            WHERE s.regulacao = 1";

// Aplicar os mesmos filtros na contagem de registros
$countParams = [];
$countTypes = '';

if (!empty($filtroNome)) {
    $sqlCount .= " AND c.no_cidadao LIKE ?";
    $countParams[] = '%' . $filtroNome . '%';
    $countTypes .= 's';
}

if (!empty($filtroProcedimento)) {
    $sqlCount .= " AND p.procedimento LIKE ?";
    $countParams[] = '%' . $filtroProcedimento . '%';
    $countTypes .= 's';
}

if (!empty($filtroDataSolicitacao)) {
    $sqlCount .= " AND DATE(s.data_recebido_secretaria) = ?";
    $countParams[] = $filtroDataSolicitacao;
    $countTypes .= 's';
}

$stmtCount = $conn->prepare($sqlCount);
if (!$stmtCount) {
    die('Erro na preparação da query de contagem: ' . $conn->error);
}

if (!empty($countParams)) {
    $stmtCount->bind_param($countTypes, ...$countParams);
}

if (!$stmtCount->execute()) {
    die('Erro na execução da query de contagem: ' . $stmtCount->error);
}

$countResult = $stmtCount->get_result();
if (!$countResult) {
    die('Erro ao obter o resultado da contagem: ' . $stmtCount->error);
}

$totalRegistros = $countResult->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Incluir o cabeçalho e a barra lateral
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Bootstrap Icons CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Main Content -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Regulação</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Regulação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../includes/notificacoes.php'; ?>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="bi bi-search"></i> Lista de Pacientes Aguardando Regulação</h3>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" name="filtroNome" id="filtroNome" value="<?= htmlspecialchars($filtroNome) ?>" placeholder="Nome do Paciente">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                            <input type="text" class="form-control" name="filtroProcedimento" id="filtroProcedimento" value="<?= htmlspecialchars($filtroProcedimento) ?>" placeholder="Procedimento">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <input type="date" class="form-control" name="filtroDataSolicitacao" id="filtroDataSolicitacao" value="<?= htmlspecialchars($filtroDataSolicitacao) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-funnel"></i> Filtrar
                                        </button>
                                        <a href="regulacao.php" class="btn btn-secondary">
                                            <i class="bi bi-eraser"></i> Limpar Filtros
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <table class="table table-hover table-striped table-bordered mt-3">
                            <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data de Nascimento</th>
                                <th>Procedimento</th>
                                <th>Classificação</th>
                                <th>Data de Solicitação</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nome_cidadao']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['data_nascimento'])) ?></td>
                                        <td><?= htmlspecialchars($row['procedimento']) ?></td>
                                        <td><?= htmlspecialchars($row['classificacao']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['data_recebido_secretaria'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="acao_regulacao.php?id=<?= $row['idSolicitacao'] ?>&acao=aprovar" class="btn btn-success btn-sm" title="Aprovar">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalReprovar<?= $row['idSolicitacao'] ?>" title="Reprovar">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                                <a href="exibir_lista_regulacao.php?id=<?= $row['idSolicitacao'] ?>" class="btn btn-info btn-sm" title="Visualizar">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>

                                            <!-- Modal de Reprovação -->
                                            <div class="modal fade" id="modalReprovar<?= $row['idSolicitacao'] ?>" tabindex="-1" aria-labelledby="modalReprovarLabel<?= $row['idSolicitacao'] ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="acao_regulacao.php?id=<?= $row['idSolicitacao'] ?>&acao=reprovar" method="POST">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #dc3545; color: white;">
                                                                <h5 class="modal-title" id="modalReprovarLabel<?= $row['idSolicitacao'] ?>">Reprovar Solicitação</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="justificativa_regulacao<?= $row['idSolicitacao'] ?>" class="form-label">Justificativa</label>
                                                                    <textarea class="form-control" name="justificativa_regulacao" id="justificativa_regulacao<?= $row['idSolicitacao'] ?>" rows="4" required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="tipo_reprovacao<?= $row['idSolicitacao'] ?>" class="form-label">Tipo de Reprovação</label>
                                                                    <select class="form-select" name="tipo_reprovacao" id="tipo_reprovacao<?= $row['idSolicitacao'] ?>" required>
                                                                        <option value="">Selecione...</option>
                                                                        <option value="fila">Enviar para Fila</option>
                                                                        <option value="finalizar">Finalizar Solicitação</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-danger">Reprovar</button>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Nenhum paciente encontrado</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Paginação -->
                        <?php if ($totalPaginas > 1): ?>
                            <div class="row">
                                <div class="col-12">
                                    <ul class="pagination pagination-sm justify-content-center">
                                        <li class="page-item <?= ($paginaAtual == 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?pagina=1&filtroNome=<?= urlencode($filtroNome) ?>&filtroProcedimento=<?= urlencode($filtroProcedimento) ?>&filtroDataSolicitacao=<?= urlencode($filtroDataSolicitacao) ?>">
                                                <i class="bi bi-chevron-double-left"></i> Primeira
                                            </a>
                                        </li>
                                        <li class="page-item <?= ($paginaAtual == 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&filtroNome=<?= urlencode($filtroNome) ?>&filtroProcedimento=<?= urlencode($filtroProcedimento) ?>&filtroDataSolicitacao=<?= urlencode($filtroDataSolicitacao) ?>">
                                                <i class="bi bi-chevron-left"></i> Anterior
                                            </a>
                                        </li>
                                        <?php for ($i = max(1, $paginaAtual - 2); $i <= min($paginaAtual + 2, $totalPaginas); $i++): ?>
                                            <li class="page-item <?= ($paginaAtual == $i) ? 'active' : '' ?>">
                                                <a class="page-link" href="?pagina=<?= $i ?>&filtroNome=<?= urlencode($filtroNome) ?>&filtroProcedimento=<?= urlencode($filtroProcedimento) ?>&filtroDataSolicitacao=<?= urlencode($filtroDataSolicitacao) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($paginaAtual == $totalPaginas) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&filtroNome=<?= urlencode($filtroNome) ?>&filtroProcedimento=<?= urlencode($filtroProcedimento) ?>&filtroDataSolicitacao=<?= urlencode($filtroDataSolicitacao) ?>">
                                                Próxima <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item <?= ($paginaAtual == $totalPaginas) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $totalPaginas ?>&filtroNome=<?= urlencode($filtroNome) ?>&filtroProcedimento=<?= urlencode($filtroProcedimento) ?>&filtroDataSolicitacao=<?= urlencode($filtroDataSolicitacao) ?>">
                                                Última <i class="bi bi-chevron-double-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
