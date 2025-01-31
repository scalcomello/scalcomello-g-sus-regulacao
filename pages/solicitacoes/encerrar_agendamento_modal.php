<?php
require '../../includes/dbconnect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idSolicitacao = intval($_POST['idSolicitacao']);
    $dataEncerramento = $_POST['data_encerramento'];
    $justificativas = isset($_POST['justificativas']) ? $_POST['justificativas'] : [];
    $outraJustificativa = $_POST['outra_justificativa'];
    $status_procedimento = (!empty($justificativas) || !empty($outraJustificativa)) ? 'Finalizado' : 'Atendido';
    $usuarioId = $_SESSION['id_usuario'];
    $dataHoraAtual = date('Y-m-d H:i:s');

    $justificativaFinal = implode(", ", $justificativas);
    if (!empty($outraJustificativa)) {
        $justificativaFinal .= "; " . $outraJustificativa;
    }

    $conn->begin_transaction();

    try {
        $sql = "UPDATE solicitacao SET data_encerramento = ?, justificativa_encerramento = ?, status_procedimento = ? WHERE idSolicitacao = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $dataEncerramento, $justificativaFinal, $status_procedimento, $idSolicitacao);
        $stmt->execute();

        $acao_auditoria = "Encerramento da Solicitação";
        $descricao = "Ação: " . $acao_auditoria . ". Justificativa: " . $justificativaFinal;

        $sql_auditoria = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao, data) VALUES (?, ?, ?, ?, ?)";
        $stmt_auditoria = $conn->prepare($sql_auditoria);
        $stmt_auditoria->bind_param("iisss", $usuarioId, $idSolicitacao, $acao_auditoria, $descricao, $dataHoraAtual);
        $stmt_auditoria->execute();

        $conn->commit();
        header("Location: exibir_solicitacao.php?mensagem=sucesso");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao encerrar agendamento: " . $e->getMessage();
    }
}
?>
