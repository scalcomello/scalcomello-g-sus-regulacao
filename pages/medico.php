<?php
require '../includes/dbconnect.php'; // Conexão com o banco de dados

// Inicializa as variáveis
$action = isset($_GET['action']) ? $_GET['action'] : 'novo';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$medico = [];

// Se a ação for "editar" ou "visualizar", buscamos os dados do médico
if ($id && ($action == 'editar' || $action == 'visualizar')) {
    $stmt = $conn->prepare("SELECT nome, especialidade, crm FROM medico WHERE idMedico = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $medico = $result->fetch_assoc();
}

// Se o formulário foi enviado, processamos o cadastro/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $especialidade = $_POST['especialidade'];
    $crm = $_POST['crm'];

    if ($action == 'editar') {
        // Atualiza médico
        $stmt = $conn->prepare("UPDATE medico SET nome = ?, especialidade = ?, crm = ? WHERE idMedico = ?");
        $stmt->bind_param('sssi', $nome, $especialidade, $crm, $id);
    } else {
        // Cadastra novo médico
        $stmt = $conn->prepare("INSERT INTO medico (nome, especialidade, crm) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $nome, $especialidade, $crm);
    }

    if ($stmt->execute()) {
        header("Location: listar_medico.php?mensagem=sucesso");
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
                        <li class="breadcrumb-item"><a href="medico.php">Médicos</a></li>
                        <li class="breadcrumb-item active">
                            <?php
                            // Ajusta o item ativo do breadcrumb com base na ação
                            if ($action == 'editar') {
                                echo 'Editar Médico';
                            } elseif ($action == 'visualizar') {
                                echo 'Visualizar Médico';
                            } else {
                                echo 'Cadastrar Médico';
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
                            <h3 class="card-title">Dados do Médico</h3>
                        </div>
                        <form action="" method="post">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <!-- Nome -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nome">Nome</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($medico['nome']) ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nome" id="nome" value="<?= htmlspecialchars($medico['nome'] ?? '') ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Especialidade -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="especialidade">Especialidade</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($medico['especialidade']) ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="especialidade" id="especialidade" value="<?= htmlspecialchars($medico['especialidade'] ?? '') ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- CRM -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="crm">CRM</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?= htmlspecialchars($medico['crm']) ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="crm" id="crm" value="<?= htmlspecialchars($medico['crm'] ?? '') ?>" required>
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
                                    <a href="medico.php?action=editar&id=<?= $id ?>" class="btn btn-warning">Editar</a>
                                <?php endif; ?>

                                <!-- Botão Cancelar -->
                                <a href="listar_medico.php" class="btn btn-secondary">Cancelar</a>
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
