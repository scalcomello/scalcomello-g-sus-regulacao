<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


if (isset($_GET['id'])) {
    $idSolicitacao = intval($_GET['id']);

    // Buscar a solicitação original
    $sql = "SELECT * FROM solicitacao WHERE idSolicitacao = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idSolicitacao);
    $stmt->execute();
    $result = $stmt->get_result();
    $solicitacao = $result->fetch_assoc();

    if ($solicitacao) {
        // Atualizar status_procedimento da solicitação original para "Finalizado"
        $sqlUpdate = "UPDATE solicitacao SET status_procedimento = 'Finalizado' WHERE idSolicitacao = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('i', $idSolicitacao);
        $stmtUpdate->execute();

        // Inserir nova solicitação com os dados copiados e as mudanças necessárias
        $sqlInsert = "INSERT INTO solicitacao (
            cidadao_id, idMedico, procedimento_id, data_solicitacao, 
            data_recebido_secretaria, classificacao, status_procedimento, 
            tipo_procedimento, status_reagendamento, numero_protocolo
        ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 1, ?)";

        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param('iiisssss',
            $solicitacao['cidadao_id'],
            $solicitacao['idMedico'],
            $solicitacao['procedimento_id'],
            $solicitacao['data_solicitacao'],
            $solicitacao['classificacao'],
            $solicitacao['status_procedimento'],
            $solicitacao['tipo_procedimento'],
            $solicitacao['numero_protocolo']
        );
        $stmtInsert->execute();

        // Verificar se a nova solicitação foi criada com sucesso
        if ($stmtInsert->affected_rows > 0) {
            // Redirecionar com mensagem de sucesso
            header("Location: listar_agendamento.php?mensagem=reagendamento_sucesso");
        } else {
            // Redirecionar com mensagem de erro
            header("Location: listar_agendamento.php?mensagem=reagendamento_erro");
        }
    } else {
        // Redirecionar com mensagem de erro se a solicitação original não for encontrada
        header("Location: listar_agendamento.php?mensagem=solicitacao_nao_encontrada");
    }
} else {
    // Redirecionar com mensagem de erro se o ID da solicitação não for fornecido
    header("Location: listar_agendamento.php?mensagem=id_invalido");
}
?>
