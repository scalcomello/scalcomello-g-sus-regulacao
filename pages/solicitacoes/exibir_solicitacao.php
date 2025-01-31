<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// Função para validação e sanitização de entrada
function sanitize_input($input) {
    return htmlspecialchars(strip_tags($input));
}

// Definir o número de registros por página e a página atual
$registrosPorPagina = isset($_GET['registrosPorPagina']) ? intval($_GET['registrosPorPagina']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

// Verificar se os filtros foram submetidos
$filtroNome = isset($_GET['filtroNome']) ? sanitize_input($_GET['filtroNome']) : '';
$filtroCPF = isset($_GET['filtroCPF']) ? sanitize_input($_GET['filtroCPF']) : '';
$filtroCNS = isset($_GET['filtroCNS']) ? sanitize_input($_GET['filtroCNS']) : '';
$filtroProtocolo = isset($_GET['filtroProtocolo']) ? sanitize_input($_GET['filtroProtocolo']) : '';
$filtroProcedimento = isset($_GET['filtroProcedimento']) ? sanitize_input($_GET['filtroProcedimento']) : '';
$filtroDataInicio = isset($_GET['filtroDataInicio']) ? sanitize_input($_GET['filtroDataInicio']) : '';
$filtroDataFim = isset($_GET['filtroDataFim']) ? sanitize_input($_GET['filtroDataFim']) : '';
$filtroUltimos30Dias = isset($_GET['filtroUltimos30Dias']) ? $_GET['filtroUltimos30Dias'] : '';

$sqlBase = "SELECT s.idSolicitacao, s.data_solicitacao, s.data_recebido_secretaria, s.classificacao, s.status_procedimento, 
                   c.no_cidadao AS nome_cidadao, c.nu_cpf AS cpf, c.nu_cns AS cns, m.nome AS nome_medico, p.procedimento, 
                   s.numero_protocolo, s.idMedico, s.data_encerramento, s.justificativa_encerramento, s.justificativa_regulacao, pr.unidade_prestadora, 
                   s.data_agendamento_clinica, s.hora_agendamento, s.tipo_procedimento, s.status_reagendamento, c.st_faleceu
            FROM solicitacao s 
            JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao 
            JOIN medico m ON s.idMedico = m.idMedico 
            JOIN procedimento p ON s.procedimento_id = p.idProcedimento 
            LEFT JOIN prestadores pr ON s.idPrestador = pr.id_prestador
            WHERE 1=1";

$sqlCondicoes = "";

if (!empty($filtroNome)) {
    $sqlCondicoes .= " AND c.no_cidadao LIKE '%$filtroNome%'";
}
if (!empty($filtroCPF)) {
    $sqlCondicoes .= " AND c.nu_cpf LIKE '%$filtroCPF%'";
}
if (!empty($filtroCNS)) {
    $sqlCondicoes .= " AND c.nu_cns LIKE '%$filtroCNS%'";
}
if (!empty($filtroProtocolo)) {
    $sqlCondicoes .= " AND s.numero_protocolo LIKE '%$filtroProtocolo%'";
}
if (!empty($filtroProcedimento)) {
    $sqlCondicoes .= " AND p.procedimento LIKE '%$filtroProcedimento%'";
}
if (!empty($filtroDataInicio) && !empty($filtroDataFim)) {
    $sqlCondicoes .= " AND s.data_recebido_secretaria BETWEEN '$filtroDataInicio' AND '$filtroDataFim'";
}
if (!empty($filtroUltimos30Dias)) {
    $sqlCondicoes .= " AND s.data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// Atualização para verificar a coluna st_faleceu
$sqlCondicoes .= " AND c.st_faleceu = 0"; // Verifica se o cidadão está vivo

$sqlCount = "SELECT COUNT(*) as total FROM solicitacao s 
             JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao 
             JOIN medico m ON s.idMedico = m.idMedico 
             JOIN procedimento p ON s.procedimento_id = p.idProcedimento 
             LEFT JOIN prestadores pr ON s.idPrestador = pr.id_prestador
             WHERE 1=1 $sqlCondicoes";

$resultCount = $conn->query($sqlCount);
$totalRegistros = $resultCount->fetch_assoc()['total'];



// Atualização da consulta final para ordenar por data_recebido_secretaria
$sqlFinal = $sqlBase . $sqlCondicoes . " ORDER BY s.data_recebido_secretaria DESC LIMIT $offset, $registrosPorPagina";

$result = $conn->query($sqlFinal);

if ($result === false) {
    die("Erro na consulta SQL: " . $conn->error);
}


$result = $conn->query($sqlFinal);

if ($result === false) {
    die("Erro na consulta SQL: " . $conn->error);
}

$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Consultar Solicitações</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Início</a></li>
                            <li class="breadcrumb-item active">Exibir Solicitação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Filtro -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filtrar Resultados</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="d-flex flex-wrap align-items-end gap-3" id="filtroForm">
                                <div class="form-group">
                                    <label for="filtroNome">Nome:</label>
                                    <input type="text" class="form-control" name="filtroNome" id="filtroNome" placeholder="Digite o nome" value="<?= htmlspecialchars($filtroNome) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroCPF">CPF:</label>
                                    <input type="text" class="form-control" name="filtroCPF" id="filtroCPF" placeholder="Digite o CPF" value="<?= htmlspecialchars($filtroCPF) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroCNS">CNS:</label>
                                    <input type="text" class="form-control" name="filtroCNS" id="filtroCNS" placeholder="Digite o CNS" value="<?= htmlspecialchars($filtroCNS) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroProtocolo">Protocolo:</label>
                                    <input type="text" class="form-control" name="filtroProtocolo" id="filtroProtocolo" placeholder="Digite o Protocolo" value="<?= htmlspecialchars($filtroProtocolo) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroProcedimento">Procedimento:</label>
                                    <input type="text" class="form-control" name="filtroProcedimento" id="filtroProcedimento" placeholder="Digite o Procedimento" value="<?= htmlspecialchars($filtroProcedimento) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroDataInicio">Data Início:</label>
                                    <input type="date" class="form-control" name="filtroDataInicio" id="filtroDataInicio" value="<?= htmlspecialchars($filtroDataInicio) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="filtroDataFim">Data Fim:</label>
                                    <input type="date" class="form-control" name="filtroDataFim" id="filtroDataFim" value="<?= htmlspecialchars($filtroDataFim) ?>">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="filtroUltimos30Dias" id="filtroUltimos30Dias" value="1" <?= !empty($filtroUltimos30Dias) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="filtroUltimos30Dias">Últimos 30 dias</label>
                                </div>
                                <div class="form-group">
                                    <label for="registrosPorPagina">Registros por página:</label>
                                    <select class="form-control" name="registrosPorPagina" id="registrosPorPagina" onchange="this.form.submit()">
                                        <option value="10" <?= $registrosPorPagina == 10 ? 'selected' : '' ?>>10</option>
                                        <option value="30" <?= $registrosPorPagina == 30 ? 'selected' : '' ?>>30</option>
                                        <option value="50" <?= $registrosPorPagina == 50 ? 'selected' : '' ?>>50</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="exibir_solicitacao.php" class="btn btn-secondary">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>

                    <!-- Tabela de Resultados -->
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">Número Protocolo</th>
                                    <th style="width: 20%;">Nome e CPF</th>
                                    <th style="width: 10%;">Classificação</th>
                                    <th style="width: 20%;">Procedimento</th>
                                    <th style="width: 15%;">Status do Procedimento</th>
                                    <th style="width: 15%;">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr data-widget="expandable-table" aria-expanded="false">
                                        <td>
                                            <?= htmlspecialchars($row['numero_protocolo']) ?><br>
                                            <small><?= htmlspecialchars(date('d/m/Y', strtotime($row['data_recebido_secretaria']))) ?><br><?= htmlspecialchars(date('H:i:s', strtotime($row['data_recebido_secretaria']))) ?></small>

                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['nome_cidadao']) ?><br>
                                            <small>CPF: <?= htmlspecialchars($row['cpf']) ?><br>CNS: <?= htmlspecialchars($row['cns']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['classificacao']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['procedimento']) ?>
                                            <?php if (!empty($row['tipo_procedimento'])): ?>
                                                <br><small>Tipo: <?= htmlspecialchars($row['tipo_procedimento']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['status_procedimento']) ?>
                                            <?php if ($row['status_reagendamento']): ?>
                                                <br><small>Retorno</small>
                                            <?php endif; ?>
                                            <?php if (!empty($row['dt_obito'])): ?>
                                                <br><small>Paciente em Óbito</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="script_protocolo.php?id=<?= $row['idSolicitacao'] ?>" title="Imprimir Protocolo" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="timeline.php?id=<?= $row['idSolicitacao'] ?>" title="Timeline" class="btn btn-sm btn-info">
                                                    <i class="fas fa-stream"></i>
                                                </a>
                                                <a href="#" data-toggle="modal" data-target="#encerrarModal" data-id="<?= $row['idSolicitacao'] ?>" title="Encerrar Agendamento" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                                <?php if ($row['procedimento'] != 'Exames Laboratoriais' && empty($row['dt_obito']) && in_array($row['status_procedimento'], ['Atendido', 'Finalizado', 'Agendado'])): ?>
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#reagendarModal" data-id="<?= $row['idSolicitacao'] ?>" data-protocolo="<?= $row['numero_protocolo'] ?>" title="Reagendar">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="expandable-body d-none">
                                        <td colspan="6">
                                            <div class="p-3">
                                                <strong>Médico:</strong> <?= htmlspecialchars($row['nome_medico']) ?><br>
                                                <strong>Data da Solicitação:</strong> <?= htmlspecialchars($row['data_solicitacao']) ?><br>
                                                <strong>Data de Encerramento:</strong> <?= htmlspecialchars($row['data_encerramento']) ?><br>
                                                <strong>Justificativa:</strong> <?= htmlspecialchars(!empty($row['justificativa_encerramento']) ? $row['justificativa_encerramento'] : $row['justificativa_regulacao']) ?><br>
                                                <strong>Unidade Prestadora:</strong> <?= htmlspecialchars($row['unidade_prestadora']) ?><br>
                                                <strong>Data de Agendamento na Clínica:</strong> <?= htmlspecialchars($row['data_agendamento_clinica']) ?><br>
                                                <strong>Hora do Agendamento:</strong> <?= htmlspecialchars($row['hora_agendamento']) ?><br>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <?php
                                $maxLinks = 5; // número máximo de links de paginação
                                $start = $paginaAtual - floor($maxLinks / 2);
                                $end = $paginaAtual + floor($maxLinks / 2);
                                if ($start < 1) {
                                    $start = 1;
                                    $end = $maxLinks;
                                }
                                if ($end > $totalPaginas) {
                                    $end = $totalPaginas;
                                    $start = $totalPaginas - $maxLinks + 1;
                                }
                                if ($start < 1) $start = 1;

                                if ($paginaAtual > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&registrosPorPagina=<?= $registrosPorPagina ?>">«</a></li>
                                <?php endif;

                                for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>"><a class="page-link" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>"><?= $i ?></a></li>
                                <?php endfor;

                                if ($paginaAtual < $totalPaginas): ?>
                                    <li class="page-item"><a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&registrosPorPagina=<?= $registrosPorPagina ?>">»</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<!-- Modal para reagendamento -->
<div class="modal fade" id="reagendarModal" tabindex="-1" role="dialog" aria-labelledby="reagendarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reagendarModalLabel">Reagendar Solicitação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reagendarForm" action="reagendar_solicitacao.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="idSolicitacao" id="modalSolicitacaoId">
                    <input type="hidden" name="numeroProtocolo" id="modalNumeroProtocolo">
                    <div class="form-group">
                        <label for="tipoRetorno">Tipo de Retorno:</label>
                        <select class="form-control" name="tipoRetorno" id="tipoRetorno" required>
                            <option value="apos_exames">Retornar após os exames prontos</option>
                            <option value="qtd_dias">Retornar em quantidade de dias</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group" id="qtdDiasGroup" style="display:none;">
                        <label for="qtdDias">Quantidade de Dias:</label>
                        <input type="number" class="form-control" name="qtdDias" id="qtdDias" min="1">
                    </div>
                    <div class="form-group" id="outrosGroup" style="display:none;">
                        <label for="outrosText">Descreva o motivo:</label>
                        <input type="text" class="form-control" name="outrosText" id="outrosText">
                    </div>
                    <div class="form-group">
                        <label for="classificacao">Classificação:</label>
                        <select class="form-control" name="classificacao" id="classificacao" required>
                            <option value="Eletivo">Eletivo</option>
                            <option value="Prioritario">Prioritário</option>
                            <option value="Urgente">Urgente</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Reagendar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para encerrar agendamento -->
<div class="modal fade" id="encerrarModal" tabindex="-1" role="dialog" aria-labelledby="encerrarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="encerrarModalLabel">Encerrar Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="encerrarForm" action="encerrar_agendamento_modal.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="idSolicitacao" id="modalEncerrarSolicitacaoId">
                    <div class="form-group">
                        <label for="data_encerramento">Data de Encerramento:</label>
                        <input type="date" class="form-control" name="data_encerramento" required>
                    </div>
                    <div class="form-group">
                        <label>Justificativa:</label><br>
                        <input type="radio" name="justificativas[]" value="Paciente solicitou o Cancelamento"> Paciente solicitou o Cancelamento<br>
                        <input type="radio" id="outro_checkbox"> Outro (especifique abaixo)<br>
                        <textarea class="form-control" name="outra_justificativa" id="outra_justificativa" disabled style="margin-top: 10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Encerrar Agendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>


<script>
    $(function () {
        $('.select2').select2();

        // Expandable table rows
        $('tbody').on('click', 'tr[data-widget="expandable-table"]', function(e) {
            if (!$(e.target).closest('.btn').length) {
                $(this).next('.expandable-body').toggleClass('d-none');
            }
        });

        // Prevent form submission on enter key press
        $('#filtroForm input').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $('#filtroForm').submit();
            }
        });

        // Show/hide quantity of days input based on return type
        $('#tipoRetorno').on('change', function() {
            if ($(this).val() == 'qtd_dias') {
                $('#qtdDiasGroup').show();
                $('#outrosGroup').hide();
            } else if ($(this).val() == 'outros') {
                $('#qtdDiasGroup').hide();
                $('#outrosGroup').show();
            } else {
                $('#qtdDiasGroup').hide();
                $('#outrosGroup').hide();
            }
        });

        // Populate modal with data for reagendar
        $('#reagendarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var idSolicitacao = button.data('id');
            var numeroProtocolo = button.data('protocolo');
            var modal = $(this);
            modal.find('#modalSolicitacaoId').val(idSolicitacao);
            modal.find('#modalNumeroProtocolo').val(numeroProtocolo);
        });

        // Populate modal with data for encerrar
        $('#encerrarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var idSolicitacao = button.data('id');
            var modal = $(this);
            modal.find('#modalEncerrarSolicitacaoId').val(idSolicitacao);
        });

        // Enable/disable other justification textarea
        $('#outro_checkbox').change(function() {
            $('#outra_justificativa').prop('disabled', !this.checked);
        });
    });
</script>
