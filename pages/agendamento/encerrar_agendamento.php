<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

session_start();

$idSolicitacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dataEncerramento = $_POST['data_encerramento'];
    $justificativas = isset($_POST['justificativas']) ? $_POST['justificativas'] : [];
    $outraJustificativa = $_POST['outra_justificativa'];
    $status_procedimento = (!empty($justificativas) || !empty($outraJustificativa)) ? 'Finalizado' : 'Atendido';
    $usuarioId = $_SESSION['id_usuario']; // Recuperar o ID do usuário logado da sessão

    // Obter a data e hora atual no fuso horário de São Paulo
    $dataHoraAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dataHoraAtualFormatada = $dataHoraAtual->format('Y-m-d H:i:s'); // Formato para inserção no banco de dados

    $justificativaFinal = implode(", ", $justificativas);
    if (!empty($outraJustificativa)) {
        $justificativaFinal .= "; " . $outraJustificativa;
    }

    $conn->begin_transaction();

    try {
        // Atualizar o registro no banco de dados
        $sql = "UPDATE solicitacao SET data_encerramento = ?, justificativa_encerramento = ?, status_procedimento = ? WHERE idSolicitacao = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Erro na preparação da atualização: ' . $conn->error);
        }
        $stmt->bind_param("sssi", $dataEncerramento, $justificativaFinal, $status_procedimento, $idSolicitacao);
        $stmt->execute();

        // Inserir na tabela de auditoria
        $acao_auditoria = "Encerramento da Solicitação";
        $descricao = "Ação: " . $acao_auditoria . ". Justificativa: " . $justificativaFinal;

        $sql_auditoria = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao, data) VALUES (?, ?, ?, ?, ?)";
        $stmt_auditoria = $conn->prepare($sql_auditoria);
        if (!$stmt_auditoria) {
            throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
        }
        $stmt_auditoria->bind_param("iisss", $usuarioId, $idSolicitacao, $acao_auditoria, $descricao, $dataHoraAtualFormatada);
        $stmt_auditoria->execute();

        $conn->commit();
        header("Location: listar_agendados.php?mensagem=sucesso");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao encerrar agendamento: " . $e->getMessage();
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
                        <div class="card-header">
                            <h3 class="card-title">Dados do Encerramento</h3>
                        </div>
                        <form method="POST" class="form-horizontal">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="data_encerramento">Data e Hora de Encerramento:</label>
                                    <input type="datetime-local" class="form-control" name="data_encerramento" required>
                                </div>
                                <div class="form-group">
                                    <label>Justificativa:</label><br>
                                    <input type="radio" name="justificativas[]" value="Paciente não compareceu ao exame"> Paciente não compareceu ao exame<br>
                                    <input type="radio" name="justificativas[]" value="Paciente realizou o exame particularmente em outra instituição"> Paciente realizou o exame particular em outra instituição<br>
                                    <input type="radio" name="justificativas[]" value="Paciente solicitou reagendamento do exame devido a conflitos de agenda"> Paciente solicitou reagendamento do exame devido a conflitos de agenda<br>
                                    <input type="radio" name="justificativas[]" value="Paciente impossibilitado de comparecer devido a condições de saúde"> Paciente impossibilitado de comparecer devido a condições de saúde<br>
                                    <input type="radio" name="justificativas[]" value="Paciente não compareceu devido a problemas familiares"> Paciente não compareceu devido a problemas familiares<br>
                                    <input type="radio" name="justificativas[]" value="Não conseguiu contato com o paciente"> Não conseguiu contato com o paciente<br>
                                    <input type="radio" name="justificativas[]" id="outro_checkbox" value="Outro"> Outro (especifique abaixo)<br>
                                    <textarea class="form-control" name="outra_justificativa" id="outra_justificativa" disabled style="margin-top: 10px;"></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Encerrar Agendamento</button>
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

<script>
    $(document).ready(function() {
        $('input[type="radio"]').click(function() {
            var wasChecked = $(this).data('waschecked');
            $('input[type="radio"]').prop('checked', false).data('waschecked', false);
            if (!wasChecked) {
                $(this).prop('checked', true).data('waschecked', true);
            }
        });

        $('#outro_checkbox').change(function() {
            $('#outra_justificativa').prop('disabled', !this.checked);
        });
    });
</script>