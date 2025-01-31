<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

session_start();

// Definir o fuso horário para o horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Função para sanitizar entradas
function sanitize_input($input) {
    return htmlspecialchars(strip_tags($input));
}

// Verificar se a sessão já está iniciada
if (!isset($_SESSION['id_usuario'])) {
    die("Erro: Sessão não iniciada. Por favor, faça login novamente.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idSolicitacao = intval($_POST['idSolicitacao']);
    $numeroProtocolo = sanitize_input($_POST['numeroProtocolo']);
    $tipoRetorno = sanitize_input($_POST['tipoRetorno']);
    $qtdDias = isset($_POST['qtdDias']) ? intval($_POST['qtdDias']) : null;
    $outrosText = sanitize_input($_POST['outrosText']);
    $classificacao = sanitize_input($_POST['classificacao']);
    $dataHoraAtual = date('Y-m-d H:i:s');

    // Pegar os dados da solicitação original
    $sql = "SELECT * FROM solicitacao WHERE idSolicitacao = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $idSolicitacao);
    $stmt->execute();
    $solicitacaoOriginal = $stmt->get_result()->fetch_assoc();

    if (!$solicitacaoOriginal) {
        die("Erro: Solicitação original não encontrada.");
    }

    $cidadao_id = $solicitacaoOriginal['cidadao_id'];
    $idMedico = $solicitacaoOriginal['idMedico'];
    $procedimento_id = $solicitacaoOriginal['procedimento_id'];
    $data_solicitacao = $solicitacaoOriginal['data_solicitacao'];
    $tipo_procedimento = $solicitacaoOriginal['tipo_procedimento'];
    $regulacao = ($classificacao == 'Urgente' || $classificacao == 'Judicial') ? 1 : 0;

    $conn->begin_transaction();

    try {
        // Criar a nova solicitação com os dados da original
        $sql = "INSERT INTO solicitacao (cidadao_id, idMedico, procedimento_id, data_solicitacao, data_recebido_secretaria, classificacao, regulacao, status_procedimento, numero_protocolo, tipo_procedimento, retorno) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Erro na preparação da inserção: ' . $conn->error);
        }

        $status_procedimento = 'Aguardando';
        $retorno = 1;
        $stmt->bind_param("iiisssisssi", $cidadao_id, $idMedico, $procedimento_id, $data_solicitacao, $dataHoraAtual, $classificacao, $regulacao, $status_procedimento, $numeroProtocolo, $tipo_procedimento, $retorno);
        $stmt->execute();

        // Registrar auditoria
        $usuario_id = $_SESSION['id_usuario'];
        $nova_solicitacao_id = $stmt->insert_id;
        $acao = 'Reagendamento';
        $descricao = 'Solicitação reagendada com o protocolo ' . $numeroProtocolo;
        $audit_sql = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao, data) VALUES (?, ?, ?, ?, ?)";
        $stmt_auditoria = $conn->prepare($audit_sql);
        if (!$stmt_auditoria) {
            throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
        }
        $stmt_auditoria->bind_param("iisss", $usuario_id, $nova_solicitacao_id, $acao, $descricao, $dataHoraAtual);
        $stmt_auditoria->execute();

        $conn->commit();
        header("Location: exibir_solicitacao.php?mensagem=sucesso");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao reagendar: " . $e->getMessage();
    }
}
?>