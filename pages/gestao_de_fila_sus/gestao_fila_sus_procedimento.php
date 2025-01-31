<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados


// Pegar o ID do procedimento selecionado via GET
$procedimento_id = isset($_GET['procedimento_id']) ? (int)$_GET['procedimento_id'] : null;

// Se nenhum procedimento foi selecionado, redirecionar
if (!$procedimento_id) {
    echo "<p>Procedimento não selecionado.</p>";
    exit;
}

// Inativar ou ativar o paciente (se a ação for passada via GET)
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $acao = $_GET['acao'];
    $idSolicitacao = (int)$_GET['id'];

    if ($acao == 'inativar') {
        // Mudar o status para "Inativo"
        $sql_inativar = "UPDATE solicitacao SET status_procedimento = 'Inativo' WHERE idSolicitacao = ?";
        $stmt_inativar = $conn->prepare($sql_inativar);
        $stmt_inativar->bind_param("i", $idSolicitacao);
        $stmt_inativar->execute();
    } elseif ($acao == 'ativar') {
        // Mudar o status para "Aguardando"
        $sql_ativar = "UPDATE solicitacao SET status_procedimento = 'Aguardando' WHERE idSolicitacao = ?";
        $stmt_ativar = $conn->prepare($sql_ativar);
        $stmt_ativar->bind_param("i", $idSolicitacao);
        $stmt_ativar->execute();
    }

    // Redirecionar de volta para a página principal mantendo os filtros
    $query_string = $_SERVER['QUERY_STRING'];
    header("Location: gestao_fila_sus_procedimento.php?$query_string");
    exit; // Certifique-se de que o script seja interrompido após o redirecionamento
}



// Consulta para obter o nome do procedimento
$sql_procedimento_nome = "SELECT procedimento FROM procedimento WHERE idProcedimento = ?";
$stmt_nome = $conn->prepare($sql_procedimento_nome);
$stmt_nome->bind_param("i", $procedimento_id);
$stmt_nome->execute();
$result_nome = $stmt_nome->get_result();
$procedimento_nome = $result_nome->fetch_assoc()['procedimento'];


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

            <!-- Abas para alternar entre pacientes ativos e inativos -->
            <ul class="nav nav-tabs" id="tabPaciente" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="ativos-tab" data-toggle="tab" href="#ativos" role="tab" aria-controls="ativos" aria-selected="true">Pacientes Ativos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inativos-tab" data-toggle="tab" href="#inativos" role="tab" aria-controls="inativos" aria-selected="false">Pacientes Inativos</a>
                </li>
            </ul>

            <!-- Conteúdo das Abas -->
            <div class="tab-content" id="tabPacienteContent">
                <!-- Aba de Pacientes Ativos -->
                <div class="tab-pane fade show active" id="ativos" role="tabpanel" aria-labelledby="ativos-tab">
                    <div class="row mt-3">
                        <div class="col-12">
                            <h3>Pacientes Ativos</h3>

                            <!-- Filtros para a fila -->
                            <form method="GET" action="gestao_fila_sus_procedimento.php" id="filter-form">
                                <input type="hidden" name="procedimento_id" value="<?php echo $procedimento_id; ?>">

                                <div class="form-row">
                                    <!-- Filtro por nome do paciente -->
                                    <div class="form-group col-md-3">
                                        <label for="nome">Nome do Paciente:</label>
                                        <input type="text" name="nome" id="nome" class="form-control" placeholder="Digite o nome do paciente" value="<?php echo isset($_GET['nome']) ? $_GET['nome'] : ''; ?>">
                                    </div>

                                    <!-- Filtro por classificação -->
                                    <div class="form-group col-md-3">
                                        <label for="classificacao">Classificação:</label>
                                        <select name="classificacao" id="classificacao" class="form-control">
                                            <option value="">Todas</option>
                                            <option value="Urgente" <?php if (isset($_GET['classificacao']) && $_GET['classificacao'] == 'Urgente') echo 'selected'; ?>>Urgente</option>
                                            <option value="Prioridade" <?php if (isset($_GET['classificacao']) && $_GET['classificacao'] == 'Prioridade') echo 'selected'; ?>>Prioridade</option>
                                            <option value="Eletivo" <?php if (isset($_GET['classificacao']) && $_GET['classificacao'] == 'Eletivo') echo 'selected'; ?>>Eletivo</option>
                                        </select>
                                    </div>

                                    <!-- Checkboxes para ocultar status -->
                                    <div class="form-group col-md-6">
                                        <label>Status do Procedimento:</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ocultar_aguardando_regulacao" id="ocultar_aguardando_regulacao" checked>
                                            <label class="form-check-label" for="ocultar_aguardando_regulacao">Ocultar Aguardando Regulação</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ocultar_agendado" id="ocultar_agendado" checked>
                                            <label class="form-check-label" for="ocultar_agendado">Ocultar Agendado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ocultar_finalizado" id="ocultar_finalizado" checked>
                                            <label class="form-check-label" for="ocultar_finalizado">Ocultar Finalizado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ocultar_atendido" id="ocultar_atendido" checked>
                                            <label class="form-check-label" for="ocultar_atendido">Ocultar Atendido</label>
                                        </div>
                                    </div>

                                    <!-- Botão para limpar filtros -->
                                    <div class="form-group col-md-3 align-self-end">
                                        <button type="submit" class="btn btn-primary" id="filter-btn">Filtrar</button>
                                        <a href="gestao_fila_sus_procedimento.php?procedimento_id=<?php echo $procedimento_id; ?>" class="btn btn-secondary">Limpar Filtros</a>
                                    </div>
                                </div>
                            </form>

                            <?php
                            // Filtros
                            $filtro_nome = isset($_GET['nome']) ? '%' . $_GET['nome'] . '%' : '';
                            $filtro_classificacao = isset($_GET['classificacao']) ? $_GET['classificacao'] : '';
                            $filtro_ocultar_aguardando_regulacao = isset($_GET['ocultar_aguardando_regulacao']) ? true : false;
                            $filtro_ocultar_agendado = isset($_GET['ocultar_agendado']) ? true : false;
                            $filtro_ocultar_finalizado = isset($_GET['ocultar_finalizado']) ? true : false;
                            $filtro_ocultar_atendido = isset($_GET['ocultar_atendido']) ? true : false;

                            // Consulta para listar os pacientes ativos
                            $sql = "SELECT s.idSolicitacao, c.nu_cpf AS CPF, c.no_cidadao AS nome_cidadao, 
                                           TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade, 
                                           DATE_FORMAT(s.data_recebido_secretaria, '%d/%m/%Y') AS data_recebido_secretaria, 
                                           s.classificacao, s.status_procedimento
                                    FROM solicitacao s
                                    JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                                    WHERE s.procedimento_id = ? 
                                    AND s.status_procedimento != 'Inativo'";

                            // Adicionar filtro por nome
                            if ($filtro_nome) {
                                $sql .= " AND c.no_cidadao LIKE ?";
                            }

                            // Adicionar filtro por classificação
                            if ($filtro_classificacao) {
                                $sql .= " AND s.classificacao = ?";
                            }

                            // Ocultar os status marcados nos checkboxes
                            if ($filtro_ocultar_aguardando_regulacao) {
                                $sql .= " AND s.status_procedimento != 'Aguardando Regulação'";
                            }
                            if ($filtro_ocultar_agendado) {
                                $sql .= " AND s.status_procedimento != 'Agendado'";
                            }
                            if ($filtro_ocultar_finalizado) {
                                $sql .= " AND s.status_procedimento != 'Finalizado'";
                            }
                            if ($filtro_ocultar_atendido) {
                                $sql .= " AND s.status_procedimento != 'Atendido'";
                            }

                            // Ordenar pela classificação e data
                            $sql .= " ORDER BY 
                                CASE 
                                    WHEN s.classificacao = 'Urgente' THEN 1
                                    WHEN s.classificacao = 'Prioritario' THEN 2
                                    WHEN s.classificacao = 'Eletivo' THEN 3
                                    ELSE 4
                                END, s.data_recebido_secretaria ASC";

                            // Preparar e executar a consulta
                            $stmt = $conn->prepare($sql);

                            // Definir os parâmetros dinâmicos
                            if ($filtro_nome) {
                                $params = [$procedimento_id, $filtro_nome];
                                $types = "is";
                            } else {
                                $params = [$procedimento_id];
                                $types = "i";
                            }

                            if ($filtro_classificacao) {
                                $params[] = $filtro_classificacao;
                                $types .= "s";
                            }

                            // Bind dos parâmetros
                            $stmt->bind_param($types, ...$params);

                            // Executar a consulta
                            $stmt->execute();
                            $solicitacoes = $stmt->get_result();
                            $patient_count = $solicitacoes->num_rows;
                            ?>

                            <?php if ($patient_count > 0): ?>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Posição</th>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Idade</th>
                                        <th>Data Recebido Secretaria</th>
                                        <th>Classificação</th>
                                        <th>Situação</th>
                                        <th>Ações</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $posicao = 1;
                                    while ($solicitacao = $solicitacoes->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$posicao}</td>
                                                <td>{$solicitacao['nome_cidadao']}</td>
                                                <td>{$solicitacao['CPF']}</td>
                                                <td>{$solicitacao['idade']}</td>
                                                <td>{$solicitacao['data_recebido_secretaria']}</td>
                                                <td>{$solicitacao['classificacao']}</td>
                                                <td>{$solicitacao['status_procedimento']}</td>
                                                <td>
                                                    <a href='gestao_fila_sus_procedimento.php?procedimento_id={$procedimento_id}&acao=inativar&id={$solicitacao['idSolicitacao']}&{$_SERVER['QUERY_STRING']}' class='btn btn-warning'>Inativar</a>
                                                </td>
                                            </tr>";
                                        $posicao++;
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>Nenhum paciente na fila para este procedimento.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Aba de Pacientes Inativos -->
                <div class="tab-pane fade" id="inativos" role="tabpanel" aria-labelledby="inativos-tab">
                    <div class="row mt-3">
                        <div class="col-12">
                            <h3>Pacientes Inativos</h3>
                            <?php
                            // Consulta para listar os pacientes inativos
                            $sql_inativos = "SELECT s.idSolicitacao, c.no_cidadao AS nome_cidadao, c.nu_cpf AS CPF, s.classificacao, s.status_procedimento
                                             FROM solicitacao s
                                             JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
                                             WHERE s.status_procedimento = 'Inativo' AND s.procedimento_id = ?";
                            $stmt_inativos = $conn->prepare($sql_inativos);
                            $stmt_inativos->bind_param("i", $procedimento_id);
                            $stmt_inativos->execute();
                            $result_inativos = $stmt_inativos->get_result();
                            ?>

                            <?php if ($result_inativos->num_rows > 0): ?>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Classificação</th>
                                        <th>Ações</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($inativo = $result_inativos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($inativo['nome_cidadao']); ?></td>
                                            <td><?= htmlspecialchars($inativo['CPF']); ?></td>
                                            <td><?= htmlspecialchars($inativo['classificacao']); ?></td>
                                            <td>
                                                <a href="gestao_fila_sus_procedimento.php?procedimento_id=<?= $procedimento_id ?>&acao=ativar&id=<?= $inativo['idSolicitacao']; ?>&<?php echo $_SERVER['QUERY_STRING']; ?>" class="btn btn-success">Ativar</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>Nenhum paciente inativo encontrado.</p>
                            <?php endif; ?>
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

<!-- Script para submeter automaticamente o formulário de filtros apenas se os filtros ainda não estiverem aplicados -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Verificar se já há filtros na URL
        const params = new URLSearchParams(window.location.search);
        if (!params.has('nome') && !params.has('classificacao') && !params.has('ocultar_aguardando_regulacao') && !params.has('ocultar_agendado') && !params.has('ocultar_finalizado') && !params.has('ocultar_atendido')) {
            document.getElementById("filter-btn").click();
        }
    });
</script>
