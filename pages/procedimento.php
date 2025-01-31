<?php
require '../includes/dbconnect.php'; // Conexão com o banco de dados

// Inicializa as variáveis
$action = isset($_GET['action']) ? $_GET['action'] : 'novo';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$procedimento = [];

// Se a ação for "editar" ou "visualizar", buscamos os dados do procedimento
if ($id && ($action == 'editar' || $action == 'visualizar')) {
    $stmt = $conn->prepare("SELECT codigo, tipo, procedimento, procedimento_especifico FROM procedimento WHERE idProcedimento = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $procedimento = $result->fetch_assoc();
}

// Se o formulário foi enviado, processamos o cadastro/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $tipo = $_POST['tipo'];
    $procedimento_nome = $_POST['procedimento'];
    $procedimento_especifico = $_POST['procedimento_especifico'];

    if ($action == 'editar') {
        // Atualiza procedimento
        $stmt = $conn->prepare("UPDATE procedimento SET codigo = ?, tipo = ?, procedimento = ?, procedimento_especifico = ? WHERE idProcedimento = ?");
        $stmt->bind_param('ssssi', $codigo, $tipo, $procedimento_nome, $procedimento_especifico, $id);
    } else {
        // Cadastra novo procedimento
        $stmt = $conn->prepare("INSERT INTO procedimento (codigo, tipo, procedimento, procedimento_especifico) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $codigo, $tipo, $procedimento_nome, $procedimento_especifico);
    }

    if ($stmt->execute()) {
        header("Location: listar_procedimento.php?mensagem=sucesso");
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
                    <h3 class="mb-0"><?= $action == 'editar' ? 'Editar Procedimento' : ($action == 'visualizar' ? 'Visualizar Procedimento' : 'Cadastrar Procedimento') ?></h3>
                </div>
                <div class="col-sm-6">
                    <?php if ($action == 'visualizar'): ?>
                        <a href="procedimento.php?action=editar&id=<?= $id ?>" class="btn btn-warning float-sm-end">
                            <i class="fas fa-edit"></i> Editar Procedimento
                        </a>
                    <?php endif; ?>
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
                            <h3 class="card-title">Dados do Procedimento</h3>
                        </div>
                        <form action="" method="post">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <!-- Código -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codigo">Código</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($procedimento['codigo'] ?? 'Não informado') ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="codigo" id="codigo" value="<?= htmlspecialchars($procedimento['codigo'] ?? '') ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Tipo -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipo">Tipo</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($procedimento['tipo'] ?? 'Não informado') ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="tipo" id="tipo" value="<?= htmlspecialchars($procedimento['tipo'] ?? '') ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Procedimento -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="procedimento">Procedimento</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($procedimento['procedimento'] ?? 'Não informado') ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="procedimento" id="procedimento" value="<?= htmlspecialchars($procedimento['procedimento'] ?? '') ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Procedimento Específico -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="procedimento_especifico">Procedimento Específico</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($procedimento['procedimento_especifico'] ?? 'Não informado') ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="procedimento_especifico" id="procedimento_especifico" value="<?= htmlspecialchars($procedimento['procedimento_especifico'] ?? '') ?>">
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

                                <!-- Botão Editar quando estiver visualizando -->
                                <?php if ($action == 'visualizar'): ?>
                                    <a href="procedimento.php?action=editar&id=<?= $id ?>" class="btn btn-warning">Editar</a>
                                <?php endif; ?>

                                <!-- Botão Cancelar -->
                                <a href="listar_procedimento.php" class="btn btn-secondary">Cancelar</a>
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
