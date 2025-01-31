<?php

require '../includes/dbconnect.php'; // Conexão com o banco de dados

session_start(); // Iniciar a sessão

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /public/index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario']; // Recuperar o ID do usuário logado

// Atualizar os dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $cns = $_POST['cns'];
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $Estabelecimento = $_POST['Estabelecimento'];
    $CNES = $_POST['CNES'];
    $Contato = $_POST['Contato'];

    $sql = "UPDATE usuario SET nome=?, cpf=?, cns=?, data_nascimento=?, telefone=?, email=?, Estabelecimento=?, CNES=?, Contato=? WHERE id_usuario=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssi', $nome, $cpf, $cns, $data_nascimento, $telefone, $email, $Estabelecimento, $CNES, $Contato, $id_usuario);

    if ($stmt->execute()) {
        $mensagem = 'Dados atualizados com sucesso!';
    } else {
        $mensagem = 'Erro ao atualizar os dados!';
    }
}

// Selecionar os dados do usuário
$sql = "SELECT * FROM usuario WHERE id_usuario=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

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
                    <h3 class="mb-0">Perfil</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Início</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagem de sucesso ou erro -->
    <div class="container-fluid">
        <?php if (isset($mensagem)): ?>
            <div id="mensagemAlerta" class="alert <?php echo ($mensagem === 'Dados atualizados com sucesso!') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Definir um tempo para que o alerta desapareça automaticamente
        setTimeout(function() {
            var alerta = document.getElementById('mensagemAlerta');
            if (alerta) {
                alerta.classList.remove('show'); // Esconde o alerta gradualmente
                alerta.classList.add('fade');    // Adiciona o efeito de fade

                // Após o efeito fade (500ms), remover o alerta do DOM completamente
                setTimeout(function() {
                    if (alerta && alerta.parentNode) {
                        alerta.parentNode.removeChild(alerta); // Remove o alerta do DOM
                    }
                }, 500); // Aguarda 500ms (efeito fade) antes de remover
            }
        }, 5000); // Tempo em milissegundos (5000ms = 5 segundos)
    </script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h3 class="card-title">Perfil do Usuário</h3>
                        </div>
                        <div class="card-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#visualizar" role="tab">Visualizar</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#editar" role="tab">Editar</a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="visualizar" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="mt-3">Informações Pessoais</h5>
                                            <p><strong>Nome: </strong><?= htmlspecialchars($usuario['nome']) ?></p>
                                            <p><strong>CPF: </strong><?= htmlspecialchars($usuario['cpf']) ?></p>
                                            <p><strong>CNS: </strong><?= htmlspecialchars($usuario['cns']) ?></p>
                                            <p><strong>Data de Nascimento: </strong><?= date('d/m/Y', strtotime($usuario['data_nascimento'])) ?></p>
                                            <p><strong>Telefone: </strong><?= htmlspecialchars($usuario['telefone']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mt-3">Informações de Contato</h5>
                                            <p><strong>Email: </strong><?= htmlspecialchars($usuario['email']) ?></p>
                                            <p><strong>Estabelecimento: </strong><?= htmlspecialchars($usuario['Estabelecimento']) ?></p>
                                            <p><strong>CNES: </strong><?= htmlspecialchars($usuario['CNES']) ?></p>
                                            <p><strong>Contato: </strong><?= htmlspecialchars($usuario['Contato']) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="editar" role="tabpanel">
                                    <form action="perfil.php" method="post">
                                        <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>"> <!-- Campo oculto para o ID -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nome">Nome</label>
                                                    <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="cpf">CPF</label>
                                                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($usuario['cpf']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="cns">CNS</label>
                                                    <input type="text" class="form-control" id="cns" name="cns" value="<?= htmlspecialchars($usuario['cns']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="data_nascimento">Data de Nascimento</label>
                                                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($usuario['data_nascimento']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="telefone">Telefone</label>
                                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="Estabelecimento">Estabelecimento</label>
                                                    <input type="text" class="form-control" id="Estabelecimento" name="Estabelecimento" value="<?= htmlspecialchars($usuario['Estabelecimento']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="CNES">CNES</label>
                                                    <input type="text" class="form-control" id="CNES" name="CNES" value="<?= htmlspecialchars($usuario['CNES']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="Contato">Contato</label>
                                                    <input type="text" class="form-control" id="Contato" name="Contato" value="<?= htmlspecialchars($usuario['Contato']) ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary mt-4">Salvar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /.tab-content -->
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
</main> <!-- Fechamento do main -->

<!-- Inclua o Footer -->
<?php include '../includes/footer.php'; ?>
