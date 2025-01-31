<?php
require '../../includes/dbconnect.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Definir quais passos estão ativos
$step1_active = true;
$step2_active = true;
$step3_active = true; // Etapa final ativa

// 1. Verificar o ID do agendamento para buscar os detalhes
$idAgendamento = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Consulta para buscar os detalhes do agendamento
$queryDetalhes = $conn->prepare("
    SELECT s.*, m.nome as nomeMedico, c.no_cidadao as nomePaciente, c.id_cidadao,
        TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, c.no_sexo as sexo,
        s.numero_protocolo, s.data_recebido_secretaria, s.data_solicitacao, s.classificacao
    FROM solicitacao s 
    LEFT JOIN medico m ON s.idMedico = m.idMedico 
    LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
    WHERE s.idSolicitacao = ?
");
$queryDetalhes->bind_param("i", $idAgendamento);
$queryDetalhes->execute();
$resultDetalhes = $queryDetalhes->get_result();
$agendamento = $resultDetalhes->fetch_assoc();

if (!$agendamento) {
    echo "<script>alert('Agendamento não encontrado.'); window.location='nova_solicitacao.php';</script>";
    exit;
}

// 4. Buscar procedimentos associados ao número de protocolo
$queryProcedimentos = $conn->prepare("
    SELECT p.procedimento, s.tipo_procedimento
    FROM solicitacao s
    JOIN procedimento p ON s.procedimento_id = p.idProcedimento
    WHERE s.numero_protocolo = ?
");
$queryProcedimentos->bind_param("s", $agendamento['numero_protocolo']);
$queryProcedimentos->execute();
$resultProcedimentos = $queryProcedimentos->get_result();

// 5. Organizar os procedimentos e tipos de ultrassom e doppler
$procedimentos = [];
$tiposUltrassom = [];
$tiposDoppler = [];
while ($row = $resultProcedimentos->fetch_assoc()) {
    if ($row['procedimento'] === 'Ultrassonografia' && !empty($row['tipo_procedimento'])) {
        $tiposUltrassum[] = $row['tipo_procedimento'];
    } elseif ($row['procedimento'] === 'Doppler' && !empty($row['tipo_procedimento'])) {
        $tiposDoppler[] = $row['tipo_procedimento'];
        // Inclui "Doppler" na lista de procedimentos
        if (!in_array('Doppler', $procedimentos)) {
            $procedimentos[] = 'Doppler';
        }
    } else {
        $procedimentos[] = $row['procedimento'];
    }
}

// Montando a string de procedimentos
$procedimentosString = implode(", ", $procedimentos);
if (!empty($tiposUltrassum)) {
    $procedimentosString .= (empty($procedimentosString) ? '' : ', ') . "Ultrassonografia";
}

$tiposDopplerString = implode(", ", $tiposDoppler);
?>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-calendar-check"></i> Solicitação Finalizada</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Finalizar Solicitação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Agendamento -->
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title"><i class="fas fa-info-circle"></i> Detalhes do Agendamento</h5>
                </div>
                <div class="card-body">
                    <!-- Informações do Paciente e Médico -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <p><i class="fas fa-user"></i> <strong>Paciente:</strong> <?= htmlspecialchars($agendamento['nomePaciente']) ?></p>
                                <p><i class="fas fa-birthday-cake"></i> <strong>Idade:</strong> <?= htmlspecialchars($agendamento['idade']) ?></p>
                                <p><i class="fas fa-venus-mars"></i> <strong>Sexo:</strong> <?= htmlspecialchars($agendamento['sexo']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <p><i class="fas fa-user-md"></i> <strong>Médico:</strong> <?= htmlspecialchars($agendamento['nomeMedico']) ?></p>
                                <p><i class="fas fa-calendar-alt"></i> <strong>Data do Pedido Médico:</strong> <?= date('d/m/Y', strtotime($agendamento['data_solicitacao'])) ?></p>
                                <p><i class="fas fa-flag"></i> <strong>Classificação:</strong> <?= htmlspecialchars($agendamento['classificacao']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Agendamento -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <p><i class="fas fa-clock"></i> <strong>Data de Recebimento na Secretaria:</strong> <?= date('d/m/Y H:i:s', strtotime($agendamento['data_recebido_secretaria'])) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <p><i class="fas fa-procedures"></i> <strong>Procedimentos:</strong> <?= htmlspecialchars($procedimentosString) ?></p>
                                <?php if (!empty($tiposDopplerString)): ?>
                                    <p><i class="fas fa-stethoscope"></i> <strong>Tipos de Doppler:</strong> <?= htmlspecialchars($tiposDopplerString) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($tiposUltrassum)): ?>
                                    <p><i class="fas fa-stethoscope"></i> <strong>Tipos de Ultrassonografia:</strong> <?= htmlspecialchars(implode(", ", $tiposUltrassum)) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="text-center">
                        <a href="script_protocolo.php?id=<?= $idAgendamento; ?>" class="btn btn-primary btn-lg me-2" target="_blank">
                            <i class="fas fa-print"></i> Imprimir
                        </a>
                        <a href="nova_solicitacao.php" class="btn btn-secondary btn-lg me-2">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <a href="inserir_solicitacao.php?id=<?= $agendamento['id_cidadao']; ?>" class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Nova Solicitação
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../../includes/footer.php'; ?>
