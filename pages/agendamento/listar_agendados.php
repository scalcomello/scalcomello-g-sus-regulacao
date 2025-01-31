<?php
// Conexão com o banco de dados
require '../../includes/dbconnect.php';

// Variável para exibir mensagem de sucesso, caso a operação de reagendamento tenha ocorrido
$mensagemSucesso = '';

if (isset($_GET['reagendado']) && $_GET['reagendado'] == '1') {
    $mensagemSucesso = "O reagendamento do paciente foi realizado com sucesso!";
}

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$filtroNome = isset($_GET['filtroNome']) ? $_GET['filtroNome'] : '';
$filtroCPF = isset($_GET['filtroCPF']) ? $_GET['filtroCPF'] : '';
$filtroProcedimento = isset($_GET['filtroProcedimento']) ? $_GET['filtroProcedimento'] : '';

// SQL base para consulta
$sql = "SELECT s.idSolicitacao, c.no_cidadao AS nome, c.nu_cpf AS cpf, 
        DATE_FORMAT(c.dt_nascimento, '%d-%m-%Y') AS data_nascimento, 
        p.procedimento, s.classificacao, s.status_procedimento, 
        DATE_FORMAT(s.data_recebido_secretaria, '%d-%m-%Y') AS data_solicitacao 
        FROM solicitacao s
        JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE p.procedimento != 'Exames Laboratoriais'";

// Arrays para armazenar tipos e parâmetros de filtro dinâmico
$types = '';
$params = [];

// Adicionando filtros dinamicamente
if ($filtroNome != '') {
    $sql .= " AND c.no_cidadao LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroNome . '%';
}
if ($filtroCPF != '') {
    $sql .= " AND c.nu_cpf LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroCPF . '%';
}
if ($filtroProcedimento != '') {
    $sql .= " AND p.procedimento LIKE ?";
    $types .= 's';
    $params[] = '%' . $filtroProcedimento . '%';
}

// Paginação e execução da consulta
$sql .= " LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limiteExibicao;
$params[] = ($paginaAtual - 1) * $limiteExibicao;

// Prepara e executa a consulta
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $bind_names[] = $types;
    foreach ($params as $key => $value) {
        $bind_name = 'bind' . $key;
        $$bind_name = $value;
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/sidebar.php'; ?>

<!-- CSS para ajustar a coluna de ações -->
<style>
    .col-acoes {
        width: 120px;
        white-space: nowrap;
        text-align: center;
    }
    .col-acoes a {
        margin-right: 5px;
    }
</style>

<!-- Main Content -->
<main class="app-main">
    <!-- Mensagem de sucesso antes do breadcrumb -->
    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $mensagemSucesso ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Agendamentos</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Agendamentos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de busca e exibição -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-search"></i> Buscar Agendamentos</h3>
                </div>

                <div class="card-body">
                    <form action="" method="get">
                        <div class="row gy-3">
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="filtroNome" name="filtroNome" class="form-control" value="<?= htmlspecialchars($filtroNome); ?>" placeholder="Nome do Cidadão">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                    <input type="text" id="filtroCPF" name="filtroCPF" class="form-control" value="<?= htmlspecialchars($filtroCPF); ?>" placeholder="CPF">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-medical"></i></span>
                                    <input type="text" id="filtroProcedimento" name="filtroProcedimento" class="form-control" value="<?= htmlspecialchars($filtroProcedimento); ?>" placeholder="Procedimento">
                                </div>
                            </div>
                        </div>

                        <div class="row gy-3 mt-2">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel"></i> Filtrar
                                </button>
                                <a href="listar_agendamento.php" class="btn btn-secondary">
                                    <i class="bi bi-eraser"></i> Limpar Filtros
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Agendamentos -->
                    <table id="agendamentoTable" class="table table-hover table-striped table-bordered mt-3">
                        <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data de Nascimento</th>
                            <th>Procedimento</th>
                            <th>Classificação</th>
                            <th>Status do Procedimento</th>
                            <th>Data de Solicitação</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["nome"]); ?></td>
                                    <td><?= formatarCPF($row["cpf"]); ?></td>
                                    <td><?= htmlspecialchars($row["data_nascimento"]); ?></td>
                                    <td><?= htmlspecialchars($row["procedimento"]); ?></td>
                                    <td><?= htmlspecialchars($row["classificacao"]); ?></td>
                                    <td><?= htmlspecialchars($row["status_procedimento"]); ?></td>
                                    <td><?= htmlspecialchars($row["data_solicitacao"]); ?></td>
                                    <td class="col-acoes">
                                        <a href="inserir_agendamento.php?id=<?= $row['idSolicitacao']; ?>" class="btn btn-success btn-sm" title="Agendar">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>
                                        <a href="exibir_lista_agendamento.php?id=<?= $row['idSolicitacao']; ?>" class="btn btn-info btn-sm" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="reagendar.php?id=<?= $row['idSolicitacao']; ?>&nome=<?= urlencode($row['nome']) ?>" class="btn btn-warning btn-sm btn-reagendar" title="Reagendar" data-nome="<?= htmlspecialchars($row['nome']); ?>">
                                            <i class="bi bi-calendar-event"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Nenhum agendamento encontrado</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Paginação -->
                    <!-- (Mantém o código de paginação existente) -->
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../../includes/footer.php'; ?>

<script>
    function confirmarReagendamento(nomePaciente) {
        return confirm(`O paciente ${nomePaciente} será remarcado e seu status será ajustado para retornar ao final da fila com a data de hoje. Este procedimento será finalizado. Deseja continuar?`);
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".btn-reagendar").forEach(function(button) {
            button.addEventListener("click", function(event) {
                const nomePaciente = button.dataset.nome;

                if (!confirmarReagendamento(nomePaciente)) {
                    event.preventDefault();
                }
            });
        });
    });
</script>

<?php
function formatarCPF($cpf) {
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}
?>
