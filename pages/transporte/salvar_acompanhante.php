<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

$entidadeId = $_POST['entidadeId']; // Pode ser ID de solicitacao ou transporte
$tipoEntidade = $_POST['tipoEntidade']; // Pode ser 'agendamento' ou 'transporte'
$acompanhanteId = $_POST['acompanhanteId'];

// Define a query com base no tipo da entidade
if ($tipoEntidade === 'agendamento') {
    $sql = "UPDATE solicitacao SET id_acompanhante = ? WHERE idSolicitacao = ?";
} elseif ($tipoEntidade === 'transporte') {
    $sql = "UPDATE transporte SET id_acompanhante = ? WHERE id_transporte = ?";
} else {
    echo json_encode(['success' => false, 'message' => 'Tipo de entidade inválido.']);
    exit;
}

// Executa a atualização
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $acompanhanteId, $entidadeId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Acompanhante adicionado com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar acompanhante.']);
}

$stmt->close();
$conn->close();
?>
