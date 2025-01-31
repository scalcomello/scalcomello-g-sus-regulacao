<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


$idSolicitacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sqlSolicitacao = "SELECT s.idSolicitacao, c.no_cidadao AS nome_cidadao, c.nu_cpf AS cpf, p.procedimento, s.numero_protocolo, s.total_valor
                   FROM solicitacao s
                   JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                   JOIN procedimento p ON s.procedimento_id = p.idProcedimento
                   WHERE s.idSolicitacao = ?";
$stmtSolicitacao = $conn->prepare($sqlSolicitacao);
if ($stmtSolicitacao === false) {
    die("Erro na preparação da consulta SQL: " . $conn->error);
}
$stmtSolicitacao->bind_param("i", $idSolicitacao);
$stmtSolicitacao->execute();
$resultSolicitacao = $stmtSolicitacao->get_result();
$solicitacao = $resultSolicitacao->fetch_assoc();
$stmtSolicitacao->close();

$sqlExames = "SELECT el.descricao, el.valor_unitario 
              FROM exames_laboratoriais el
              JOIN exames_laboratoriais_solicitacao els ON el.id = els.exame_id
              WHERE els.solicitacao_id = ?";
$stmtExames = $conn->prepare($sqlExames);
if ($stmtExames === false) {
    die("Erro na preparação da consulta SQL: " . $conn->error);
}
$stmtExames->bind_param("i", $idSolicitacao);
$stmtExames->execute();
$resultExames = $stmtExames->get_result();
$exames = $resultExames->fetch_all(MYSQLI_ASSOC);
$stmtExames->close();

$totalExames = $solicitacao['total_valor'];


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
                    <h3 class="mb-0">Consultar Solicitações</h3>
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
                            <h3 class="card-title">Dados da Solicitação</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Nome do Paciente:</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($solicitacao['nome_cidadao']) ?></dd>
                                <dt class="col-sm-3">CPF:</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($solicitacao['cpf']) ?></dd>
                                <dt class="col-sm-3">Procedimento:</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($solicitacao['procedimento']) ?></dd>
                                <dt class="col-sm-3">Número do Protocolo:</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($solicitacao['numero_protocolo']) ?></dd>

                            </dl>
                            <h4>Exames Laboratoriais</h4>
                            <ul>
                                <?php foreach ($exames as $exame): ?>
                                    <li><?= htmlspecialchars($exame['descricao']) ?> - R$ <?= number_format($exame['valor_unitario'], 2, ',', '.') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <dt class="col-sm-3">Total dos Exames: R$ <?= number_format($totalExames, 2, ',', '.') ?></dt>

                        </div>
                        <div class="card-footer">
                            <a href="exames_laboratoriais.php" class="btn btn-secondary">Voltar</a>
                            <a target="_blank" href="script_guia_exame_sangue.php?id=<?= $idSolicitacao ?>" class="btn btn-primary">Imprimir</a>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>
