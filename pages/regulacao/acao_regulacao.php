<?php
require '../../includes/dbconnect.php';
session_start(); // Garantir que a sessão esteja iniciada

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit;
}

// Função para registrar auditoria
function registrarAuditoria($conn, $usuarioId, $idSolicitacao, $acao, $descricao, $dataHora) {
    $sql_auditoria = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao, data) VALUES (?, ?, ?, ?, ?)";
    $stmt_auditoria = $conn->prepare($sql_auditoria);
    if (!$stmt_auditoria) {
        throw new Exception('Erro na preparação da auditoria: ' . $conn->error);
    }
    $stmt_auditoria->bind_param("iisss", $usuarioId, $idSolicitacao, $acao, $descricao, $dataHora);
    if (!$stmt_auditoria->execute()) {
        throw new Exception('Erro ao executar a auditoria: ' . $stmt_auditoria->error);
    }
}

try {
    // Verificar se o ID da solicitação foi enviado via GET ou POST
    $idSolicitacao = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if ($idSolicitacao <= 0) {
        throw new Exception('ID da solicitação não fornecido ou inválido.');
    }

    // Determinar a ação: 'aprovar' ou 'reprovar'
    $acao = isset($_REQUEST['acao']) ? $_REQUEST['acao'] : '';

    // Dados comuns
    $dataHoraAtual = date('Y-m-d H:i:s'); // Data e hora atual
    $usuarioId = $_SESSION['id_usuario']; // ID do usuário logado

    // Iniciar a transação
    $conn->begin_transaction();

    if ($acao === 'aprovar') {
        // **Ação de Aprovação**
        $sql = "UPDATE solicitacao 
                SET regulacao = 0, status_procedimento = 'Aguardando', data_regulacao = ? 
                WHERE idSolicitacao = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Erro na preparação da query de aprovação: ' . $conn->error);
        }
        $stmt->bind_param("si", $dataHoraAtual, $idSolicitacao);
        if (!$stmt->execute()) {
            throw new Exception('Erro na execução da query de aprovação: ' . $stmt->error);
        }

        // Inserir na tabela de auditoria
        $acao_auditoria = "Aprovação de Regulação";
        $descricao = "Solicitação aprovada e regulada para 'Aguardando'.";

        // Mensagem de sucesso
        $_SESSION['mensagem'] = "Solicitação aprovada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";

    } elseif ($acao === 'reprovar') {
        // **Ação de Reprovação**
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new Exception('Método de requisição inválido para reprovação.');
        }

        $justificativa_regulacao = isset($_POST['justificativa_regulacao']) ? trim($_POST['justificativa_regulacao']) : '';
        $tipo_reprovacao = isset($_POST['tipo_reprovacao']) ? $_POST['tipo_reprovacao'] : '';

        if (empty($justificativa_regulacao)) {
            throw new Exception('A justificativa é obrigatória para reprovação.');
        }

        if ($tipo_reprovacao === 'fila') {
            $status_procedimento = 'Aguardando';
            $classificacao = 'Eletivo';
            $sql = "UPDATE solicitacao 
                    SET regulacao = 0, status_procedimento = ?, classificacao = ?, justificativa_regulacao = ?, data_regulacao = ? 
                    WHERE idSolicitacao = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro na preparação da query de reprovação (fila): ' . $conn->error);
            }
            $stmt->bind_param("ssssi", $status_procedimento, $classificacao, $justificativa_regulacao, $dataHoraAtual, $idSolicitacao);
            if (!$stmt->execute()) {
                throw new Exception('Erro na execução da query de reprovação (fila): ' . $stmt->error);
            }

            // Auditoria
            $acao_auditoria = "Retorno para Fila";
            $descricao = "Ação: Retorno para Fila. Justificativa: " . $justificativa_regulacao;

            // Mensagem de sucesso
            $_SESSION['mensagem'] = "Solicitação enviado para a fila de espera com sucesso!";
            $_SESSION['tipo_mensagem'] = "warning";

        } elseif ($tipo_reprovacao === 'finalizar') {
            $status_procedimento = 'Finalizado';
            $sql = "UPDATE solicitacao 
                    SET regulacao = 0, status_procedimento = ?, justificativa_regulacao = ?, data_regulacao = ?, data_encerramento = ? 
                    WHERE idSolicitacao = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Erro na preparação da query de reprovação (finalizar): ' . $conn->error);
            }
            $stmt->bind_param("ssssi", $status_procedimento, $justificativa_regulacao, $dataHoraAtual, $dataHoraAtual, $idSolicitacao);
            if (!$stmt->execute()) {
                throw new Exception('Erro na execução da query de reprovação (finalizar): ' . $stmt->error);
            }

            // Auditoria
            $acao_auditoria = "Finalização da Solicitação pela Regulação";
            $descricao = "Ação: Finalização da Solicitação pela Regulação. Justificativa: " . $justificativa_regulacao;

            // Mensagem de sucesso
            $_SESSION['mensagem'] = "Solicitação finalizada com sucesso!";
            $_SESSION['tipo_mensagem'] = "danger";
        } else {
            throw new Exception('Tipo de reprovação inválido.');
        }

    } else {
        throw new Exception('Ação inválida.');
    }

    // Inserir na tabela de auditoria
    registrarAuditoria($conn, $usuarioId, $idSolicitacao, $acao_auditoria, $descricao, $dataHoraAtual);

    // Commit da transação
    $conn->commit();

    // Redirecionar para a página principal
    header('Location: regulacao.php');
    exit;

} catch (Exception $e) {
    // Rollback da transação em caso de erro
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    // Definir mensagem de erro
    $_SESSION['mensagem'] = "Erro ao processar a solicitação: " . htmlspecialchars($e->getMessage());
    $_SESSION['tipo_mensagem'] = "danger";

    // Redirecionar para a página principal
    header('Location: regulacao.php');
    exit;
}
?>
