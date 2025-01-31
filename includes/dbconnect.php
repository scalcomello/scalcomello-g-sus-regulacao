<?php

// Ativar exibição de erros para depuração (remover ou comentar após desenvolvimento)
error_reporting(E_ALL); // remover depois de pronto
ini_set('display_errors', '1'); // remover depois de pronto

$servername = "localhost"; // geralmente é "localhost"
$username = "root"; // seu nome de usuário do banco de dados
$password = ""; // sua senha do banco de dados
$dbname = "sushub"; // nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error); // Mostra erro de conexão e termina o script
}

// Opcional: Definir o charset como UTF-8 para garantir que não haja problemas com acentuação
// Pode ser removido se não houver necessidade de lidar com caracteres especiais
$conn->set_charset("utf8");

?>
