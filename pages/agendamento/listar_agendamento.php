<?php
// Conexão com o banco de dados
require '../../includes/dbconnect.php';

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$filtroNome = isset($_GET['filtroNome']) ? $_GET['filtroNome'] : '';
$filtroCPF = isset($_GET['filtroCPF']) ? $_GET['filtroCPF'] : '';
$filtroProcedimento = isset($_GET['filtroProcedimento']) ? $_GET['filtroProcedimento'] : '';

// Base da SQL para consulta de agendamentos
$sql = "SELECT s.idSolicitacao, c.no_cidadao AS nome, c.nu_cpf AS cpf, 
        DATE_FORMAT(c.dt_nascimento, '%d-%m-%Y') AS data_nascimento, 
        p.procedimento, s.classificacao, s.status_procedimento, 
        DATE_FORMAT(s.data_recebido_secretaria, '%d-%m-%Y') AS data_solicitacao 
        FROM solicitacao s
        JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE p.procedimento != 'Exames Laboratoriais'";

// Arrays para armazenar tipos e parâmetros
$types = '';
$params = [];

// Adicionar condições dinamicamente conforme os filtros
if ($filtroNome != '') {
    $sql .= " AND c.no_cidadao LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroNome . '%';
}
if ($filtroCPF != '') {
    $sql .= " AND c.nu_cpf LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroCPF . '%';
}
if ($filtroProcedimento != '') {
    $sql .= " AND p.procedimento LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroProcedimento . '%';
}

// Executar a contagem total de registros para paginação
$sqlCount = "SELECT COUNT(*) as total FROM solicitacao s
             JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
             JOIN procedimento p ON s.procedimento_id = p.idProcedimento
             WHERE p.procedimento != 'Exames Laboratoriais'";
if ($filtroNome != '') $sqlCount .= " AND c.no_cidadao LIKE '%$filtroNome%'";
if ($filtroCPF != '') $sqlCount .= " AND c.nu_cpf LIKE '%$filtroCPF%'";
if ($filtroProcedimento != '') $sqlCount .= " AND p.procedimento LIKE '%$filtroProcedimento%'";

// Executar a contagem total de registros
$resultCount = $conn->query($sqlCount);
$totalRegistros = $resultCount->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Adicionar limite e offset para paginação
$sql .= " LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limiteExibicao;
$params[] = ($paginaAtual - 1) * $limiteExibicao;

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

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<!-- CSS para ajustar a coluna de ações -->
<style>
    .col-acoes {
        width: 120px; /* Ajuste a largura conforme necessário */
        white-space: nowrap;
        text-align: center;
    }

    .col-acoes a {
        margin-right: 5px; /* Espaçamento entre os ícones */
    }
</style>

<!-- Main Content -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Agendamentos</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Agendamentos</li>
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
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-search"></i> Buscar Agendamentos</h3>
                </div>

                <div class="card-body">
                    <form action="" method="get">
                        <div class="row gy-3">
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="filtroNome" name="filtroNome" class="form-control" value="<?php echo htmlspecialchars($filtroNome); ?>" placeholder="Nome do Cidadão">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                    <input type="text" id="filtroCPF" name="filtroCPF" class="form-control" value="<?php echo htmlspecialchars($filtroCPF); ?>" placeholder="CPF">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-medical"></i></span>
                                    <input type="text" id="filtroProcedimento" name="filtroProcedimento" class="form-control" value="<?php echo htmlspecialchars($filtroProcedimento); ?>" placeholder="Procedimento">
                                </div>
                            </div>
                        </div>

                        <div class="row gy-3 mt-2">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="listar_agendamento.php" class="btn btn-secondary">
                                    <i class="bi bi-eraser"></i> Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Agendamentos -->
                    <table id="agendamentoTable" class="table table-hover table-striped table-bordered mt-3">
                        <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data de Nascimento</th>
                            <th>Procedimento</th>
                            <th>Classificação</th>
                            <th>Status do Procedimento</th>
                            <th>Data de Solicitação</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                                    <td><?php echo formatarCPF($row["cpf"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["data_nascimento"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["procedimento"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["classificacao"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["status_procedimento"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["data_solicitacao"]); ?></td>
                                    <td class="col-acoes">
                                        <a href="inserir_agendamento.php?id=<?php echo $row['idSolicitacao']; ?>" class="btn btn-success btn-sm" title="Agendar">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>
                                        <a href="exibir_lista_agendamento.php?id=<?php echo $row['idSolicitacao']; ?>" class="btn btn-info btn-sm" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="reagendar.php?id=<?php echo $row['idSolicitacao']; ?>" class="btn btn-warning btn-sm btn-reagendar" title="Reagendar" data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                                            <i class="bi bi-calendar-event"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhum agendamento encontrado</td>
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
<?php include '../../includes/footer.php'; ?>

<script>
    function confirmarReagendamento(nomePaciente) {
        return confirm(`O paciente ${nomePaciente} será remarcado e seu status será ajustado para retornar ao final da fila com a data de hoje. Este procedimento será finalizado. Deseja continuar?`);
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".btn-reagendar").forEach(function(button) {
            button.addEventListener("click", function(event) {
                const nomePaciente = button.dataset.nome;

                if (!confirmarReagendamento(nomePaciente)) {
                    event.preventDefault();
                }
            });
        });
    });
</script>

<?php
function formatarCPF($cpf) {
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}
?>
