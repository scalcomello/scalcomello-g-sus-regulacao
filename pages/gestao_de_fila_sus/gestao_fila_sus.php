<?php
require '../../includes/dbconnect.php'; // Conexão com o banco de dados

// php



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
            <!-- Barra de pesquisa -->
            <div class="row">
                <div class="col-12">
                    <h3>Procedimentos Disponíveis</h3>
                    <input type="text" id="searchProcedures" class="form-control" placeholder="Pesquisar procedimentos...">
                </div>
            </div>

            <!-- Seleção de Procedimentos -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="row">
                        <?php
                        // Consulta para listar os procedimentos
                        $sql_procedimentos = "SELECT idProcedimento, procedimento FROM procedimento";
                        $result_procedimentos = $conn->query($sql_procedimentos);

                        if ($result_procedimentos === false) {
                            echo "Erro na consulta SQL: " . $conn->error;
                        } else {
                            if ($result_procedimentos->num_rows > 0) {
                                $count = 0; // Contador para dividir colunas

                                // Abrir a primeira coluna
                                echo '<div class="col-md-6"><ul class="procedure-list">';
                                while ($procedimento = $result_procedimentos->fetch_assoc()) {
                                    // Atualizando o link para direcionar para a nova página gestao_fila_sus_procedimento.php
                                    echo "<li><a href='gestao_fila_sus_procedimento.php?procedimento_id=".$procedimento['idProcedimento']."'>".$procedimento['procedimento']."</a></li>";

                                    $count++;

                                    // A cada 15 itens, fecha a coluna e abre uma nova
                                    if ($count % 15 == 0) {
                                        echo '</ul></div><div class="col-md-6"><ul class="procedure-list">';
                                    }
                                }
                                echo '</ul></div>'; // Fechar última coluna
                            } else {
                                echo "<p>Nenhum procedimento encontrado.</p>";
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </section>



    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../../includes/footer.php'; ?>

<!-- Script para barra de pesquisa -->
<script>
    document.getElementById('searchProcedures').addEventListener('keyup', function() {
        var filter = this.value.toUpperCase();
        var lists = document.querySelectorAll('.procedure-list'); // Pega todas as listas
        lists.forEach(function(ul) {
            var li = ul.getElementsByTagName('li');
            for (var i = 0; i < li.length; i++) {
                var a = li[i].getElementsByTagName("a")[0];
                var txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        });
    });
</script>

<!-- Estilos CSS -->
<style>
    #procedureList {
        max-height: 600px; /* Altura fixa com scroll */
        overflow-y: auto;
        list-style-type: none;
        padding-left: 0;
    }
    #procedureList li {
        padding: 10px 0;
    }
    #procedureList li a {
        text-decoration: none;
        color: #007bff;
    }
    #procedureList li a:hover {
        text-decoration: underline;
    }
</style>
