<?php
// Conexão com o banco de dados
require '../includes/dbconnect.php';

// Variáveis de controle de paginação e busca
$limiteExibicao = isset($_GET['limiteExibicao']) ? intval($_GET['limiteExibicao']) : 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$buscaNome = isset($_GET['buscaNome']) ? $_GET['buscaNome'] : '';
$buscaCPF = isset($_GET['buscaCPF']) ? $_GET['buscaCPF'] : '';
$buscaCNS = isset($_GET['buscaCNS']) ? $_GET['buscaCNS'] : '';
$buscaDataNascimento = isset($_GET['buscaDataNascimento']) ? $_GET['buscaDataNascimento'] : '';
$buscaNomeMae = isset($_GET['buscaNomeMae']) ? $_GET['buscaNomeMae'] : '';

// Base da SQL
$sql = "SELECT id_cidadao, nu_cpf AS cpf, no_cidadao AS nome, nu_cns AS cns, 
DATE_FORMAT(dt_nascimento, '%d-%m-%Y') AS data_nascimento, 
DATE_FORMAT(dt_obito, '%d-%m-%Y') AS data_obito, 
nu_telefone_residencial AS telefone_residencial, nu_telefone_celular AS telefone_celular, 
nu_telefone_contato AS telefone_contato, no_mae FROM tb_cidadao WHERE 1=1";

// Arrays para armazenar tipos e parâmetros
$types = '';
$params = [];

// Adicionar condições dinamicamente conforme os filtros
if ($buscaNome != '') {
    $sql .= " AND no_cidadao LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaNome . '%';
}
if ($buscaCPF != '') {
    $sql .= " AND nu_cpf LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaCPF . '%';
}
if ($buscaCNS != '') {
    $sql .= " AND nu_cns LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaCNS . '%';
}
if ($buscaDataNascimento != '') {
    $sql .= " AND dt_nascimento = ?";
    $types .= 's';
    $params[] = $buscaDataNascimento;
}
if ($buscaNomeMae != '') {
    $sql .= " AND no_mae LIKE ?";
    $types .= 's';
    $params[] = '%' . $buscaNomeMae . '%';
}

// Executar a contagem total de registros para paginação
$sqlCount = "SELECT COUNT(*) as total FROM tb_cidadao WHERE 1=1";

// Adicionar condições ao SQL da contagem, conforme os filtros
if ($buscaNome != '') {
    $sqlCount .= " AND no_cidadao LIKE '%$buscaNome%'";
}
if ($buscaCPF != '') {
    $sqlCount .= " AND nu_cpf LIKE '%$buscaCPF%'";
}
if ($buscaCNS != '') {
    $sqlCount .= " AND nu_cns LIKE '%$buscaCNS%'";
}
if ($buscaDataNascimento != '') {
    $sqlCount .= " AND dt_nascimento = '$buscaDataNascimento'";
}
if ($buscaNomeMae != '') {
    $sqlCount .= " AND no_mae LIKE '%$buscaNomeMae%'";
}

// Executar a contagem total de registros
$resultCount = $conn->query($sqlCount);
$totalRegistros = $resultCount->fetch_assoc()['total'];

// Calcular o total de páginas
$totalPaginas = ceil($totalRegistros / $limiteExibicao);

// Adicionar limite e offset para paginação
$sql .= " LIMIT ? OFFSET ?";
$types .= 'ii';  // Dois inteiros para limite e offset
$params[] = $limiteExibicao;
$offset = ($paginaAtual - 1) * $limiteExibicao;
$params[] = $offset;

// Preparar a consulta
$stmt = $conn->prepare($sql);

// Usar "call_user_func_array" para passar os parâmetros dinamicamente
if (!empty($params)) {
    // Criar uma referência dos parâmetros para bind_param
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    // Bind dos parâmetros
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>


<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Cidadãos</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Cidadãos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/notificacoes.php'; ?>
    <!-- Filtro de busca e exibição -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-search"></i> Buscar Cidadãos</h3>
                            <a href="cidadao.php" class="btn btn-primary ms-auto">
                                <i class="fas fa-plus"></i> Cadastrar Cidadão
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="" method="get">
                                <div class="row gy-3">
                                    <!-- Primeira linha: Nome, CPF e CNS -->
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" id="buscaNome" name="buscaNome" class="form-control" value="<?php echo htmlspecialchars($buscaNome); ?>" placeholder="Nome do Cidadão">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            <input type="text" id="buscaCPF" name="buscaCPF" class="form-control" value="<?php echo htmlspecialchars($buscaCPF); ?>" placeholder="CPF">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                            <input type="text" id="buscaCNS" name="buscaCNS" class="form-control" value="<?php echo htmlspecialchars($buscaCNS); ?>" placeholder="CNS">
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulário com Botão de Limpar -->
                                <div class="row gy-3 mt-2">
                                    <!-- Primeira linha: Nome da Mãe e Data de Nascimento -->
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                                            <input type="text" id="buscaNomeMae" name="buscaNomeMae" class="form-control" value="<?php echo htmlspecialchars($buscaNomeMae); ?>" placeholder="Nome da Mãe">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" id="buscaDataNascimento" name="buscaDataNascimento" class="form-control" value="<?php echo htmlspecialchars($buscaDataNascimento); ?>">
                                        </div>
                                    </div>

                                    <!-- Botões: Filtrar e Limpar Filtros -->
                                    <div class="col-lg-4 col-md-6 col-12">
                                        <div class="d-flex">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-filter"></i> Filtrar
                                            </button>
                                            <!-- Botão Limpar Filtros -->
                                            <a href="cidadao.php" class="btn btn-secondary">
                                                <i class="fas fa-eraser"></i> Limpar Filtros
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Tabela de Cidadãos -->
                            <table id="citizenTable" class="table table-hover table-striped table-bordered mt-3">
                                <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>CNS</th>
                                    <th>Data de Nascimento / Óbito</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <!-- Exibir o nome primeiro -->
                                            <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                                            <!-- Depois exibir o CPF -->
                                            <td><?php echo formatarCPF($row["cpf"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["cns"]); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row["data_nascimento"]); ?>
                                                <?php if (!empty($row["data_obito"]) && $row["data_obito"] != '00-00-0000'): ?>
                                                    <br><i class="fas fa-cross text-danger"></i> <?php echo htmlspecialchars($row["data_obito"]); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row["telefone_residencial"])): ?>
                                                    <i class="fas fa-phone" data-bs-toggle="tooltip" title="Telefone Residencial"></i>
                                                    <?php echo htmlspecialchars($row["telefone_residencial"]); ?><br>
                                                <?php endif; ?>
                                                <?php if (!empty($row["telefone_celular"])): ?>
                                                    <i class="fas fa-mobile-alt" data-bs-toggle="tooltip" title="Telefone Celular"></i>
                                                    <?php echo htmlspecialchars($row["telefone_celular"]); ?><br>
                                                <?php endif; ?>
                                                <?php if (!empty($row["telefone_contato"])): ?>
                                                    <i class="fas fa-phone-square" data-bs-toggle="tooltip" title="Telefone de Contato"></i>
                                                    <?php echo htmlspecialchars($row["telefone_contato"]); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Botão para Visualizar o Cidadão -->
                                                <a href="cidadao.php?action=visualizar&id=<?php echo $row['id_cidadao']; ?>" class="btn btn-success btn-sm" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Botão para Editar o Cidadão -->
                                                <a href="cidadao.php?action=editar&id=<?php echo $row['id_cidadao']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum cidadão encontrado</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Paginação -->
                            <?php if ($totalPaginas > 1): ?>
                                <div class="row">
                                    <div class="col-12">
                                        <ul class="pagination pagination-sm justify-content-center">
                                            <li class="page-item <?php if($paginaAtual == 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=1"><i class="fas fa-chevron-left"></i> Primeira</a>
                                            </li>
                                            <li class="page-item <?php if($paginaAtual == 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
                                            </li>
                                            <?php for ($i = max(1, $paginaAtual - 2); $i <= min($paginaAtual + 2, $totalPaginas); $i++): ?>
                                                <li class="page-item <?php echo $paginaAtual == $i ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>">Próxima <i class="fas fa-chevron-right"></i></a>
                                            </li>
                                            <li class="page-item <?php if($paginaAtual == $totalPaginas) echo 'disabled'; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $totalPaginas; ?>">Última <i class="fas fa-chevron-right"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../includes/footer.php'; ?>

<script>
    // Ocultar mensagens de sucesso após 3 segundos
    setTimeout(function() {
        var successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }, 3000);

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php
// Função para formatar CPF
function formatarCPF($cpf) {
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}
?>
