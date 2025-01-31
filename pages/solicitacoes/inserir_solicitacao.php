<?php
ob_start(); // Iniciar o buffer de saída
require '../../includes/dbconnect.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Definir quais passos estão ativos
$step1_active = true;  // Passo 1: Buscar Cidadão
$step2_active = true; // Passo 2: Informar Detalhes
$step3_active = false; // Passo 3: Finalizar

// Verificar se a sessão já está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cidadao_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['cidadao_id']) ? intval($_POST['cidadao_id']) : 0);
$usuario_id = $_SESSION['id_usuario']; // Pega o ID do usuário logado

$cidadao_nome = '';
if ($cidadao_id) {
    $result = $conn->query("SELECT no_cidadao FROM tb_cidadao WHERE id_cidadao = $cidadao_id");
    if ($result && $row = $result->fetch_assoc()) {
        $cidadao_nome = $row['no_cidadao'];
    }
}

$medicos = $conn->query("SELECT idMedico, nome FROM medico ORDER BY nome ASC");
$procedimentos = $conn->query("SELECT idProcedimento, procedimento FROM procedimento");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idMedico = $_POST['idMedico'];
    $procedimento_ids = $_POST['procedimento_ids'];
    $tipos_ultrassom = $_POST['tipos_ultrassom'] ?? [];
    $tipos_doppler = $_POST['tipos_doppler'] ?? []; // Adicionado para capturar tipos de Doppler

    // Aqui usamos o formato correto
    $data_solicitacao = DateTime::createFromFormat('Y-m-d', $_POST['data_solicitacao']);

    // Verificar se a criação da data foi bem-sucedida
    if ($data_solicitacao === false) {
        $errors = DateTime::getLastErrors();
        echo "Erro ao criar a data: ";
        print_r($errors);
        exit; // Interrompe a execução se ocorrer um erro
    }

    $data_solicitacao = $data_solicitacao->format('Y-m-d');

    $classificacao = $_POST['classificacao'];
    $status_procedimento = ($classificacao == 'Urgente' || $classificacao == 'Judicial') ? 'Aguardando Regulação' : 'Aguardando';
    $regulacao = ($classificacao == 'Urgente' || $classificacao == 'Judicial') ? 1 : 0;
    date_default_timezone_set('America/Sao_Paulo');
    $data_recebido_secretaria = date('Y-m-d H:i:s');

    $conn->begin_transaction();

    try {
        $protocol_prefix = date('dmy') . count($procedimento_ids);
        $inserted_ids = [];
        $protocolo_gerado = false;

        foreach ($procedimento_ids as $procedimento_id) {
            $procedimento_nome = $conn->query("SELECT procedimento FROM procedimento WHERE idProcedimento = $procedimento_id")->fetch_assoc()['procedimento'];

            // Inserção para Ultrassonografia
            if ($procedimento_nome === 'Ultrassonografia' && !empty($tipos_ultrassom)) {
                foreach ($tipos_ultrassom as $tipo_ultrassom) {
                    $sql = "INSERT INTO solicitacao (cidadao_id, idMedico, procedimento_id, data_solicitacao, data_recebido_secretaria, classificacao, regulacao, status_procedimento, tipo_procedimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        throw new Exception('Erro de preparação: ' . $conn->error);
                    }

                    $stmt->bind_param("iiisssiss", $cidadao_id, $idMedico, $procedimento_id, $data_solicitacao, $data_recebido_secretaria, $classificacao, $regulacao, $status_procedimento, $tipo_ultrassom);
                    $stmt->execute();
                    $solicitacao_id = $stmt->insert_id;
                    $inserted_ids[] = $solicitacao_id;

                    // Registrar auditoria
                    $acao = 'Inserção';
                    $descricao = 'Solicitação inserida';
                    $audit_sql = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao) VALUES (?, ?, ?, ?)";
                    $audit_stmt = $conn->prepare($audit_sql);

                    if ($audit_stmt === false) {
                        throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
                    }

                    $audit_stmt->bind_param("iiss", $usuario_id, $solicitacao_id, $acao, $descricao);
                    $audit_stmt->execute();

                    if ($audit_stmt->error) {
                        throw new Exception('Erro na execução da auditoria: ' . $audit_stmt->error);
                    }

                    if (!$protocolo_gerado) {
                        $protocolo = $protocol_prefix . $solicitacao_id;
                        $protocolo_gerado = true;
                    }
                }
            }
            // Inserção para Doppler
            elseif ($procedimento_nome === 'Doppler' && !empty($tipos_doppler)) {
                foreach ($tipos_doppler as $tipo_doppler) {
                    $sql = "INSERT INTO solicitacao (cidadao_id, idMedico, procedimento_id, data_solicitacao, data_recebido_secretaria, classificacao, regulacao, status_procedimento, tipo_procedimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        throw new Exception('Erro de preparação: ' . $conn->error);
                    }

                    $stmt->bind_param("iiisssiss", $cidadao_id, $idMedico, $procedimento_id, $data_solicitacao, $data_recebido_secretaria, $classificacao, $regulacao, $status_procedimento, $tipo_doppler);
                    $stmt->execute();
                    $solicitacao_id = $stmt->insert_id;
                    $inserted_ids[] = $solicitacao_id;

                    // Registrar auditoria
                    $acao = 'Inserção';
                    $descricao = 'Solicitação inserida';
                    $audit_sql = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao) VALUES (?, ?, ?, ?)";
                    $audit_stmt = $conn->prepare($audit_sql);

                    if ($audit_stmt === false) {
                        throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
                    }

                    $audit_stmt->bind_param("iiss", $usuario_id, $solicitacao_id, $acao, $descricao);
                    $audit_stmt->execute();

                    if ($audit_stmt->error) {
                        throw new Exception('Erro na execução da auditoria: ' . $audit_stmt->error);
                    }

                    if (!$protocolo_gerado) {
                        $protocolo = $protocol_prefix . $solicitacao_id;
                        $protocolo_gerado = true;
                    }
                }
            } else {
                $sql = "INSERT INTO solicitacao (cidadao_id, idMedico, procedimento_id, data_solicitacao, data_recebido_secretaria, classificacao, regulacao, status_procedimento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception('Erro de preparação: ' . $conn->error);
                }

                $stmt->bind_param("iiisssis", $cidadao_id, $idMedico, $procedimento_id, $data_solicitacao, $data_recebido_secretaria, $classificacao, $regulacao, $status_procedimento);
                $stmt->execute();
                $solicitacao_id = $stmt->insert_id;
                $inserted_ids[] = $solicitacao_id;

                // Registrar auditoria
                $acao = 'Inserção';
                $descricao = 'Solicitação inserida';
                $audit_sql = "INSERT INTO auditoria_solicitacao (usuario_id, solicitacao_id, acao, descricao) VALUES (?, ?, ?, ?)";
                $audit_stmt = $conn->prepare($audit_sql);

                if ($audit_stmt === false) {
                    throw new Exception('Erro de preparação da auditoria: ' . $conn->error);
                }

                $audit_stmt->bind_param("iiss", $usuario_id, $solicitacao_id, $acao, $descricao);
                $audit_stmt->execute();

                if ($audit_stmt->error) {
                    throw new Exception('Erro na execução da auditoria: ' . $audit_stmt->error);
                }

                if (!$protocolo_gerado) {
                    $protocolo = $protocol_prefix . $solicitacao_id;
                    $protocolo_gerado = true;
                }
            }
        }

        foreach ($inserted_ids as $id) {
            $sql = "UPDATE solicitacao SET numero_protocolo = ? WHERE idSolicitacao = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                throw new Exception('Erro de preparação para atualizar protocolo: ' . $conn->error);
            }

            $stmt->bind_param("si", $protocolo, $id);
            $stmt->execute();
        }

        $conn->commit();
        header("Location: /pages/solicitacoes/finalizar_solicitacao.php?id=" . $inserted_ids[0]);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao agendar exame: " . $e->getMessage();
    }
}

ob_end_flush(); // Finalizar e enviar o conteúdo do buffer
?>



<!-- Main Content -->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Nova Solicitação</h3>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="inicio.php">Início</a></li>
                            <li class="breadcrumb-item active">Nova Solicitação</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Barra de Progresso -->
                    <?php include 'progress_bar.php'; ?>

                    <!-- Destaque para o Nome do Paciente -->
                    <div class="alert alert-info text-center" role="alert">
                        <h4 class="mb-3">
                            <i class="fas fa-user-md"></i> Pedido Médico de: <strong><?= $cidadao_nome ?></strong>
                        </h4>
                        <p class="mb-0">Insira os detalhes do pedido médico e os procedimentos para continuar.</p>
                    </div>

                    <!-- Formulário de Solicitação Médica -->
                    <form action="" method="post">
                        <input type="hidden" name="cidadao_id" value="<?= $cidadao_id ?>">

                        <!-- Médico Solicitante -->
                        <div class="form-group row mb-3">
                            <label for="idMedico" class="col-sm-2 col-form-label">Médico Solicitante:</label>
                            <div class="col-sm-6">
                                <select class="form-control" id="idMedico" name="idMedico" required>
                                    <option value="" disabled selected>Selecione um médico</option>
                                    <?php while ($medico = $medicos->fetch_assoc()): ?>
                                        <option value="<?= $medico['idMedico'] ?>"><?= htmlspecialchars($medico['nome']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Procedimentos Solicitados -->
                        <div class="form-group row mb-3">
                            <label for="procedimento_ids" class="col-sm-2 col-form-label">Quais ou qual procedimento solicitado?</label>
                            <div class="col-sm-6">
                                <select class="form-control select2" id="procedimento_ids" name="procedimento_ids[]" multiple="multiple" required>
                                    <option value="" disabled>Selecione um ou mais procedimentos</option>
                                    <?php while ($procedimento = $procedimentos->fetch_assoc()): ?>
                                        <option value="<?= $procedimento['idProcedimento'] ?>" data-nome="<?= htmlspecialchars($procedimento['procedimento']) ?>">
                                            <?= htmlspecialchars($procedimento['procedimento']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Campo adicional para tipos de Ultrassonografia -->
                        <div class="form-group row mb-3" id="ultrassonografia_tipo" style="display: none;">
                            <label for="tipos_ultrassom" class="col-sm-2 col-form-label">Tipo de Ultrassonografia:</label>
                            <div class="col-sm-6">
                                <select class="form-control select2" id="tipos_ultrassom" name="tipos_ultrassom[]" multiple>
                                    <option value="" disabled>Selecione o tipo de ultrassonografia</option>
                                    <option value="Abdome Total">Abdome Total</option>
                                    <option value="Morfologico">Morfologico</option>
                                    <option value="Abdome Superior">Abdome Superior</option>
                                    <option value="Abdome Superior com Doppler">Abdome Superior Com Doppler</option>
                                    <option value="Articulação">Articulação</option>
                                    <option value="Rins e Vias">Rins e Vias</option>
                                    <option value="Pélvico">Pélvico</option>
                                    <option value="Próstata">Próstata</option>
                                    <option value="Partes Moles">Partes Moles</option>
                                    <option value="Tireoide/Cervical">Tireoide/Cervical</option>
                                    <option value="Mamas">Mamas</option>
                                </select>
                            </div>
                        </div>

                        <!-- Campo adicional para tipos de Doppler -->
                        <div class="form-group row mb-3" id="doppler_tipo" style="display: none;">
                            <label for="tipos_doppler" class="col-sm-2 col-form-label">Tipo de Doppler:</label>
                            <div class="col-sm-6">
                                <select class="form-control select2" id="tipos_doppler" name="tipos_doppler[]" multiple>
                                    <option value="" disabled>Selecione o tipo de Doppler</option>
                                    <option value="Doppler Arterial de Membros Inferiores">Doppler Arterial de Membros Inferiores</option>
                                    <option value="Doppler Venoso de Membros Inferiores">Doppler Venoso de Membros Inferiores</option>
                                    <option value="Doppler Obstétrico">Doppler Obstétrico</option>
                                    <option value="Doppler de Carótidas">Doppler de Carótidas</option>
                                    <option value="Doppler de Tireoide">Doppler de Tireoide</option>
                                </select>
                            </div>
                        </div>

                        <!-- Data do Pedido Médico -->
                        <div class="form-group row mb-3">
                            <label for="data_solicitacao" class="col-sm-2 col-form-label">Data do Pedido Médico:</label>
                            <div class="col-sm-2">
                                <input type="date" class="form-control" id="data_solicitacao" name="data_solicitacao" required />
                            </div>
                        </div>


                        <!-- Classificação -->
                        <div class="form-group row mb-3">
                            <label for="classificacao" class="col-sm-2 col-form-label">Classificação:</label>
                            <div class="col-sm-6">
                                <select class="form-control" id="classificacao" name="classificacao" required>
                                    <option value="">Selecione a classificação</option>
                                    <option value="Eletivo">Eletivo</option>
                                    <option value="Prioritario">Prioritário</option>
                                    <option value="Urgente">Urgente</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Agendar</button>
                            <a href="nova_solicitacao.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../../includes/footer.php'; ?>

<!-- Select2 JS com Tema Bootstrap 5 -->
<script src="../../plugins/select2/js/select2.min.js"></script>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Moment.js locale para pt-BR -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/pt-br.min.js"></script>

<!-- Tempus Dominus JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2 para Médico Solicitante (Select simples)
        $('#idMedico').select2({
            theme: "bootstrap-5",
            width: '100%'
        });

        // Inicializar Select2 para Procedimento Solicitado (multi-select)
        $('#procedimento_ids').select2({
            theme: "bootstrap-5",
            placeholder: "Selecione um ou mais procedimentos",
            width: '100%'
        });

        // Inicializar Select2 para Tipos de Ultrassonografia (multi-select)
        $('#tipos_ultrassom').select2({
            theme: "bootstrap-5",
            placeholder: "Selecione o tipo de ultrassonografia",
            width: '100%'
        });

        // Inicializar Select2 para Tipos de Doppler (multi-select)
        $('#tipos_doppler').select2({
            theme: "bootstrap-5",
            placeholder: "Selecione o tipo de Doppler",
            width: '100%'
        });

        // Inicialização do Tempus Dominus para o DateTimePicker
        $('#datetimepicker').datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'pt-br'
        });

        // Lógica de mostrar/ocultar campos Ultrassonografia e Doppler
        $('#procedimento_ids').change(function() {
            var procedimentosSelecionados = $(this).val() || [];
            var mostrarUltrassonografia = false;
            var mostrarDoppler = false;

            procedimentosSelecionados.forEach(function(procedimento) {
                var nomeProcedimento = $('#procedimento_ids option[value="' + procedimento + '"]').data('nome');

                if (nomeProcedimento === 'Ultrassonografia') {
                    mostrarUltrassonografia = true;
                }
                if (nomeProcedimento === 'Doppler') {
                    mostrarDoppler = true;
                }
            });

            // Exibe ou oculta os campos
            $('#ultrassonografia_tipo').toggle(mostrarUltrassonografia);
            $('#doppler_tipo').toggle(mostrarDoppler);
        });
    });
</script>
