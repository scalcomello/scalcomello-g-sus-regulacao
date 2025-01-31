<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// Inicializar variáveis de filtro de data e paginação
$data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING) ?: '';
$data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING) ?: '';
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$limite = 10; // Limite de registros por página
$offset = ($paginaAtual - 1) * $limite; // Calcula o offset

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
            <div class="row">
                <div class="col-md-12">
                    <!-- Boletim de Produção Ambulatorial Individualizada - BPA -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Boletim de Produção Ambulatorial Individualizada - BPA</h3>
                        </div>
                        <div class="card-body">
                            <!-- Formulário para Exportação BPA -->
                            <form action="processar_exportacao_bpa.php" method="POST">
                                <div class="row">
                                    <!-- CNES -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cnes">CNES</label>
                                            <input type="text" class="form-control" id="cnes" name="cnes" placeholder="Insira o CNES" required>
                                        </div>
                                    </div>
                                    <!-- CNS Profissional -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cns_profissional">CNS do Profissional</label>
                                            <input type="text" class="form-control" id="cns_profissional" name="cns_profissional" placeholder="Insira o CNS do Profissional" required>
                                        </div>
                                    </div>
                                    <!-- Nome do Profissional -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nome_profissional">Nome do Profissional</label>
                                            <input type="text" class="form-control" id="nome_profissional" name="nome_profissional" placeholder="Insira o Nome do Profissional" required>
                                        </div>
                                    </div>
                                    <!-- CBO -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cbo">CBO</label>
                                            <input type="text" class="form-control" id="cbo" name="cbo" placeholder="Insira o CBO" required>
                                        </div>
                                    </div>
                                    <!-- Código INE -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codigo_ine">Código INE</label>
                                            <input type="text" class="form-control" id="codigo_ine" name="codigo_ine" placeholder="Insira o Código INE" required>
                                        </div>
                                    </div>
                                    <!-- Mês/Ano -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mes_ano">Mês/Ano</label>
                                            <input type="text" class="form-control" id="mes_ano" name="mes_ano" placeholder="Insira o Mês/Ano no formato AAAAMM" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botão de envio -->
                                <button type="submit" class="btn btn-primary">Exportar Arquivo BPA</button>
                            </form>
                        </div>
                    </div>

                    <!-- Filtro de Período para Pacientes Agendados -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filtro de Período</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="GET">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="data_inicio">Data Início</label>
                                        <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="data_fim">Data Fim</label>
                                        <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">Buscar</button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabela de Pacientes Agendados -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pacientes Agendados</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>CPF</th>
                                    <th>CNS</th>
                                    <th>Hora</th>
                                    <th>Local de Atendimento</th>
                                    <th>Usou Transporte</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!$conn) {
                                    die("Erro de conexão com o banco de dados.");
                                }

                                // Consulta SQL com JOIN para obter o local de atendimento da tabela prestadores
                                if ($data_inicio && $data_fim) {
                                    $stmt = $conn->prepare("
                                            SELECT 
                                                tc.no_cidadao AS Paciente,
                                                tc.nu_cpf AS CPF,
                                                tc.nu_cns AS CNS,
                                                s.hora_agendamento AS Hora,
                                                p.unidade_prestadora AS LocalAtendimento,
                                                s.usou_transporte AS UsouTransporte
                                            FROM tb_cidadao tc
                                            INNER JOIN solicitacao s ON s.cidadao_id = tc.id_cidadao
                                            LEFT JOIN prestadores p ON s.idPrestador = p.id_prestador
                                            WHERE s.data_agendamento_clinica BETWEEN ? AND ?
                                            LIMIT ? OFFSET ?
                                        ");
                                    $stmt->bind_param("ssii", $data_inicio, $data_fim, $limite, $offset);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $checked = $row['UsouTransporte'] ? 'checked' : '';
                                            echo "<tr>
                                                        <td>" . htmlspecialchars($row['Paciente']) . "</td>
                                                        <td>" . htmlspecialchars($row['CPF']) . "</td>
                                                        <td>" . htmlspecialchars($row['CNS']) . "</td>
                                                        <td>" . htmlspecialchars($row['Hora']) . "</td>
                                                        <td>" . htmlspecialchars($row['LocalAtendimento']) . "</td>
                                                        <td>
                                                            <input type='checkbox' class='usou-transporte' data-id='" . htmlspecialchars($row['CPF']) . "' $checked>
                                                        </td>
                                                      </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6'>Nenhum registro encontrado no período selecionado.</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>Selecione um período para buscar os registros.</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>

                            <!-- Paginação -->
                            <?php
                            // Consulta para contar o total de registros
                            $stmt_total = $conn->prepare("
                                SELECT COUNT(*) AS total
                                FROM solicitacao s
                                WHERE s.data_agendamento_clinica BETWEEN ? AND ?
                            ");
                            $stmt_total->bind_param("ss", $data_inicio, $data_fim);
                            $stmt_total->execute();
                            $result_total = $stmt_total->get_result();
                            $total_registros = $result_total->fetch_assoc()['total'];
                            $total_paginas = ceil($total_registros / $limite);

                            if ($total_paginas > 1) {
                                echo '<nav><ul class="pagination justify-content-center">';
                                for ($i = 1; $i <= $total_paginas; $i++) {
                                    $active = ($i == $paginaAtual) ? 'active' : '';
                                    echo "<li class='page-item $active'><a class='page-link' href='?data_inicio=$data_inicio&data_fim=$data_fim&pagina=$i'>$i</a></li>";
                                }
                                echo '</ul></nav>';
                            }
                            ?>
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



<!-- Script para atualizar o status do transporte via AJAX -->
<script>
    document.querySelectorAll('.usou-transporte').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var cpf = this.getAttribute('data-id');
            var usouTransporte = this.checked ? 1 : 0;

            // Enviar a requisição AJAX para atualizar o status no banco de dados
            fetch('atualizar_transporte.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cpf: cpf,
                    usou_transporte: usouTransporte
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Erro ao atualizar o status do transporte.');
                    }
                })
                .catch(error => console.error('Erro:', error));
        });
    });
</script>
