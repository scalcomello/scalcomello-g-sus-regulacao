<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

$query = isset($_GET['query']) ? $_GET['query'] : '';

if (!empty($query)) {
    $sql = "SELECT id_cidadao, no_cidadao, nu_cpf, dt_nascimento, nu_telefone_celular
            FROM tb_cidadao 
            WHERE no_cidadao LIKE ? OR nu_cns LIKE ?
            LIMIT 10";  // Limitar a 10 resultados

    $stmt = $conn->prepare($sql);
    $likeQuery = '%' . $query . '%';
    $stmt->bind_param("ss", $likeQuery, $likeQuery);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $response = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response[] = [
                    'id' => $row['id_cidadao'],
                    'name' => $row['no_cidadao'],
                    'cpf' => $row['nu_cpf'],
                    'birthdate' => date('d/m/Y', strtotime($row['dt_nascimento'])),
                    'telefone' => $row['nu_telefone_celular']
                ];
            }
        } else {
            $response = [];  // Nenhum paciente encontrado
        }

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Erro ao executar a consulta']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Parâmetro de busca não fornecido']);
}

$conn->close();
?>
