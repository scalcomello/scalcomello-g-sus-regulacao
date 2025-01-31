<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados
// Consulta para buscar agendamentos diários
$sql = "SELECT COUNT(*) AS total_agendamentos, data_agendamento_clinica 
        FROM solicitacao 
        WHERE status_procedimento = 'agendado'
        AND hora_agendamento IS NOT NULL
        AND idPrestador IS NOT NULL
        GROUP BY data_agendamento_clinica";

$result = $conn->query($sql);

// Array para armazenar os agendamentos no formato do FullCalendar
$eventos = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $eventos[] = [
            'title' => 'Agendados: ' . $row['total_agendamentos'],
            'start' => $row['data_agendamento_clinica'],
            'backgroundColor' => '#00a65a',
            'borderColor' => '#00a65a',
            'extendedProps' => [
                'data' => $row['data_agendamento_clinica']
            ]
        ];
    }
}

// Consulta para buscar os pacientes de transporte diários
$sqlTransporte = "SELECT COUNT(*) AS total_transporte, data_transporte 
                  FROM transporte 
                  GROUP BY data_transporte";

$resultTransporte = $conn->query($sqlTransporte);

if ($resultTransporte->num_rows > 0) {
    while($row = $resultTransporte->fetch_assoc()) {
        $eventos[] = [
            'title' => 'Transporte: ' . $row['total_transporte'],
            'start' => $row['data_transporte'],
            'backgroundColor' => '#add8e6',
            'borderColor' => '#add8e6',
            'extendedProps' => [
                'data' => $row['data_transporte']
            ]
        ];
    }
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
                    <h3 class="mb-0">erenciamento de Pacientes Agendados</h3>
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
    <!-- Conteúdo Principal -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-body p-0">
                            <!-- Botão para abrir o modal -->
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#transporteModal">
                                Cadastrar Paciente para Transporte
                            </button>

                            <!-- Calendário -->
                            <div id="calendar" style="padding: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-body p-0">


                            <!-- Calendário -->
                            <div id="calendar" style="padding: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>


    <!-- Modal para Cadastrar Paciente no Transporte -->
    <div class="modal fade" id="transporteModal" tabindex="-1" role="dialog" aria-labelledby="transporteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transporteModalLabel">Cadastrar Paciente para Transporte</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Etapa 1: Selecionar Paciente -->
                    <div id="step1">
                        <form id="step1Form">
                            <div class="form-group">
                                <label for="paciente-search">Buscar Paciente (CNS ou Nome):</label>
                                <input type="text" class="form-control" id="paciente-search" placeholder="Digite o CNS ou Nome do Paciente" required>
                                <ul id="search-results" class="list-group mt-2"></ul> <!-- Resultados de busca -->
                            </div>

                            <!-- Campo oculto para armazenar o id_cidadao -->
                            <input type="hidden" id="cidadao_id" name="cidadao_id">

                            <div class="form-group">
                                <label for="telefone">Telefone:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="telefone" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" id="editarTelefoneBtn" type="button">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="nextStep" disabled>Avançar</button>
                        </form>
                    </div>

                    <!-- Etapa 2: Informações do Transporte -->
                    <div id="step2" style="display: none;">
                        <form id="step2Form">
                            <div class="form-group">
                                <label for="hora-transporte">Hora de Saída:</label>
                                <input type="time" class="form-control" id="hora-transporte" required>
                            </div>
                            <div class="form-group">
                                <label for="local-transporte">Local de Destino:</label>
                                <input type="text" class="form-control" id="local-transporte" required>
                            </div>
                            <div class="form-group">
                                <label for="data-transporte">Data do Transporte:</label>
                                <input type="date" class="form-control" id="data-transporte" required>
                            </div>
                            <button type="button" class="btn btn-secondary" id="previousStep">Voltar</button>
                            <button type="button" class="btn btn-primary" id="saveTransporteBtn">Salvar</button>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>


<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>

    <!-- FullCalendar CSS e JS via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>

    <!-- Script para manipulação do modal e calendário -->
    <script>
        $(document).ready(function() {
            // Inicializa o FullCalendar com visualização somente de mês e em português
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br', // Define o idioma para português do Brasil
                initialView: 'dayGridMonth', // Define a visualização inicial como mês
                headerToolbar: {
                    left: 'prev,next today', // Navegação (anterior, próximo e "Hoje")
                    center: 'title',         // Exibe o título (Mês e Ano)
                    right: ''                // Remove as opções de semana e dia
                },
                events: <?php echo json_encode($eventos); ?>,
                eventClick: function(info) {
                    var dataAgendamento = info.event.extendedProps.data; // Data do evento clicado
                    window.location.href = 'detalhes_agendamentos.php?data=' + dataAgendamento;
                }
            });

            // Renderiza o calendário
            calendar.render();

            // Busca dinâmica de paciente
            $('#paciente-search').on('input', function() {
                var query = $(this).val();
                $('#search-results').empty();

                if (query.length > 2) { // Inicia a busca a partir de 3 caracteres
                    $.ajax({
                        url: 'buscar_paciente.php',
                        method: 'GET',
                        data: { query: query },
                        success: function(response) {
                            var results = JSON.parse(response);
                            $('#search-results').empty(); // Limpa os resultados anteriores

                            if (results.length > 0) {
                                results.forEach(function(citizen) {
                                    $('#search-results').append(
                                        `<li class="list-group-item list-group-item-action" data-id="${citizen.id}" data-nome="${citizen.name}" data-cpf="${citizen.cpf}" data-nascimento="${citizen.birthdate}" data-telefone="${citizen.telefone}">
                                    <strong>${citizen.name}</strong> - CPF: ${citizen.cpf} - Nascimento: ${citizen.birthdate}
                                </li>`
                                    );
                                });
                            } else {
                                $('#search-results').append('<li class="list-group-item">Nenhum paciente encontrado</li>');
                            }
                        },
                        error: function() {
                            alert('Erro ao buscar pacientes.');
                        }
                    });
                }
            });

            // Seleção de um paciente
            $('#search-results').on('click', 'li', function() {
                var nome = $(this).data('nome');
                var cpf = $(this).data('cpf');
                var nascimento = $(this).data('nascimento');
                var telefone = $(this).data('telefone');

                // Preenche as informações do paciente
                $('#cidadao_id').val($(this).data('id'));
                $('#telefone').val(telefone);
                $('#nextStep').prop('disabled', false);

                // Exibe as informações
                $('#paciente-search').val(nome);
                $('#search-results').empty();
            });

            // Permitir edição do telefone
            $('#editarTelefoneBtn').on('click', function() {
                $('#telefone').prop('readonly', false); // Permite editar o telefone
            });

            // Avançar para a próxima etapa
            $('#nextStep').on('click', function() {
                $('#step1').hide();
                $('#step2').show();
            });

            // Voltar para a etapa anterior
            $('#previousStep').on('click', function() {
                $('#step2').hide();
                $('#step1').show();
            });

            // Salvamento do formulário
            $('#saveTransporteBtn').on('click', function() {
                var formData = {
                    cidadao_id: $('#cidadao_id').val(),
                    telefone: $('#telefone').val(),
                    hora: $('#hora-transporte').val(),
                    local: $('#local-transporte').val(),
                    data: $('#data-transporte').val(),
                };

                $.ajax({
                    url: 'salvar_transporte.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            location.reload(); // Recarrega a página após o salvamento
                        } else {
                            alert('Erro: ' + res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao salvar o paciente para transporte.');
                    }
                });
            });
        });
    </script>