<?php
session_start();
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// Obter todas as competências
$sqlCompetencias = "SELECT * FROM competencias";
$resultCompetencias = $conn->query($sqlCompetencias);

if ($resultCompetencias === false) {
    die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
}

// Verificar se uma competência foi selecionada
if (isset($_POST['competencia_id'])) {
    $_SESSION['competencia_id'] = (int)$_POST['competencia_id'];
}

$competenciaId = isset($_SESSION['competencia_id']) ? $_SESSION['competencia_id'] : null;
$dataInicio = '';
$dataFim = '';
$nomeCompetencia = '';

if ($competenciaId) {
    $sqlCompetencia = "SELECT * FROM competencias WHERE id = $competenciaId";
    $resultCompetencia = $conn->query($sqlCompetencia);

    if ($resultCompetencia && $resultCompetencia->num_rows > 0) {
        $competencia = $resultCompetencia->fetch_assoc();
        $dataInicio = date('d/m/Y', strtotime($competencia['data_inicial']));
        $dataFim = date('d/m/Y', strtotime($competencia['data_final']));
        $nomeCompetencia = $competencia['nome'];
    }
}

// Verificar se os filtros foram submetidos
$filtroCPF = isset($_POST['filtroCPF']) ? $_POST['filtroCPF'] : '';
$filtroNome = isset($_POST['filtroNome']) ? $_POST['filtroNome'] : '';
$filtroCNS = isset($_POST['filtroCNS']) ? $_POST['filtroCNS'] : '';

// Montar a query SQL com base nos filtros
$sql = "SELECT s.idSolicitacao, c.no_cidadao AS nome_cidadao, DATE_FORMAT(c.dt_nascimento, '%d/%m/%Y') AS data_nascimento, p.procedimento, s.classificacao, DATE_FORMAT(s.data_recebido_secretaria, '%d/%m/%Y') AS data_recebido_secretaria, s.status_procedimento
        FROM solicitacao s
        JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE p.procedimento = 'Exames Laboratoriais'";

if (!empty($filtroCPF)) {
    $sql .= " AND c.nu_cpf = '$filtroCPF'";
}
if (!empty($filtroNome)) {
    $sql .= " AND c.no_cidadao LIKE '%$filtroNome%'";
}
if (!empty($filtroCNS)) {
    $sql .= " AND c.nu_cns = '$filtroCNS'";
}
if (!empty($dataInicio)) {
    $sql .= " AND s.data_recebido_secretaria >= STR_TO_DATE('$dataInicio', '%d/%m/%Y')";
}
if (!empty($dataFim)) {
    $sql .= " AND s.data_recebido_secretaria <= STR_TO_DATE('$dataFim', '%d/%m/%Y')";
}

$sql .= " ORDER BY s.data_recebido_secretaria DESC, s.classificacao DESC"; // Ordenar por data_recebido_secretaria e classificação

$result = $conn->query($sql);

if ($result === false) {
    die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
}

// Calcular o total previsionado
$sqlTotalPrevisionado = "SELECT SUM(el.valor_unitario) as total
                         FROM solicitacao s
                         JOIN exames_laboratoriais_solicitacao els ON s.idSolicitacao = els.solicitacao_id
                         JOIN exames_laboratoriais el ON els.exame_id = el.id
                         WHERE s.data_recebido_secretaria >= STR_TO_DATE('$dataInicio', '%d/%m/%Y') AND s.data_recebido_secretaria <= STR_TO_DATE('$dataFim', '%d/%m/%Y')";

$resultTotalPrevisionado = $conn->query($sqlTotalPrevisionado);

if ($resultTotalPrevisionado === false) {
    die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
}

$totalPrevisionado = $resultTotalPrevisionado->fetch_assoc()['total'] ?? 0;

// Buscar os dois laboratórios mais relevantes
$sqlLaboratorios = "SELECT unidade_prestadora 
                    FROM prestadores 
                    WHERE tipo = 'LABORATORIO E ANALISES CLINICAS' 
                    LIMIT 2";

$resultLaboratorios = $conn->query($sqlLaboratorios);

if ($resultLaboratorios === false) {
    die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
}

$laboratorios = [];
$sqlLaboratorios = "SELECT p.unidade_prestadora, SUM(el.valor_unitario) as total_agendado 
                    FROM solicitacao s
                    JOIN exames_laboratoriais_solicitacao els ON s.idSolicitacao = els.solicitacao_id
                    JOIN exames_laboratoriais el ON els.exame_id = el.id
                    JOIN prestadores p ON s.idPrestador = p.id_prestador
                    WHERE p.tipo = 'LABORATORIO E ANALISES CLINICAS'
                    AND s.data_recebido_secretaria >= STR_TO_DATE('$dataInicio', '%d/%m/%Y')
                    AND s.data_recebido_secretaria <= STR_TO_DATE('$dataFim', '%d/%m/%Y')
                    GROUP BY p.unidade_prestadora
                    LIMIT 2";

$resultLaboratorios = $conn->query($sqlLaboratorios);

if ($resultLaboratorios === false) {
    die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
}

while ($row = $resultLaboratorios->fetch_assoc()) {
    $laboratorios[] = [
        'unidade_prestadora' => $row['unidade_prestadora'],
        'total_agendado' => $row['total_agendado']
    ];
}


include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lista de Agendamento</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#criarCompetenciaModal">
                        Criar Competência
                    </button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#selecionarCompetenciaModal">
                        Selecionar Competência
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Competência e Período -->
            <div class="row">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h5>Competência: <?= htmlspecialchars($nomeCompetencia) ?></h5>
                        <p>Período: <?= htmlspecialchars($dataInicio) ?> a <?= htmlspecialchars($dataFim) ?></p>
                    </div>
                </div>
            </div>

            <!-- Bloco para exibir o total previsionado -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-;icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Previsionado</span>
                            <span class="info-box-number">R$ <?= number_format($totalPrevisionado, 2, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <?php foreach ($laboratorios as $laboratorio): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-orange">
                            <span class="info-box-icon"><i class="fas fa-flask"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= htmlspecialchars($laboratorio['unidade_prestadora']) ?></span>
                                <span class="info-box-number">Agendado: R$ <?= number_format($laboratorio['total_agendado'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>



            <!-- Modal para Selecionar Competência -->
            <div class="modal fade" id="selecionarCompetenciaModal" tabindex="-1" role="dialog" aria-labelledby="selecionarCompetenciaModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selecionarCompetenciaModalLabel">Selecionar Competência</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="competenciaSelect">Selecionar Competência</label>
                                    <select class="form-control" id="competenciaSelect" name="competencia_id" required>
                                        <option value="">Selecione uma competência</option>
                                        <?php
                                        $resultCompetencias->data_seek(0); // Reset the result set pointer
                                        while ($row = $resultCompetencias->fetch_assoc()): ?>
                                            <option value="<?= $row['id'] ?>" <?= $competenciaId == $row['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['nome']) ?> (<?= htmlspecialchars(date('d/m/Y', strtotime($row['data_inicial']))) ?> a <?= htmlspecialchars(date('d/m/Y', strtotime($row['data_final']))) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Selecionar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal para Criar Competência -->
            <div class="modal fade" id="criarCompetenciaModal" tabindex="-1" role="dialog" aria-labelledby="criarCompetenciaModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="criarCompetenciaModalLabel">Criar Competência</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="criar_competencia.php">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nomeCompetencia">Nome da Competência</label>
                                    <input type="text" class="form-control" id="nomeCompetencia" name="nome" required>
                                </div>
                                <div class="form-group">
                                    <label for="dataInicialCompetencia">Data Inicial</label>
                                    <input type="date" class="form-control" id="dataInicialCompetencia" name="data_inicial" required>
                                </div>
                                <div class="form-group">
                                    <label for="dataFinalCompetencia">Data Final</label>
                                    <input type="date" class="form-control" id="dataFinalCompetencia" name="data_final" required>
                                </div>
                                <div class="form-group">
                                    <label for="descricaoCompetencia">Descrição</label>
                                    <textarea class="form-control" id="descricaoCompetencia" name="descricao"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Salvar Competência</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filtrar Resultados</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="filtroCPF">CPF:</label>
                                    <input type="text" class="form-control" name="filtroCPF" id="filtroCPF" placeholder="Digite o CPF" value="<?= htmlspecialchars($filtroCPF) ?>">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="filtroNome">Nome:</label>
                                    <input type="text" class="form-control" name="filtroNome" id="filtroNome" placeholder="Digite o nome" value="<?= htmlspecialchars($filtroNome) ?>">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="filtroCNS">CNS:</label>
                                    <input type="text" class="form-control" name="filtroCNS" id="filtroCNS" placeholder="Digite o CNS" value="<?= htmlspecialchars($filtroCNS) ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="exames_laboratoriais.php" class="btn btn-secondary ml-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Abas para Pacientes aguardando agendamento e Pacientes agendados -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="aguardando-tab" data-toggle="tab" href="#aguardando" role="tab" aria-controls="aguardando" aria-selected="true">Pacientes aguardando agendamento</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="agendados-tab" data-toggle="tab" href="#agendados" role="tab" aria-controls="agendados" aria-selected="false">Pacientes agendados</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="aguardando" role="tabpanel" aria-labelledby="aguardando-tab">
                                    <?php if ($result === false): ?>
                                        <div class="alert alert-danger">
                                            Erro na consulta SQL: <?= htmlspecialchars($conn->error) ?>
                                        </div>
                                    <?php elseif ($result->num_rows == 0): ?>
                                        <div class="alert alert-info">
                                            Nenhum resultado encontrado.
                                        </div>
                                    <?php else: ?>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Data de Nascimento</th>
                                                <th>Procedimento</th>
                                                <th>Classificação</th>
                                                <th>Status do Procedimento</th>
                                                <th>Data de Solicitação</th>
                                                <th>Ações</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <?php if ($row['status_procedimento'] == 'Aguardando'): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($row['nome_cidadao']) ?></td>
                                                        <td><?= htmlspecialchars($row['data_nascimento']) ?></td>
                                                        <td><?= htmlspecialchars($row['procedimento']) ?></td>
                                                        <td><?= htmlspecialchars($row['classificacao']) ?></td>
                                                        <td><?= htmlspecialchars($row['status_procedimento']) ?></td>
                                                        <td><?= htmlspecialchars($row['data_recebido_secretaria']) ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="exibir_lista_agendamento_exames.php?id=<?= $row['idSolicitacao'] ?>" title="Visualizar" class="btn btn-info"><i class="fas fa-eye"></i></a>

                                                                <a href="complementar_solicitacao.php?id=<?= $row['idSolicitacao'] ?>" title="Editar" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                <?php endif; ?>
                                            <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-pane fade" id="agendados" role="tabpanel" aria-labelledby="agendados-tab">
                                    <?php
                                    // Query para pacientes agendados
                                    $sqlAgendados = "SELECT s.idSolicitacao, c.no_cidadao AS nome_cidadao, DATE_FORMAT(c.dt_nascimento, '%d/%m/%Y') AS data_nascimento, p.procedimento, s.classificacao, DATE_FORMAT(s.data_recebido_secretaria, '%d/%m/%Y') AS data_recebido_secretaria, s.status_procedimento
                     FROM solicitacao s
                     JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                     JOIN procedimento p ON s.procedimento_id = p.idProcedimento
                     WHERE p.procedimento = 'Exames Laboratoriais' AND s.status_procedimento = 'Agendado'
                     ORDER BY s.data_recebido_secretaria DESC, s.classificacao DESC";

                                    $resultAgendados = $conn->query($sqlAgendados);

                                    if ($resultAgendados === false) {
                                        die("Erro na consulta SQL: " . htmlspecialchars($conn->error));
                                    }
                                    ?>
                                    <?php if ($resultAgendados->num_rows == 0): ?>
                                        <div class="alert alert-info">
                                            Nenhum resultado encontrado.
                                        </div>
                                    <?php else: ?>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Data de Nascimento</th>
                                                <th>Procedimento</th>
                                                <th>Classificação</th>
                                                <th>Status do Procedimento</th>
                                                <th>Data de Solicitação</th>
                                                <th>Ações</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php while ($row = $resultAgendados->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['nome_cidadao']) ?></td>
                                                    <td><?= htmlspecialchars($row['data_nascimento']) ?></td>
                                                    <td><?= htmlspecialchars($row['procedimento']) ?></td>
                                                    <td><?= htmlspecialchars($row['classificacao']) ?></td>
                                                    <td><?= htmlspecialchars($row['status_procedimento']) ?></td>
                                                    <td><?= htmlspecialchars($row['data_recebido_secretaria']) ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="exibir_lista_agendamento_exames.php?id=<?= $row['idSolicitacao'] ?>" title="Visualizar" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                                            <a target="_blank" href="script_guia_exame_sangue.php?id=<?= $row['idSolicitacao'] ?>" title="Imprimir Guia" class="btn btn-secondary"><i class="fas fa-print"></i></a>
                                                            <a href="complementar_solicitacao.php?id=<?= $row['idSolicitacao'] ?>" title="Editar" class="btn btn-warning"><i class="fas fa-edit"></i></a> <!-- Adicione este botão -->
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>