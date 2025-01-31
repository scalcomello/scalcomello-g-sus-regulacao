<?php

require 'dbconnect.php';

// Recebe os dados do POST
$cidadao_id = isset($_POST['cidadao_id']) ? $_POST['cidadao_id'] : '';
$telefone = isset($_POST['telefone']) ? $_POST['telefone'] : '';
$hora = isset($_POST['hora']) ? $_POST['hora'] : '';
$local = isset($_POST['local']) ? $_POST['local'] : '';
$data = isset($_POST['data']) ? $_POST['data'] : '';

// Verifica se todos os campos foram preenchidos
if (empty($cidadao_id) || empty($telefone) || empty($hora) || empty($local) || empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}


// Atualiza o telefone do cidadão, se necessário
$updateTelefoneSQL = "UPDATE tb_cidadao SET nu_telefone_celular = ? WHERE id_cidadao = ?";
$updateTelefoneStmt = $conn->prepare($updateTelefoneSQL);

if (!$updateTelefoneStmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a consulta de atualização do telefone: ' . $conn->error]);
    exit;
}

$updateTelefoneStmt->bind_param("si", $telefone, $cidadao_id);

if (!$updateTelefoneStmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o telefone: ' . $updateTelefoneStmt->error]);
    exit;
}

$updateTelefoneStmt->close();

// Insere os dados do transporte no banco de dados
$insertTransporteSQL = "INSERT INTO transporte (cidadao_id, hora_transporte, local_transporte, data_transporte) 
                        VALUES (?, ?, ?, ?)";
$insertTransporteStmt = $conn->prepare($insertTransporteSQL);

if (!$insertTransporteStmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a consulta de inserção de transporte: ' . $conn->error]);
    exit;
}

$insertTransporteStmt->bind_param("isss", $cidadao_id, $hora, $local, $data);

if ($insertTransporteStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Paciente cadastrado para transporte com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o paciente para transporte: ' . $insertTransporteStmt->error]);
}

$insertTransporteStmt->close();
$conn->close();
?>
