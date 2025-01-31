<?php
require '../includes/dbconnect.php'; // Conexão com o banco de dados

// Inicializa as variáveis
$action = isset($_GET['action']) ? $_GET['action'] : 'novo';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$unidade = [];

// Se a ação for "editar" ou "visualizar", buscamos os dados da unidade prestadora
if ($id && ($action == 'editar' || $action == 'visualizar')) {
    $stmt = $conn->prepare("SELECT unidade_prestadora AS nome, cnpj, endereco, cidade FROM prestadores WHERE id_prestador = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unidade = $result->fetch_assoc();

    // Verifica se a unidade foi encontrada
    if (!$unidade) {
        echo "Nenhuma unidade encontrada.";
        exit;
    }
}

// Se o formulário foi enviado, processamos o cadastro/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];

    if ($action == 'editar') {
        // Atualiza unidade prestadora
        $stmt = $conn->prepare("UPDATE prestadores SET unidade_prestadora = ?, cnpj = ?, endereco = ?, cidade = ? WHERE id_prestador = ?");
        $stmt->bind_param('ssssi', $nome, $cnpj, $endereco, $cidade, $id);
    } else {
        // Cadastra nova unidade prestadora
        $stmt = $conn->prepare("INSERT INTO prestadores (unidade_prestadora, cnpj, endereco, cidade) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nome, $cnpj, $endereco, $cidade);
    }

    if ($stmt->execute()) {
        header("Location: listar_unidade_prestadora.php?mensagem=sucesso");
        exit;
    } else {
        echo "Erro: " . $stmt->error;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <?php
                        // Ajusta o título com base na ação
                        if ($action == 'editar') {
                            echo 'Editar Cidadão';
                        } elseif ($action == 'visualizar') {
                            echo 'Visualizar Cidadão';
                        } else {
                            echo 'Cadastrar Cidadão';
                        }
                        ?>
                    </h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item"><a href="listar_cidadao.php">Unidades Prestadoras</a></li>
                            <li class="breadcrumb-item active">
                                <?php
                                // Ajusta o item ativo do breadcrumb com base na ação
                                if ($action == 'editar') {
                                    echo 'Editar Cidadão';
                                } elseif ($action == 'visualizar') {
                                    echo 'Visualizar Unidades Prestadora';
                                } else {
                                    echo 'Cadastrar Unidades Prestadora';
                                }
                                ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Section content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card" style="background-color: #ffffff; color: #000000;">
                        <div class="card-header">
                            <h3 class="card-title">Dados da Unidade Prestadora</h3>
                        </div>
                        <form action="" method="post">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <!-- Nome -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nome">Nome</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= isset($unidade['nome']) ? htmlspecialchars($unidade['nome']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nome" id="nome" value="<?= isset($unidade['nome']) ? htmlspecialchars($unidade['nome']) : ''; ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- CNPJ -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cnpj">CNPJ</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= isset($unidade['cnpj']) ? htmlspecialchars($unidade['cnpj']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="cnpj" id="cnpj" value="<?= isset($unidade['cnpj']) ? htmlspecialchars($unidade['cnpj']) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Endereço -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="endereco">Endereço</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= isset($unidade['endereco']) ? htmlspecialchars($unidade['endereco']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="endereco" id="endereco" value="<?= isset($unidade['endereco']) ? htmlspecialchars($unidade['endereco']) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Cidade -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cidade">Cidade</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= isset($unidade['cidade']) ? htmlspecialchars($unidade['cidade']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="cidade" id="cidade" value="<?= isset($unidade['cidade']) ? htmlspecialchars($unidade['cidade']) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de ação -->
                            <div class="card-footer">
                                <?php if ($action != 'visualizar'): ?>
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                <?php endif; ?>

                                <?php if ($action == 'visualizar'): ?>
                                    <a href="unidade_prestadora.php?action=editar&id=<?= $id ?>" class="btn btn-warning">Editar</a>
                                <?php endif; ?>

                                <a href="listar_unidade_prestadora.php" class="btn btn-secondary">Cancelar</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../includes/footer.php'; ?>
