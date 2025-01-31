<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar se o ID da solicitação foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de solicitação inválido.');
}

$idSolicitacao = intval($_GET['id']); // Obter e sanitizar o ID da solicitação

// Preparar a consulta
$query = $conn->prepare("SELECT s.*, m.nome AS nomeMedico, c.no_cidadao AS nomePaciente, 
                         TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, c.no_sexo AS sexo,
                         p.procedimento AS tipoServicoConsulta
                         FROM solicitacao s 
                         LEFT JOIN medico m ON s.idMedico = m.idMedico 
                         LEFT JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                         LEFT JOIN procedimento p ON s.procedimento_id = p.idProcedimento
                         WHERE s.idSolicitacao = ?");

if (!$query) {
    die('Erro na preparação da consulta: ' . $conn->error);
}

$query->bind_param('i', $idSolicitacao);

if (!$query->execute()) {
    die('Erro na execução da consulta: ' . $query->error);
}

$result = $query->get_result();
if ($result->num_rows === 0) {
    die('Solicitação não encontrada.');
}

$solicitacao = $result->fetch_assoc();

// Incluir o cabeçalho e a barra lateral
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Adicionando estilos personalizados -->
<style>
    .compact-table th, .compact-table td {
        padding: 5px; /* Reduzir ainda mais o padding */
        vertical-align: middle; /* Alinha verticalmente no meio */
    }
    .compact-table th {
        width: 15%; /* Define uma largura fixa para a primeira coluna */
        text-align: left; /* Alinha o texto à esquerda */
    }
    .compact-table td {
        text-align: left; /* Alinha o texto à esquerda */
    }
</style>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalhes da Solicitação</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item"><a href="regulacao.php">Regulação</a></li>
                            <li class="breadcrumb-item active">Detalhes da solicitação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo da página -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- Card de visualização -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informações da Solicitação</h3>
                        </div>
                        <!-- Corpo do card -->
                        <div class="card-body">
                            <!-- Aplicando a classe compact-table para estilização personalizada -->
                            <table class="table table-bordered compact-table">
                                <tr>
                                    <th>Nome do Paciente:</th>
                                    <td><?= htmlspecialchars($solicitacao['nomePaciente']) ?></td>
                                </tr>
                                <tr>
                                    <th>Idade:</th>
                                    <td><?= htmlspecialchars($solicitacao['idade']) ?> anos</td>
                                </tr>
                                <tr>
                                    <th>Sexo:</th>
                                    <td><?= htmlspecialchars($solicitacao['sexo']) ?></td>
                                </tr>
                                <tr>
                                    <th>Procedimento:</th>
                                    <td><?= htmlspecialchars($solicitacao['tipoServicoConsulta']) ?></td>
                                </tr>
                                <tr>
                                    <th>Nome do Médico:</th>
                                    <td><?= htmlspecialchars($solicitacao['nomeMedico']) ?></td>
                                </tr>
                                <tr>
                                    <th>Data de Solicitação:</th>
                                    <td><?= date('d/m/Y', strtotime($solicitacao['data_recebido_secretaria'])) ?></td>
                                </tr>
                            </table>
                        </div>
                        <!-- Footer do card -->
                        <div class="card-footer">
                            <a href="regulacao.php" class="btn btn-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../../includes/footer.php'; ?>
