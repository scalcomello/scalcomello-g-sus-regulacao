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

$idSolicitacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_SESSION['id_usuario'])) {
    die("Usuário não está logado.");
}

$usuario_id = $_SESSION['id_usuario'];
$queryUsuario = $conn->prepare("SELECT nome, Contato, Estabelecimento, CNES FROM usuario WHERE id_usuario = ?");
if ($queryUsuario === false) {
    die("Erro na preparação da consulta para obter os dados do usuário: " . $conn->error);
}
$queryUsuario->bind_param("i", $usuario_id);
$queryUsuario->execute();
$resultUsuario = $queryUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
if ($usuario) {
    $dadosAutorizador = [
        'nomeusuario' => htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'),
        'Contatousuario' => htmlspecialchars($usuario['Contato'], ENT_QUOTES, 'UTF-8'),
        'estabelecimentousuario' => htmlspecialchars($usuario['Estabelecimento'], ENT_QUOTES, 'UTF-8'),
        'cnesusuario' => htmlspecialchars($usuario['CNES'], ENT_QUOTES, 'UTF-8')
    ];
} else {
    die("Dados do usuário não encontrados.");
}

$queryDetalhes = $conn->prepare("SELECT s.*, 
    c.no_cidadao AS nomePaciente, 
    c.nu_cpf AS CPF, 
    c.nu_cns AS CNS,
    c.no_mae AS NomeMae, 
    c.dt_nascimento AS DataNascimento, 
    TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade,
    c.no_sexo AS sexo, 
    c.nu_telefone_celular AS Telefone, 
    CONCAT(c.ds_logradouro, ', ', c.nu_numero, ' - ', c.no_bairro) AS Endereco, 
    u.nome AS NomeMedico, 
    u.Estabelecimento AS Organizacao,
    u.CNES,
    u.Contato AS ContatoAutorizador,
    s.data_solicitacao AS DataPedidoMedico,
    s.classificacao AS ClassificacaoPrioridade,
    s.data_recebido_secretaria AS DataSolicitacao,
    s.idPrestador
FROM solicitacao s
LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
LEFT JOIN usuario u ON s.idMedico = u.id_usuario
WHERE s.idSolicitacao = ?");

if ($queryDetalhes === false) {
    die("Erro na preparação da consulta para obter os detalhes da solicitação: " . $conn->error);
}

$queryDetalhes->bind_param("i", $idSolicitacao);
$queryDetalhes->execute();
$resultDetalhes = $queryDetalhes->get_result();
$solicitacao = $resultDetalhes->fetch_assoc();

if (!$solicitacao) {
    echo "<script>alert('Solicitação não encontrada.'); window.location='nova_solicitacao.php';</script>";
    exit;
}

// Verifique se idPrestador está definido e não é nulo
if (!isset($solicitacao['idPrestador']) || is_null($solicitacao['idPrestador'])) {
    die("ID do prestador não encontrado na solicitação.");
}

$prestador_id = $solicitacao['idPrestador'];

// Obter dados do prestador
$queryPrestador = $conn->prepare("SELECT unidade_prestadora, cnpj, CONCAT(endereco, ' - ', bairro) AS enderecoprestador FROM prestadores WHERE id_prestador = ?");
if ($queryPrestador === false) {
    die("Erro na preparação da consulta para obter os dados do prestador: " . $conn->error);
}
$queryPrestador->bind_param("i", $prestador_id);
$queryPrestador->execute();
$resultPrestador = $queryPrestador->get_result();
$prestador = $resultPrestador->fetch_assoc();
if ($prestador) {
    $dadosPrestador = [
        'nomeprestador' => htmlspecialchars($prestador['unidade_prestadora'], ENT_QUOTES, 'UTF-8'),
        'cnpjprestador' => htmlspecialchars($prestador['cnpj'], ENT_QUOTES, 'UTF-8'),
        'enderecoprestador' => htmlspecialchars($prestador['enderecoprestador'], ENT_QUOTES, 'UTF-8')
    ];
} else {
    die("Dados do prestador não encontrados.");
}

// Obter data_horario dos exames laboratoriais solicitados
$queryDataHorario = $conn->prepare("SELECT data_horario FROM exames_laboratoriais_solicitacao WHERE solicitacao_id = ? LIMIT 1");
if ($queryDataHorario === false) {
    die("Erro na preparação da consulta para obter a data de horário: " . $conn->error);
}
$queryDataHorario->bind_param("i", $idSolicitacao);
$queryDataHorario->execute();
$resultDataHorario = $queryDataHorario->get_result();
$dataHorarioRow = $resultDataHorario->fetch_assoc();
$dataHorario = $dataHorarioRow ? date('d/m/Y', strtotime($dataHorarioRow['data_horario'])) : date('d/m/Y');

$queryExames = $conn->prepare("SELECT el.descricao, el.valor_unitario 
                               FROM exames_laboratoriais el
                               JOIN exames_laboratoriais_solicitacao els ON el.id = els.exame_id
                               WHERE els.solicitacao_id = ?");
if ($queryExames === false) {
    die("Erro na preparação da consulta para obter os exames: " . $conn->error);
}

$queryExames->bind_param("i", $idSolicitacao);
$queryExames->execute();
$resultExames = $queryExames->get_result();
$exames = [];
while ($row = $resultExames->fetch_assoc()) {
    $exames[] = $row;
}

$examesString = '';
$totalValor = 0.0;
foreach ($exames as $index => $exame) {
    $examesString .= ($index + 1) . " - " . htmlspecialchars($exame['descricao'], ENT_QUOTES, 'UTF-8')  . "<br>";
    $totalValor += $exame['valor_unitario'];
}

// Dados a serem preenchidos no template
$dados = [
    'Protocolo' => htmlspecialchars($solicitacao['numero_protocolo'], ENT_QUOTES, 'UTF-8'),
    'NomePaciente' => htmlspecialchars($solicitacao['nomePaciente'], ENT_QUOTES, 'UTF-8'),
    'CPF' => htmlspecialchars($solicitacao['CPF'], ENT_QUOTES, 'UTF-8'),
    'CNS' => htmlspecialchars($solicitacao['CNS'], ENT_QUOTES, 'UTF-8'),
    'DataNascimento' => htmlspecialchars(date('d/m/Y', strtotime($solicitacao['DataNascimento'])), ENT_QUOTES, 'UTF-8'),
    'NomeMae' => htmlspecialchars($solicitacao['NomeMae'], ENT_QUOTES, 'UTF-8'),
    'Telefone' => htmlspecialchars($solicitacao['Telefone'], ENT_QUOTES, 'UTF-8'),
    'Endereco' => htmlspecialchars($solicitacao['Endereco'], ENT_QUOTES, 'UTF-8'),
    'Exames' => $examesString,
    'NomeAtendente' => htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'),
    'data' => $dataHorario
];

// Mescla os dados do autorizador e do prestador com os dados da solicitação
$dados = array_merge($dadosAutorizador, $dados, $dadosPrestador);

$templatePath = __DIR__ . '/guia_exame_sangue.html';
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
    <title>Guia de Exame de Sangue</title>
</head>
<body>
    '.$htmlPreenchido.'
</body>
</html>";
?>
