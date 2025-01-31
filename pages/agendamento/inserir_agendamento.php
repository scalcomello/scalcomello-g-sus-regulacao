<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados
session_start();

$idSolicitacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar unidades prestadoras
$prestadoresQuery = "SELECT id_prestador, unidade_prestadora FROM prestadores";
$prestadoresResult = $conn->query($prestadoresQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dataAgendamento = $_POST['data_agendamento'];
    $horaAgendamento = $_POST['hora_agendamento'];
    $idPrestador = $_POST['id_prestador']; // ID do prestador
    $usuarioId = $_SESSION['id_usuario']; // ID do usuário logado

    // Obter a data e hora atual no fuso horário de São Paulo
    $dataHoraAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dataHoraAtualFormatada = $dataHoraAtual->format('Y-m-d H:i:s'); // Formato para inserção no banco de dados

    // Obter o nome do prestador
    $prestadorNomeQuery = "SELECT unidade_prestadora FROM prestadores WHERE id_prestador = ?";
    $stmtPrestador = $conn->prepare($prestadorNomeQuery);
    $stmtPrestador->bind_param("i", $idPrestador);
    $stmtPrestador->execute();
    $resultPrestador = $stmtPrestador->get_result();
    $prestador = $resultPrestador->fetch_assoc();
    $nomePrestador = $prestador['unidade_prestadora'];

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Atualizar o registro no banco de dados
        $sql = "UPDATE solicitacao SET data_agendamento_clinica = ?, hora_agendamento = ?, idPrestador = ?, status_procedimento = 'Agendado' WHERE idSolicitacao = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Erro na preparação da consulta: ' . $conn->error);
        }

        $stmt->bind_param("ssii", $dataAgendamento, $horaAgendamento, $idPrestador, $idSolicitacao);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao inserir agendamento: ' . $conn->error);
        }

        // Inserir na tabela de auditoria
        $acao_auditoria = "Agendamento";
        $descricao = "Agendamento realizado para a data " . $dataAgendamento . " às " . $horaAgendamento . " com o prestador " . $nomePrestador;

        $sql_auditoria = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao, data) VALUES (?, ?, ?, ?, ?)";
        $stmt_auditoria = $conn->prepare($sql_auditoria);

        if (!$stmt_auditoria) {
            throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
        }

        $stmt_auditoria->bind_param("iisss", $usuarioId, $idSolicitacao, $acao_auditoria, $descricao, $dataHoraAtualFormatada);

        if (!$stmt_auditoria->execute()) {
            throw new Exception('Erro ao inserir na auditoria: ' . $conn->error);
        }

        // Commit da transação
        $conn->commit();

        header("Location: listar_agendamento.php?mensagem=sucesso"); // Redirecionamento para listar_agendamento.php
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao inserir agendamento: " . $e->getMessage();
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
                    <h3 class="mb-0">Inserir Agendamento</h3>
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
                            <h3 class="card-title">Dados do Agendamento</h3>
                        </div>
                        <form method="POST" class="form-horizontal">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="data_agendamento">Data de Agendamento:</label>
                                    <input type="date" class="form-control" name="data_agendamento" required>
                                </div>
                                <div class="form-group">
                                    <label for="hora_agendamento">Hora de Agendamento:</label>
                                    <input type="time" class="form-control" name="hora_agendamento" required>
                                </div>
                                <div class="form-group">
                                    <label for="id_prestador">Prestador:</label>
                                    <select class="form-control" name="id_prestador" required>
                                        <option value="">Selecione um Prestador</option>
                                        <?php while ($prestador = $prestadoresResult->fetch_assoc()): ?>
                                            <option value="<?= $prestador['id_prestador'] ?>"><?= htmlspecialchars($prestador['unidade_prestadora']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Salvar Agendamento</button>
                                <a href="listar_agendamento.php" title="Voltar" class="btn btn-secondary">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>
