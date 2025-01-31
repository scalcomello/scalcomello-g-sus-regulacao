<?php
require '../../includes/dbconnect.php';

// Obtém o valor da busca
$searchQuery = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// Verifica se a busca está vazia
if (empty($searchQuery)) {
    echo '<div class="alert alert-warning">Por favor, insira um termo de busca.</div>';
    exit;
}

// Consulta SQL com os campos corretos baseados na estrutura da tabela
$sql = "SELECT id_cidadao, nu_cpf, nu_cns, no_cidadao, dt_nascimento, no_mae, nu_telefone_residencial, nu_telefone_celular, no_sexo, dt_atualizado, ds_cep
        FROM tb_cidadao
        WHERE no_cidadao LIKE ? OR nu_cpf LIKE ? OR nu_cns LIKE ? OR no_mae LIKE ?";

// Prepara a consulta SQL
$stmt = $conn->prepare($sql);

// Verifica se a consulta foi preparada corretamente
if (!$stmt) {
    die("Erro na consulta: " . $conn->error);
}

// Liga os parâmetros para a consulta
$stmt->bind_param('ssss', $searchQuery, $searchQuery, $searchQuery, $searchQuery);

// Executa a consulta
$stmt->execute();
$result = $stmt->get_result();

// Verifica se encontrou resultados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Exibe os resultados em um card estilizado
        echo '
        <div class="card shadow result-card mb-3"  margin: 0 auto;">
            <div class="card-body">
                <!-- Linha flexível para o nome e os botões -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <!-- Nome do cidadão -->
                 <p class="font-weight-bold fs-3 mb-0">' . htmlspecialchars($row['no_cidadao']) . '</p>
                    
                    <!-- Botões alinhados à direita -->
                    <div>
                        <a href="exibir_cidadao.php?id=' . $row['id_cidadao'] . '" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> Visualizar
                        </a>
                        <a href="inserir_solicitacao.php?id=' . $row['id_cidadao'] . '" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Selecionar
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Primeira coluna: informações principais -->
                    <div class="col-md-6">
                        <!-- CPF e outras informações -->
                        <p class="mb-1"><i class="fas fa-id-card"></i> CPF: ' . substr($row['nu_cpf'], 0, 3) . '.' . substr($row['nu_cpf'], 3, 3) . '.' . substr($row['nu_cpf'], 6, 3) . '-' . substr($row['nu_cpf'], -2) . '</p>
                        <p class="mb-1"><i class="fas fa-address-card"></i> CNS: ' . htmlspecialchars($row['nu_cns']) . '</p>
                        <p class="mb-1"><i class="fas fa-calendar-alt"></i> Data de Nascimento: ' . date('d/m/Y', strtotime($row['dt_nascimento'])) . '</p>
                        <p class="mb-1"><i class="fas fa-venus-mars"></i> Sexo: ' . htmlspecialchars($row['no_sexo']) . '</p>
                    </div>

                    <!-- Segunda coluna: informações adicionais -->
                    <div class="col-md-6">
                        <p class="mb-1"><i class="fas fa-female"></i> Nome da mãe: ' . htmlspecialchars($row['no_mae']) . '</p>
                        <p class="mb-1"><i class="fas fa-phone"></i> Telefone Residencial: ' . htmlspecialchars($row['nu_telefone_residencial']) . '</p>
                        <p class="mb-1"><i class="fas fa-mobile-alt"></i> Celular: ' . htmlspecialchars($row['nu_telefone_celular']) . '</p>
                        <p class="mb-1"><i class="fas fa-sync"></i> Última atualização: ' . htmlspecialchars($row['dt_atualizado']) . '</p>
                    </div>
                </div>
            </div>
        </div>';
    }
} else {
    // Exibe mensagem caso não encontre resultados
    echo '<div class="alert alert-info">Nenhum cidadão encontrado para o termo de busca.</div>';
}
?>
