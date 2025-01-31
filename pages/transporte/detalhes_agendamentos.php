<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// Pega a data passada pela URL
$data = isset($_GET['data']) ? $_GET['data'] : '';

// Inicializa as variáveis como arrays vazios
$agendamentos = [];
$transportes = [];

// Query de agendamentos
$sqlAgendamentos = "SELECT s.idSolicitacao, c.no_cidadao AS paciente, c.nu_cns AS cns, s.hora_agendamento AS hora, p.unidade_prestadora AS local, 
                    c.nu_telefone_celular AS telefone, ac.no_cidadao AS acompanhante_nome, ac.nu_cns AS acompanhante_cns, s.status_transporte
                    FROM solicitacao s
                    JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                    JOIN prestadores p ON s.idPrestador = p.id_prestador
                    LEFT JOIN tb_cidadao ac ON s.id_acompanhante = ac.id_cidadao
                    WHERE s.data_agendamento_clinica = ?
                    AND s.status_procedimento = 'agendado'
                    ORDER BY s.hora_agendamento ASC";

if ($stmtAgendamentos = $conn->prepare($sqlAgendamentos)) {
    $stmtAgendamentos->bind_param("s", $data);
    $stmtAgendamentos->execute();
    $resultAgendamentos = $stmtAgendamentos->get_result();

    while ($row = $resultAgendamentos->fetch_assoc()) {
        $agendamentos[] = $row;
    }
    $stmtAgendamentos->close();
}

// Query de transportes
$sqlTransportes = "SELECT t.id_transporte, c.no_cidadao AS paciente, c.nu_cns AS cns, t.hora_transporte AS hora, t.local_transporte AS local, 
                    c.nu_telefone_celular AS telefone, t.status_transporte
                    FROM transporte t
                    JOIN tb_cidadao c ON t.cidadao_id = c.id_cidadao
                    WHERE t.data_transporte = ?
                    ORDER BY t.hora_transporte ASC";

if ($stmtTransportes = $conn->prepare($sqlTransportes)) {
    $stmtTransportes->bind_param("s", $data);
    $stmtTransportes->execute();
    $resultTransportes = $stmtTransportes->get_result();

    while ($row = $resultTransportes->fetch_assoc()) {
        $transportes[] = $row;
    }
    $stmtTransportes->close();
}


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
                    <h3 class="mb-0">Gerenciamento de Agendamentos para <?php echo date('d/m/Y', strtotime($data)); ?></h3>

                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Início</a></li>
                            <li class="breadcrumb-item"><a href="#">Exibir Solicitacão</a></li>
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
                <!-- Agendamentos -->
                <?php foreach ($agendamentos as $agendamento): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <?php echo $agendamento['paciente']; ?>
                                    <span class="badge badge-secondary"><?php echo $agendamento['hora']; ?></span>
                                </h5>
                                <p class="card-text">CNS: <?php echo $agendamento['cns']; ?></p>
                                <p class="card-text">Local: <?php echo $agendamento['local']; ?></p>
                                <p class="card-text">Telefone: <?php echo $agendamento['telefone']; ?></p>
                                <p class="card-text">
                                    Acompanhante:
                                    <?php if (empty($agendamento['acompanhante_nome'])): ?>
                                        <button class="btn btn-sm btn-success adicionar-acompanhante" data-toggle="modal" data-target="#buscarAcompanhanteModal" data-id="<?php echo $agendamento['idSolicitacao']; ?>" data-type="agendamento">
                                            <i class="fas fa-user-plus"></i> Adicionar
                                        </button>
                                    <?php else: ?>
                                        <?php echo $agendamento['acompanhante_nome']; ?>
                                        <button class="btn btn-sm btn-warning editar-acompanhante" data-toggle="modal" data-target="#buscarAcompanhanteModal" data-id="<?php echo $agendamento['idSolicitacao']; ?>" data-type="agendamento">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-danger remover-acompanhante" data-id="<?php echo $agendamento['idSolicitacao']; ?>">
                                            <i class="fas fa-trash"></i> Remover
                                        </button>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text">
                                    <span class="badge <?php echo ($agendamento['status_transporte'] == 'habilitado') ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($agendamento['status_transporte']); ?>
                                    </span>
                                    <?php if ($agendamento['status_transporte'] == 'habilitado'): ?>
                                        <button class="btn btn-sm btn-warning desmarcar-transporte" data-id="<?php echo $agendamento['idSolicitacao']; ?>" data-type="agendamento">
                                            <i class="fas fa-ban"></i> Desmarcar Transporte
                                        </button>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Transportes -->
            <div class="row">
                <?php foreach ($transportes as $transporte): ?>
                    <div class="col-md-6">
                        <div class="card bg-light shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-info"><?php echo $transporte['paciente']; ?> <span class="badge badge-secondary"><?php echo $transporte['hora']; ?></span></h5>
                                <p class="card-text">CNS: <?php echo $transporte['cns']; ?></p>
                                <p class="card-text">Local: <?php echo $transporte['local']; ?></p>
                                <p class="card-text">Telefone: <?php echo $transporte['telefone']; ?></p>
                                <p class="card-text">
                                    <span class="badge <?php echo ($transporte['status_transporte'] == 'habilitado') ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($transporte['status_transporte']); ?>
                                    </span>
                                    <?php if ($transporte['status_transporte'] == 'habilitado'): ?>
                                        <button class="btn btn-sm btn-warning desmarcar-transporte" data-id="<?php echo $transporte['id_transporte']; ?>" data-type="transporte">
                                            <i class="fas fa-ban"></i> Desmarcar Transporte
                                        </button>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>

<!-- Modal de Busca de Acompanhante -->
<div class="modal fade" id="buscarAcompanhanteModal" tabindex="-1" role="dialog" aria-labelledby="buscarAcompanhanteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarAcompanhanteLabel">Buscar Acompanhante</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscaAcompanhante" class="form-control" placeholder="Digite o nome ou CNS do acompanhante">
                <ul id="resultadoBuscaAcompanhante" class="list-group mt-3"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="salvarAcompanhanteBtn">Salvar Acompanhante</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        // Busca dinâmica de acompanhantes ao digitar no campo
        $('#buscaAcompanhante').on('input', function() {
            var query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: 'buscar_paciente.php',
                    method: 'GET',
                    data: { query: query },
                    success: function(response) {
                        var results = JSON.parse(response);
                        $('#resultadoBuscaAcompanhante').empty();

                        if (results.length > 0) {
                            results.forEach(function(patient) {
                                $('#resultadoBuscaAcompanhante').append(
                                    `<li class="list-group-item list-group-item-action" data-id="${patient.id}">
                                    <strong>${patient.name}</strong> - CNS: ${patient.cpf} - Nasc: ${patient.birthdate}
                                </li>`
                                );
                            });
                        } else {
                            $('#resultadoBuscaAcompanhante').append('<li class="list-group-item">Nenhum acompanhante encontrado</li>');
                        }
                    },
                    error: function() {
                        alert('Erro ao buscar acompanhante.');
                    }
                });
            } else {
                $('#resultadoBuscaAcompanhante').empty();
            }
        });

        // Ação ao selecionar um acompanhante da lista
        $('#resultadoBuscaAcompanhante').on('click', 'li', function() {
            var pacienteId = $(this).data('id');
            $('#buscaAcompanhante').val($(this).text());
            $('#buscaAcompanhante').data('paciente-id', pacienteId);
            $('#resultadoBuscaAcompanhante').empty();
        });

        // Salvando o acompanhante selecionado
        $('#salvarAcompanhanteBtn').on('click', function() {
            var solicitacaoId = $(this).data('solicitacao-id');
            var pacienteId = $('#buscaAcompanhante').data('paciente-id');
            var tipoEntidade = $(this).data('type');

            if (!pacienteId) {
                alert('Selecione um acompanhante.');
                return;
            }

            // Envia os dados para salvar o acompanhante na solicitação ou transporte
            $.ajax({
                url: 'salvar_acompanhante.php',
                method: 'POST',
                data: {
                    solicitacaoId: solicitacaoId,
                    pacienteId: pacienteId,
                    tipoEntidade: tipoEntidade
                },
                success: function(response) {
                    alert('Acompanhante salvo com sucesso!');
                    $('#buscarAcompanhanteModal').modal('hide');
                    location.reload();
                },
                error: function() {
                    alert('Erro ao salvar acompanhante.');
                }
            });
        });

        // Remover acompanhante
        $('.remover-acompanhante').on('click', function() {
            var solicitacaoId = $(this).data('id');

            if (confirm('Tem certeza que deseja remover o acompanhante?')) {
                $.ajax({
                    url: 'remover_acompanhante.php',
                    method: 'POST',
                    data: { solicitacaoId: solicitacaoId },
                    success: function(response) {
                        alert('Acompanhante removido com sucesso!');
                        location.reload();
                    },
                    error: function() {
                        alert('Erro ao remover acompanhante.');
                    }
                });
            }
        });
    });
</script>