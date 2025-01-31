<?php
session_start();
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $data_inicial = $_POST['data_inicial'];
    $data_final = $_POST['data_final'];
    $descricao = $_POST['descricao'];

    $sql = "INSERT INTO competencias (nome, data_inicial, data_final, descricao) VALUES ('$nome', '$data_inicial', '$data_final', '$descricao')";

    if ($conn->query($sql) === TRUE) {
        header('Location: exames_laboratoriais.php?mensagem=sucesso');
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }
}
?>
