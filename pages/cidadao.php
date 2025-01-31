<?php
require '../includes/dbconnect.php'; // Conexão com o banco de dados

$action = isset($_GET['action']) ? $_GET['action'] : 'cadastrar';
$id_cidadao = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cidadao = null;

if ($id_cidadao > 0) {
    // Se for para editar ou visualizar, buscar os dados do cidadão
    $sql = "SELECT * FROM tb_cidadao WHERE id_cidadao = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cidadao);
    $stmt->execute();
    $result = $stmt->get_result();
    $cidadao = $result->fetch_assoc();
    $stmt->close();
}

// Verificamos se o formulário foi enviado para salvar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar dados do formulário e limpar
    $nome = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['no_cidadao']);
    $cpf = preg_replace("/[^0-9]/", "", $_POST['nu_cpf']);
    $cns = preg_replace("/[^0-9]/", "", $_POST['nu_cns']);
    $dataNasc = $_POST['dt_nascimento'];
    $mae = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['no_mae']);
    $pai = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['no_pai']);
    $cep = preg_replace("/[^0-9]/", "", $_POST['ds_cep']);
    $complemento = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['ds_complemento']);
    $logradouro = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['ds_logradouro']);
    $numero = preg_replace("/[^0-9]/", "", $_POST['nu_numero']);
    $bairro = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['no_bairro']);
    $telefoneRes = preg_replace("/[^0-9]/", "", $_POST['nu_telefone_residencial']);
    $telefoneCel = preg_replace("/[^0-9]/", "", $_POST['nu_telefone_celular']);
    $telefoneCont = preg_replace("/[^0-9]/", "", $_POST['nu_telefone_contato']);
    $email = $_POST['ds_email'];
    $sexo = $_POST['no_sexo'];
    $agenteSaude = preg_replace("/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/", "", $_POST['agente_de_saude']);
    $obito = $_POST['dt_obito'];

    if ($action == 'editar' && $id_cidadao > 0) {
        // Atualizar o cidadão existente
        $sql = "UPDATE tb_cidadao SET no_cidadao=?, nu_cpf=?, nu_cns=?, dt_nascimento=?, no_mae=?, no_pai=?, ds_cep=?, ds_complemento=?, ds_logradouro=?, nu_numero=?, no_bairro=?, nu_telefone_residencial=?, nu_telefone_celular=?, nu_telefone_contato=?, ds_email=?, no_sexo=?, agente_de_saude=?, dt_obito=? WHERE id_cidadao=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssssi", $nome, $cpf, $cns, $dataNasc, $mae, $pai, $cep, $complemento, $logradouro, $numero, $bairro, $telefoneRes, $telefoneCel, $telefoneCont, $email, $sexo, $agenteSaude, $obito, $id_cidadao);
    } else {
        // Inserir novo cidadão
        $sql = "INSERT INTO tb_cidadao (no_cidadao, nu_cpf, nu_cns, dt_nascimento, no_mae, no_pai, ds_cep, ds_complemento, ds_logradouro, nu_numero, no_bairro, nu_telefone_residencial, nu_telefone_celular, nu_telefone_contato, ds_email, no_sexo, agente_de_saude, dt_obito) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssss", $nome, $cpf, $cns, $dataNasc, $mae, $pai, $cep, $complemento, $logradouro, $numero, $bairro, $telefoneRes, $telefoneCel, $telefoneCont, $email, $sexo, $agenteSaude, $obito);
    }

    if ($stmt->execute()) {
        header("Location: listar_cidadao.php?mensagem=sucesso");
        $_SESSION['mensagem'] = "Solicitação aprovada com sucesso!";
        exit;
    } else {
        echo "Erro: " . $stmt->error;
    }
    $stmt->close();
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
                            <li class="breadcrumb-item"><a href="listar_cidadao.php">Cidadão</a></li>
                            <li class="breadcrumb-item active">
                                <?php
                                // Ajusta o item ativo do breadcrumb com base na ação
                                if ($action == 'editar') {
                                    echo 'Editar Cidadão';
                                } elseif ($action == 'visualizar') {
                                    echo 'Visualizar Cidadão';
                                } else {
                                    echo 'Cadastrar Cidadão';
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
                            <h3 class="card-title">Dados do Cidadão</h3>
                        </div>
                        <form action="" method="post" id="formCidadao">
                            <div class="card-body">
                                <!-- Nome, CPF, Sexo, Data de Nascimento -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_cidadao">Nome <span class="text-danger">*</span></label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['no_cidadao']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="no_cidadao" id="no_cidadao" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['no_cidadao']) : ''; ?>" maxlength="500" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nu_cpf">CPF <span class="text-danger">*</span></label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_cpf']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_cpf" id="nu_cpf" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_cpf']) : ''; ?>" maxlength="11" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="no_sexo">Sexo <span class="text-danger">*</span></label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? ucfirst(strtolower(htmlspecialchars($cidadao['no_sexo']))) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <select class="form-control" name="no_sexo" id="no_sexo" required>
                                                    <option value="">Selecione</option>
                                                    <option value="MASCULINO" <?php echo ($cidadao && strtolower($cidadao['no_sexo']) == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                                    <option value="FEMININO" <?php echo ($cidadao && strtolower($cidadao['no_sexo']) == 'feminino') ? 'selected' : ''; ?>>Feminino</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Data de Nascimento -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dt_nascimento">Data de Nascimento <span class="text-danger">*</span></label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['dt_nascimento']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="date" class="form-control" name="dt_nascimento" id="dt_nascimento" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['dt_nascimento']) : ''; ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- CNS -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nu_cns">CNS</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_cns']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_cns" id="nu_cns" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_cns']) : ''; ?>" maxlength="16">
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Data de Óbito -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dt_obito">Data de Óbito</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao && !empty($cidadao['dt_obito'])) ? htmlspecialchars($cidadao['dt_obito']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="date" class="form-control" name="dt_obito" id="dt_obito" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['dt_obito']) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>


                                <!-- Nome da Mãe, Nome do Pai -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_mae">Nome da Mãe</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['no_mae']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="no_mae" id="no_mae" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['no_mae']) : ''; ?>" maxlength="255">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_pai">Nome do Pai</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['no_pai']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="no_pai" id="no_pai" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['no_pai']) : ''; ?>" maxlength="255">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Telefone Residencial, Telefone Celular, Telefone Contato -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nu_telefone_residencial">Telefone Residencial</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_residencial']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_telefone_residencial" id="nu_telefone_residencial" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_residencial']) : ''; ?>" maxlength="10">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nu_telefone_celular">Telefone Celular</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_celular']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_telefone_celular" id="nu_telefone_celular" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_celular']) : ''; ?>" maxlength="10">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nu_telefone_contato">Telefone de Contato</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_contato']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_telefone_contato" id="nu_telefone_contato" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_telefone_contato']) : ''; ?>" maxlength="10">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- E-mail -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="ds_email">E-mail</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_email']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="email" class="form-control" name="ds_email" id="ds_email" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_email']) : ''; ?>" maxlength="50">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Endereço -->
                                <h5>Endereço</h5>
                                <div class="row mb-3">
                                    <!-- CEP, Logradouro, Número -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ds_cep">CEP</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_cep']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="ds_cep" id="ds_cep" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_cep']) : ''; ?>" maxlength="8">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ds_logradouro">Logradouro</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_logradouro']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="ds_logradouro" id="ds_logradouro" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_logradouro']) : ''; ?>" maxlength="255">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nu_numero">Número</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_numero']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="nu_numero" id="nu_numero" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['nu_numero']) : ''; ?>" maxlength="6">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Complemento, Bairro, Agente de Saúde -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ds_complemento">Complemento</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_complemento']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="ds_complemento" id="ds_complemento" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['ds_complemento']) : ''; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="no_bairro">Bairro</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['no_bairro']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="no_bairro" id="no_bairro" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['no_bairro']) : ''; ?>" maxlength="100">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="agente_de_saude">Agente de Saúde</label>
                                            <?php if ($action == 'visualizar'): ?>
                                                <p class="form-control-plaintext"><?php echo ($cidadao) ? htmlspecialchars($cidadao['agente_de_saude']) : 'Não informado'; ?></p>
                                            <?php else: ?>
                                                <input type="text" class="form-control" name="agente_de_saude" id="agente_de_saude" value="<?php echo ($cidadao) ? htmlspecialchars($cidadao['agente_de_saude']) : ''; ?>" maxlength="100">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões de ação -->
                                <div class="card-footer d-flex justify-content-start">
                                    <?php if ($action == 'visualizar') : ?>
                                        <!-- Botão de Editar no modo de visualização -->
                                        <a href="cidadao.php?action=editar&id=<?php echo $cidadao['id_cidadao']; ?>" class="btn btn-warning me-2">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($action != 'visualizar') : ?>
                                        <!-- Botão Salvar no modo de edição/cadastro -->
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-save"></i> Salvar
                                        </button>
                                    <?php endif; ?>

                                    <!-- Botão de Cancelar, que sempre aparece -->
                                    <a href="listar_cidadao.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
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

<script>
    $(document).ready(function () {
        // Máscaras de entrada
        $('#nu_cpf').mask('000.000.000-00', {reverse: true});
        $('#nu_cns').mask('0000000000000000'); // CNS sem espaços ou símbolos
        $('#nu_telefone_residencial, #nu_telefone_celular, #nu_telefone_contato').mask('(00) 00000-0000');
        $('#ds_cep').mask('00000-000');

        // Remover máscara antes de submeter o CPF
        $('#formCidadao').on('submit', function() {
            var cpf = $('#nu_cpf').val().replace(/\D/g, ''); // Remover todos os caracteres não numéricos
            $('#nu_cpf').val(cpf); // Atualizar o campo CPF sem a máscara

            // Remover máscaras dos telefones
            $('#nu_telefone_residencial').val($('#nu_telefone_residencial').val().replace(/\D/g, ''));
            $('#nu_telefone_celular').val($('#nu_telefone_celular').val().replace(/\D/g, ''));
            $('#nu_telefone_contato').val($('#nu_telefone_contato').val().replace(/\D/g, ''));
        });

        // Validar campos para apenas letras e espaços
        $('#no_cidadao, #no_mae, #no_pai, #ds_logradouro, #ds_complemento, #no_bairro, #agente_de_saude').on('input', function () {
            this.value = this.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/g, '');
        });

        // Validar campo de número para aceitar apenas números
        $('#nu_numero').on('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });
</script>
