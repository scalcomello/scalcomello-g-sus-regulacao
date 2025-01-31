<?php

require '../../includes/dbconnect.php'; // Conexão com o banco de dados

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function preencher_template($html, $dados) {
    foreach ($dados as $chave => $valor) {
        $html = str_replace('${' . $chave . '}', $valor, $html);
    }
    return $html;
}

$idAgendamento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_SESSION['id_usuario'])) {
    die("Usuário não está logado.");
}

$usuario_id = $_SESSION['id_usuario'];
$queryUsuario = $conn->prepare("SELECT nome FROM usuario WHERE id_usuario = ?");
if ($queryUsuario === false) {
    die("Erro na preparação da consulta para obter o nome do usuário: " . $conn->error);
}
$queryUsuario->bind_param("i", $usuario_id);
$queryUsuario->execute();
$resultUsuario = $queryUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
$nomeAtendente = $usuario['nome'];

$queryDetalhes = $conn->prepare("SELECT s.*, m.nome as nomeMedico, c.no_cidadao as nomePaciente,
    c.nu_cpf AS CPF, c.nu_cns AS CNS, c.no_mae AS NomeMae, c.dt_nascimento AS DataNascimento, 
    TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, c.no_sexo as sexo,
    s.numero_protocolo, s.data_recebido_secretaria, s.data_solicitacao, s.classificacao,
    s.retorno, c.nu_telefone_celular AS TelefoneCelular, c.nu_telefone_contato AS TelefoneContato
FROM solicitacao s 
LEFT JOIN medico m ON s.idMedico = m.idMedico 
LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
WHERE s.idSolicitacao = ?");

if ($queryDetalhes === false) {
    die("Erro na preparação da consulta para obter os detalhes do agendamento: " . $conn->error);
}

$queryDetalhes->bind_param("i", $idAgendamento);
$queryDetalhes->execute();
$resultDetalhes = $queryDetalhes->get_result();
$agendamento = $resultDetalhes->fetch_assoc();

if (!$agendamento) {
    echo "<script>alert('Agendamento não encontrado.'); window.location='nova_solicitacao.php';</script>";
    exit;
}

$telefoneCelular = isset($agendamento['TelefoneCelular']) ? htmlspecialchars($agendamento['TelefoneCelular'], ENT_QUOTES, 'UTF-8') : 'N/A';
$telefoneContato = isset($agendamento['TelefoneContato']) ? htmlspecialchars($agendamento['TelefoneContato'], ENT_QUOTES, 'UTF-8') : 'N/A';

if ($agendamento['retorno'] == 1) {
    // Caso seja um reagendamento, buscar apenas pelo id_procedimento
    $queryProcedimentos = $conn->prepare("SELECT p.procedimento, s.tipo_procedimento
        FROM solicitacao s
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE s.idSolicitacao = ?");

    if ($queryProcedimentos === false) {
        die("Erro na preparação da consulta para obter os procedimentos: " . $conn->error);
    }

    $queryProcedimentos->bind_param("i", $idAgendamento);
} else {
    // Caso contrário, buscar todos os procedimentos associados ao numero_protocolo e data_recebido_secretaria
    $dataRecebidoSecretaria = $agendamento['data_recebido_secretaria'];

    $queryProcedimentos = $conn->prepare("SELECT p.procedimento, s.tipo_procedimento
        FROM solicitacao s
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE s.numero_protocolo = ? 
        AND s.data_recebido_secretaria = ?");

    if ($queryProcedimentos === false) {
        die("Erro na preparação da consulta para obter os procedimentos: " . $conn->error);
    }

    $queryProcedimentos->bind_param("ss", $agendamento['numero_protocolo'], $dataRecebidoSecretaria);
}

$queryProcedimentos->execute();
$resultProcedimentos = $queryProcedimentos->get_result();

$procedimentos = [];
$tiposUltrassom = [];
$tiposDoppler = [];

while ($row = $resultProcedimentos->fetch_assoc()) {
    if ($row['procedimento'] === 'Ultrassonografia' && !empty($row['tipo_procedimento'])) {
        $tiposUltrassom[] = $row['tipo_procedimento'];
    } elseif ($row['procedimento'] === 'Doppler' && !empty($row['tipo_procedimento'])) {
        $tiposDoppler[] = $row['tipo_procedimento'];
    } else {
        $procedimentos[] = $row['procedimento'];
    }
}

$procedimentosString = implode(", ", $procedimentos);
$tiposUltrassomString = implode(", ", $tiposUltrassom);
$tiposDopplerString = implode(", ", $tiposDoppler);

if (!empty($tiposUltrassom)) {
    $procedimentosString .= (empty($procedimentosString) ? '' : ', ') . "Ultrassonografia";
}

if (!empty($tiposDoppler)) {
    $procedimentosString .= (empty($procedimentosString) ? '' : ', ') . "Doppler";
}

$dados = [
    'Protocolo' => htmlspecialchars($agendamento['numero_protocolo'], ENT_QUOTES, 'UTF-8'),
    'NomePaciente' => htmlspecialchars($agendamento['nomePaciente'], ENT_QUOTES, 'UTF-8'),
    'Idade' => htmlspecialchars($agendamento['idade'], ENT_QUOTES, 'UTF-8'),
    'Sexo' => htmlspecialchars($agendamento['sexo'], ENT_QUOTES, 'UTF-8'),
    'CNS' => htmlspecialchars($agendamento['CNS'], ENT_QUOTES, 'UTF-8'),
    'CPF' => htmlspecialchars($agendamento['CPF'], ENT_QUOTES, 'UTF-8'),
    'NomeMae' => htmlspecialchars($agendamento['NomeMae'], ENT_QUOTES, 'UTF-8'),
    'DataNascimento' => htmlspecialchars(date('d/m/Y', strtotime($agendamento['DataNascimento'])), ENT_QUOTES, 'UTF-8'),
    'Telefone' => $telefoneCelular,
    'TelefoneContato' => $telefoneContato,
    'NomeMedico' => htmlspecialchars($agendamento['nomeMedico'], ENT_QUOTES, 'UTF-8'),
    'DataPedidoMedico' => htmlspecialchars(date('d/m/Y', strtotime($agendamento['data_solicitacao'])), ENT_QUOTES, 'UTF-8'),
    'ClassificacaoPrioridade' => htmlspecialchars($agendamento['classificacao'], ENT_QUOTES, 'UTF-8'),
    'DataSolicitacao' => htmlspecialchars(date('d/m/Y H:i:s', strtotime($agendamento['data_recebido_secretaria'])), ENT_QUOTES, 'UTF-8'),
    'TipoServicoConsulta' => htmlspecialchars($procedimentosString . (empty($tiposUltrassomString) ? '' : ' - ' . $tiposUltrassomString) . (empty($tiposDopplerString) ? '' : ' - ' . $tiposDopplerString), ENT_QUOTES, 'UTF-8'),
    'NomeAtendente' => htmlspecialchars($nomeAtendente, ENT_QUOTES, 'UTF-8')
];

$templatePath = __DIR__ . '/protocolo_agendamneto.html';
if (!file_exists($templatePath)) {
    die("O arquivo de template não foi encontrado.");
}
$html = file_get_contents($templatePath);

$htmlPreenchido = preencher_template($html, $dados);

$htmlPreenchido .= '
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Comprovante de Solicitação de Agendamento</title>
</head>
<body>
    $htmlPreenchido
</body>
</html>";
?>
