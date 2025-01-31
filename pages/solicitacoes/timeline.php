<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


// Verificar se o ID da solicitação foi passado via GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Recuperar dados da solicitação
    $sql = "SELECT s.*, c.no_cidadao, c.nu_cpf 
            FROM solicitacao s 
            JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao 
            WHERE s.idSolicitacao = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $solicitacao = $result->fetch_assoc();

    if (!$solicitacao) {
        die("Solicitação não encontrada.");
    }

    $numeroProtocolo = $solicitacao['numero_protocolo'];

    // Recuperar registros de auditoria para todas as solicitações com o mesmo protocolo
    $sqlAuditoria = "SELECT a.*, u.nome AS usuario_nome 
                     FROM auditoria_solicitacao a
                     JOIN usuario u ON a.usuario_id = u.id_usuario
                     JOIN solicitacao s ON a.solicitacao_id = s.idSolicitacao
                     WHERE s.numero_protocolo = ?
                     ORDER BY a.data DESC";
    $stmtAuditoria = $conn->prepare($sqlAuditoria);
    $stmtAuditoria->bind_param("s", $numeroProtocolo);
    $stmtAuditoria->execute();
    $resultAuditoria = $stmtAuditoria->get_result();

    $stmt->close();
    $stmtAuditoria->close();
} else {
    die("ID de solicitação não fornecido.");
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detalhes da Solicitação</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Protocolo:</strong> <?= htmlspecialchars($solicitacao['numero_protocolo']) ?></p>
                            <p><strong>Nome do Cidadão:</strong> <?= htmlspecialchars($solicitacao['no_cidadao']) ?></p>
                            <p><strong>CPF:</strong> <?= htmlspecialchars($solicitacao['nu_cpf']) ?></p>
                        </div>
                    </div>
                    <!-- Aqui começa a timeline -->
                    <div class="timeline">
                        <?php
                        $currentDate = '';
                        while ($auditoria = $resultAuditoria->fetch_assoc()):
                            $auditoriaDate = htmlspecialchars(date('d-m-Y', strtotime($auditoria['data'])));
                            if ($auditoriaDate != $currentDate):
                                $currentDate = $auditoriaDate;
                                ?>
                                <div class="time-label">
                                    <span class="bg-red"><?= $currentDate ?></span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <i class="fas fa-user bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> <?= htmlspecialchars(date('H:i', strtotime($auditoria['data']))) ?></span>
                                    <h3 class="timeline-header"><?= htmlspecialchars($auditoria['usuario_nome']) ?> realizou a ação: <?= htmlspecialchars($auditoria['acao']) ?></h3>
                                    <div class="timeline-body">
                                        <?= nl2br(htmlspecialchars($auditoria['descricao'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
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
