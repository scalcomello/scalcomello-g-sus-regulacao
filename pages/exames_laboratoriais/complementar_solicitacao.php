<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


// Definir o fuso horário para Brasília (São Paulo)
date_default_timezone_set('America/Sao_Paulo');

// Capturar o ID da solicitação a partir do parâmetro GET
$idSolicitacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar os dados do formulário
    $idSolicitacao = $_POST['idSolicitacao'];
    $examesSelecionados = $_POST['exames'] ?? [];
    $unidadePrestadora = $_POST['unidade_prestadora'];

    // Obter o número do protocolo existente na tabela solicitacao
    $sqlProtocolo = "SELECT numero_protocolo FROM solicitacao WHERE idSolicitacao = ?";
    $stmtProtocolo = $conn->prepare($sqlProtocolo);
    if ($stmtProtocolo === false) {
        die("Erro na preparação da consulta SQL: " . $conn->error);
    }
    $stmtProtocolo->bind_param("i", $idSolicitacao);
    $stmtProtocolo->execute();
    $stmtProtocolo->bind_result($numeroProtocolo);
    $stmtProtocolo->fetch();
    $stmtProtocolo->close();

    // Calcular o total dos valores dos exames selecionados
    $totalValor = 0;
    foreach ($examesSelecionados as $exameId) {
        $sqlValor = "SELECT valor_unitario FROM exames_laboratoriais WHERE id = ?";
        $stmtValor = $conn->prepare($sqlValor);
        if ($stmtValor === false) {
            die("Erro na preparação da consulta SQL: " . $conn->error);
        }
        $stmtValor->bind_param("i", $exameId);
        $stmtValor->execute();
        $stmtValor->bind_result($valorUnitario);
        $stmtValor->fetch();
        $totalValor += $valorUnitario;
        $stmtValor->close();
    }

    // Deletar os exames selecionados da tabela exames_laboratoriais_solicitacao
    $sqlDelete = "DELETE FROM exames_laboratoriais_solicitacao WHERE solicitacao_id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    if ($stmtDelete === false) {
        die("Erro na preparação da consulta SQL: " . $conn->error);
    }
    $stmtDelete->bind_param("i", $idSolicitacao);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Inserir os exames selecionados na tabela exames_laboratoriais_solicitacao
    $sqlInsert = "INSERT INTO exames_laboratoriais_solicitacao (solicitacao_id, exame_id, data_horario, numero_protocolo) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    if ($stmtInsert === false) {
        die("Erro na preparação da consulta SQL: " . $conn->error);
    }
    $dataHorario = date('Y-m-d H:i:s');
    foreach ($examesSelecionados as $exameId) {
        $stmtInsert->bind_param("iiss", $idSolicitacao, $exameId, $dataHorario, $numeroProtocolo);
        $stmtInsert->execute();
    }
    $stmtInsert->close();

    // Atualizar os dados na tabela solicitacao, incluindo data_agendamento_clinica e hora_agendamento
    $sqlUpdate = "UPDATE solicitacao SET total_valor = ?, idPrestador = ?, status_procedimento = 'Agendado', data_agendamento_clinica = ?, hora_agendamento = ? WHERE idSolicitacao = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    if ($stmtUpdate === false) {
        die("Erro na preparação da consulta SQL: " . $conn->error);
    }
    $dataAtual = date('Y-m-d'); // Capturar a data atual
    $horaAtual = date('H:i:s'); // Capturar a hora atual

    // Adicionar mensagens de depuração
    echo "Data Atual: " . $dataAtual . "<br>";
    echo "Hora Atual: " . $horaAtual . "<br>";

    $stmtUpdate->bind_param("diiss", $totalValor, $unidadePrestadora, $dataAtual, $horaAtual, $idSolicitacao);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Redirecionar para a página de visualização
    header("Location: visualizar_solicitacao.php?id=$idSolicitacao");
    exit;
}

// Buscar os dados da solicitação
$sqlSolicitacao = "SELECT s.idSolicitacao, s.total_valor, s.idPrestador, c.no_cidadao AS nome_cidadao, c.nu_cpf AS cpf, p.procedimento
                   FROM solicitacao s
                   JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                   JOIN procedimento p ON s.procedimento_id = p.idProcedimento
                   WHERE s.idSolicitacao = ?";
$stmtSolicitacao = $conn->prepare($sqlSolicitacao);
if ($stmtSolicitacao === false) {
    die("Erro na preparação da consulta SQL: " . $conn->error);
}
$stmtSolicitacao->bind_param("i", $idSolicitacao);
$stmtSolicitacao->execute();
$resultSolicitacao = $stmtSolicitacao->get_result();
$solicitacao = $resultSolicitacao->fetch_assoc();
$stmtSolicitacao->close();

// Buscar os exames laboratoriais disponíveis
$sqlExames = "SELECT * FROM exames_laboratoriais ORDER BY descricao";
$resultExames = $conn->query($sqlExames);
if ($resultExames === false) {
    die("Erro na consulta SQL: " . $conn->error);
}

// Buscar os exames selecionados para a solicitação atual
$sqlExamesSelecionados = "SELECT exame_id FROM exames_laboratoriais_solicitacao WHERE solicitacao_id = ?";
$stmtExamesSelecionados = $conn->prepare($sqlExamesSelecionados);
if ($stmtExamesSelecionados === false) {
    die("Erro na preparação da consulta SQL: " . $conn->error);
}
$stmtExamesSelecionados->bind_param("i", $idSolicitacao);
$stmtExamesSelecionados->execute();
$resultExamesSelecionados = $stmtExamesSelecionados->get_result();
$examesSelecionadosArray = [];
while ($row = $resultExamesSelecionados->fetch_assoc()) {
    $examesSelecionadosArray[] = $row['exame_id'];
}
$stmtExamesSelecionados->close();

// Buscar as unidades prestadoras disponíveis que são do tipo LABORATORIO E ANALISES CLINICAS
$sqlPrestadores = "SELECT id_prestador, unidade_prestadora 
                   FROM prestadores 
                   WHERE tipo = 'LABORATORIO E ANALISES CLINICAS' 
                   ORDER BY unidade_prestadora";
$resultPrestadores = $conn->query($sqlPrestadores);
if ($resultPrestadores === false) {
    die("Erro na consulta SQL: " . $conn->error);
}


$resultPrestadores = $conn->query($sqlPrestadores);
if ($resultPrestadores === false) {
    die("Erro na consulta SQL: " . $conn->error);
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
                    <h3 class="mb-0">Consultar Solicitações</h3>
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
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header" style="background-color: #343a40; color: white;">
                            <h3 class="card-title">Dados da Solicitação</h3>
                        </div>
                        <div class="card-body">
                            <form action="complementar_solicitacao.php" method="post">
                                <input type="hidden" name="idSolicitacao" value="<?= $idSolicitacao ?>">
                                <div class="form-group">
                                    <label for="nome_cidadao">Nome do Paciente:</label>
                                    <input type="text" class="form-control" id="nome_cidadao" value="<?= htmlspecialchars($solicitacao['nome_cidadao']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="cpf">CPF:</label>
                                    <input type="text" class="form-control" id="cpf" value="<?= htmlspecialchars($solicitacao['cpf']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="procedimento">Procedimento:</label>
                                    <input type="text" class="form-control" id="procedimento" value="<?= htmlspecialchars($solicitacao['procedimento']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="unidade_prestadora">Unidade Prestadora:</label>
                                    <select id="unidade_prestadora" name="unidade_prestadora" class="form-control">
                                        <?php while ($prestador = $resultPrestadores->fetch_assoc()): ?>
                                            <option value="<?= $prestador['id_prestador'] ?>" <?= $prestador['id_prestador'] == $solicitacao['idPrestador'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($prestador['unidade_prestadora']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exames">Exames Laboratoriais:</label>
                                    <select name="exames[]" id="exames" class="duallistbox" multiple="multiple">
                                        <?php while ($exame = $resultExames->fetch_assoc()): ?>
                                            <option value="<?= $exame['id'] ?>" data-valor="<?= $exame['valor_unitario'] ?>" <?= in_array($exame['id'], $examesSelecionadosArray) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($exame['descricao']) ?> - R$ <?= number_format($exame['valor_unitario'], 2, ',', '.') ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div id="total">Total: R$ <?= number_format($solicitacao['total_valor'], 2, ',', '.') ?></div>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                                <a href="exames_laboratoriais.php" class="btn btn-secondary">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->
<!-- Incluir os arquivos necessários para o Bootstrap Duallistbox -->
<link rel="stylesheet" href="../../plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
<script src="../../plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>

<script>
    $(function () {
        $('.duallistbox').bootstrapDualListbox({
            filterPlaceHolder: 'Filtrar',
            infoText: 'Mostrando todos os {0}',
            infoTextEmpty: 'Lista vazia',
            infoTextFiltered: '<span class="label label-warning">Filtrado</span> {0} de {1}',
            selectorMinimalHeight: 160,
            nonSelectedListLabel: 'Disponíveis',
            selectedListLabel: 'Selecionados',
            moveOnSelect: false,  // Impede que o item seja movido imediatamente após ser selecionado
        });

        // Forçar reindexação após filtragem
        $('.duallistbox').on('bootstrapDualListbox.refresh', function () {
            let box = $(this).bootstrapDualListbox('getContainer');
            box.find('option').each(function (index, option) {
                $(option).attr('data-original-index', index);
            });
        });

        // Atualizar total ao selecionar exames
        $('#exames').change(function () {
            let total = 0;
            $('#exames option:selected').each(function () {
                total += parseFloat($(this).data('valor'));
            });
            $('#total').text('Total: R$ ' + total.toFixed(2).replace('.', ','));

            // Depuração: Mostrar os IDs dos exames selecionados
            let selectedIds = [];
            $('#exames option:selected').each(function () {
                selectedIds.push($(this).val());
            });
            console.log('Exames Selecionados IDs:', selectedIds);
        });
    });

</script>
