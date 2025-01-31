<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// Função para substituir variáveis no template HTML
function preencher_template($html, $dados) {
    foreach ($dados as $chave => $valor) {
        $html = str_replace('${' . $chave . '}', $valor, $html);
    }
    return $html;
}

$ids = isset($_POST['ids']) ? $_POST['ids'] : [];

if (empty($ids) && isset($_GET['id'])) {
    $ids[] = intval($_GET['id']);
}

if (empty($ids)) {
    die("Nenhum ID fornecido.");
}

$htmlFinal = "";

foreach ($ids as $idAgendamento) {
    // Consulta para buscar os detalhes do agendamento pelo ID
    $queryDetalhes = $conn->prepare("SELECT s.*, m.nome as nomeMedico, c.no_cidadao as nomePaciente, c.nu_cns as cns,
        TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, c.no_sexo as sexo, p.unidade_prestadora, s.tipo_procedimento
        FROM solicitacao s 
        LEFT JOIN medico m ON s.idMedico = m.idMedico 
        LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        LEFT JOIN prestadores p ON s.idPrestador = p.id_prestador
        WHERE s.idSolicitacao = ?");
    
    $queryDetalhes->bind_param("i", $idAgendamento);
    $queryDetalhes->execute();
    $resultDetalhes = $queryDetalhes->get_result();
    $agendamento = $resultDetalhes->fetch_assoc();

    if (!$queryDetalhes) {
        die("Erro na preparação da consulta: " . $conn->error);
    }

    if (!$agendamento) {
        echo "<script>alert('Agendamento não encontrado.'); window.location='listar_agendados.php';</script>";
        exit;
    }

    // Converter a data e hora para o formato desejado com dia da semana
    $diasSemana = [
        'Sunday' => 'domingo',
        'Monday' => 'segunda-feira',
        'Tuesday' => 'terça-feira',
        'Wednesday' => 'quarta-feira',
        'Thursday' => 'quinta-feira',
        'Friday' => 'sexta-feira',
        'Saturday' => 'sábado'
    ];

    $dataAgendamentoClinica = isset($agendamento['data_agendamento_clinica']) ? $agendamento['data_agendamento_clinica'] : null;
    $horaAgendamento = isset($agendamento['hora_agendamento']) ? $agendamento['hora_agendamento'] : null;

    $diaSemana = isset($dataAgendamentoClinica) ? $diasSemana[date('l', strtotime($dataAgendamentoClinica))] : 'Desconhecido';
    $dataHorario = isset($dataAgendamentoClinica) ? date('d/m/Y', strtotime($dataAgendamentoClinica)) . " - " . $diaSemana . " às " . date('H:i:s', strtotime($horaAgendamento)) : 'Desconhecido';

    // Consulta para buscar as orientações associadas ao tipo de procedimento
    $tipoProcedimento = isset($agendamento['tipo_procedimento']) ? $agendamento['tipo_procedimento'] : '';

    $queryOrientacoes = $conn->prepare("SELECT orientacao FROM orientacoes WHERE tipo_procedimento = ?");
    $queryOrientacoes->bind_param("s", $tipoProcedimento);
    $queryOrientacoes->execute();
    $resultOrientacoes = $queryOrientacoes->get_result();
    $orientacao = $resultOrientacoes->fetch_assoc();

    $orientacaoTexto = isset($orientacao['orientacao']) ? $orientacao['orientacao'] : 'Nenhuma orientação disponível';

    $dados = [
        'NomePaciente' => htmlspecialchars($agendamento['nomePaciente'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'),
        'Idade' => htmlspecialchars($agendamento['idade'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'),
        'Sexo' => htmlspecialchars($agendamento['sexo'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'),
        'cns' => htmlspecialchars($agendamento['cns'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'),
        'NomeMedico' => htmlspecialchars($agendamento['nomeMedico'] ?? 'Desconhecido', ENT_QUOTES, 'UTF-8'),
        'DataHorario' => $dataHorario,
        'tipoprocedimento' => htmlspecialchars($tipoProcedimento, ENT_QUOTES, 'UTF-8'),
        'orientacao' => htmlspecialchars($orientacaoTexto, ENT_QUOTES, 'UTF-8')
    ];

    // Carregar o template HTML
    $templatePath = __DIR__ . '/agendamento_ultrassom.html';
    if (!file_exists($templatePath)) {
        die("O arquivo de template não foi encontrado.");
    }
    $html = file_get_contents($templatePath);

    // Substituir variáveis no template
    $htmlPreenchido = preencher_template($html, $dados);

    $htmlFinal .= $htmlPreenchido;
}

// Exibir o HTML final em uma nova janela
echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Guias de Agendamento de Ultrassom</title>
</head>
<body>
    $htmlFinal
</body>
</html>";
?>
