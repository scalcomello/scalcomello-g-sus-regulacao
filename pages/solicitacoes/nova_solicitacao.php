<?php
require '../../includes/dbconnect.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Definir quais passos estão ativos
$step1_active = true;  // Passo 1: Buscar Cidadão
$step2_active = false; // Passo 2: Informar Detalhes
$step3_active = false; // Passo 3: Finalizar
?>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
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
            <!-- Card Principal (Incluindo Barra de Progresso e Formulário) -->
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Incluindo a Barra de Progresso -->
                    <?php include 'progress_bar.php'; ?>

                    <!-- Formulário de Busca -->
                    <form class="w-100 mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Nome, CNS, CPF ou Nome da Mãe" autocomplete="off">
                            <button class="btn btn-secondary ml-2" type="reset" id="reset-filters"><i class="fas fa-times"></i> Limpar filtros</button>
                        </div>
                    </form>

                    <!-- Loading indicator -->
                    <div id="loading" class="text-center d-none mb-3">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Buscando dados...</p>
                    </div>

                    <!-- Resultados da busca -->
                    <div id="results-count" class="mb-2 d-none">
                        <p class="lead"></p>
                    </div>
                    <div id="results-container" class="text-center"></div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Rodapé -->
<?php include '../../includes/footer.php'; ?>

<!-- Estilo Personalizado -->
<style>


    /* Cartões de Resultados */
    .result-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px;
        margin-bottom: 15px;
        text-align: left;
    }
    .result-card .info-icon {
        font-size: 18px;
        margin-right: 8px;
    }
    .result-card .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    /* Botões */
    .btn-primary {
        background-color: #28a745;
        border-color: #28a745;
    }
    .btn-outline-primary {
        color: #007bff;
        border-color: #007bff;
    }

    /* Indicador de Carregamento */
    #loading {
        background: rgba(255, 255, 255, 0.8);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Função debounce para otimizar a busca
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Função de busca em tempo real
        $('#search').on('input', debounce(function() {
            let searchQuery = $(this).val();
            $('#results-container').empty();
            $('#results-count').addClass('d-none');
            $('#loading').removeClass('d-none');

            $.ajax({
                url: 'busca_cidadao_ajax.php',
                method: 'GET',
                data: { search: searchQuery },
                success: function(response) {
                    $('#results-container').html(response);
                    $('#loading').addClass('d-none');

                    let resultCount = $('#results-container .result-card').length;
                    $('#results-count p').text(resultCount > 0 ? `${resultCount} cidadão(ãos) encontrado(s)` : 'Nenhum resultado encontrado.');
                    $('#results-count').removeClass('d-none');
                },
                error: function() {
                    $('#loading').addClass('d-none');
                    $('#results-container').html('<div class="alert alert-danger">Erro ao buscar dados. Tente novamente.</div>');
                }
            });
        }, 500));

        // Resetar a busca e limpar os filtros
        $('#reset-filters').click(function() {
            $('#search').val('');
            $('#results-container').empty();
            $('#results-count').addClass('d-none');
        });
    });
</script>
