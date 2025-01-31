<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

$idAgendamento = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = $conn->prepare("SELECT s.*, m.nome AS nomeMedico, c.no_cidadao AS nomePaciente,
                         TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, c.no_sexo AS sexo,
                         p.procedimento AS tipoServicoConsulta, pr.unidade_prestadora AS unidadePrestadora
                         FROM solicitacao s 
                         LEFT JOIN medico m ON s.idMedico = m.idMedico 
                         LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                         LEFT JOIN procedimento p ON s.procedimento_id = p.idProcedimento
                         LEFT JOIN prestadores pr ON s.idPrestador = pr.id_prestador
                         WHERE s.idSolicitacao = ?");
$query->bind_param("i", $idAgendamento);
$query->execute();
$result = $query->get_result();
$agendamento = $result->fetch_assoc();

if (!$agendamento) {
    echo "<script>alert('Agendamento não encontrado.'); window.location='nova_solicitacao.php';</script>";
    exit;
}
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalhes do Agendamento</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Início</a></li>
                            <li class="breadcrumb-item"><a href="#">Exibir Solicitacão</a></li>
                        </ol>

                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header" style="background-color: #343a40; color: white;">
                            <h3 class="card-title">Detalhes do Agendamento</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Paciente:</strong> <?= htmlspecialchars($agendamento['nomePaciente']) ?></p>
                            <p><strong>Idade:</strong> <?= htmlspecialchars($agendamento['idade']) ?></p>
                            <p><strong>Sexo:</strong> <?= htmlspecialchars($agendamento['sexo']) ?></p>
                            <p><strong>Médico:</strong> <?= htmlspecialchars($agendamento['nomeMedico']) ?></p>
                            <p><strong>Data do Pedido Médico:</strong> <?= isset($agendamento['data_solicitacao']) ? htmlspecialchars(date('d/m/Y', strtotime($agendamento['data_solicitacao']))) : 'Não especificada' ?></p>
                            <p><strong>Data de Solicitação na Secretaria de Saúde :</strong> <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($agendamento['data_recebido_secretaria']))) ?></p>
                            <p><strong>Classificação:</strong> <?= htmlspecialchars($agendamento['classificacao']) ?></p>
                            <p><strong>Solicitação de Agendamento para:</strong> <?= htmlspecialchars($agendamento['tipoServicoConsulta']) ?></p>
                            <p><strong>Unidade Prestadora:</strong> <?= htmlspecialchars($agendamento['unidadePrestadora']) ?></p>
                            <p><strong>Data Agendamento:</strong> <?= isset($agendamento['data_agendamento_clinica']) ? htmlspecialchars(date('d/m/Y', strtotime($agendamento['data_agendamento_clinica']))) : 'Não especificada' ?></p>

                            <p><strong>Hora Agendamento:</strong> <?= htmlspecialchars($agendamento['hora_agendamento']) ?></p>

                            <!-- Adicione aqui mais detalhes do agendamento conforme necessário -->
                        </div>
                    </div>
                </div>
                <div class="card-footer">


                    <a href="encerrar_agendamento.php?id=<?= $agendamento['idSolicitacao'] ?>" class="btn btn-danger">Finalizar Agendamento</a>
                    <a href="listar_agendados.php" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

        </div>

    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>
