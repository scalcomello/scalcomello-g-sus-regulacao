<!-- /public/inicio.php -->
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="app-main">
    <!-- Breadcrumb -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Início</h3>
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

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Default box -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Menu</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Bloco Nova Solicitação -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>Nova Solicitação</h3>
                                            <p>Registre solicitações de pedidos de Exames, Consultas e Cirurgias</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <a href="solicitacoes/nova_solicitacao.php" class="small-box-footer">
                                            Agendar <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Bloco Consultar Solicitações -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="small-box bg-primary">
                                        <div class="inner">
                                            <h3>Consultar Solicitações e Agendar Retorno</h3>
                                            <p>Consulte as solicitações de agendamentos</p>
                                            <p>Faça Reeagendamento dos retornos</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <a href="solicitacoes/exibir_solicitacao.php" class="small-box-footer">
                                            Consultar <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Bloco Exames Laboratoriais -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>Exames Laboratoriais</h3>
                                            <p>Gerenciamento de exames laboratoriais</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-vials"></i>
                                        </div>
                                        <a href="exames_laboratoriais/exames_laboratoriais.php" class="small-box-footer">
                                            Acessar <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Bloco Gerenciamento de Pacientes Agendados -->
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>Pacientes Agendados</h3>
                                            <p>Acompanhe os pacientes com agendamentos</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <a href="transporte/gerenciamento_pacientes.php" class="small-box-footer">
                                            Gerenciar <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Bloco Gestão de Fila SUS -->
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3 style="font-size: 24px;">Gestão de Fila SUS</h3>
                                            <p style="font-size: 16px;">Acompanhe e gerencie as filas do SUS</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-users" style="font-size: 36px;"></i>
                                        </div>
                                        <a href="gestao_de_fila_sus/gestao_fila_sus.php" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Bloco de Exportação de Arquivo Transporte SUS -->
                                    <div class="small-box bg-secondary">
                                        <div class="inner">
                                            <h3 style="font-size: 24px;">Exportação de Arquivo Transporta SUS</h3>
                                            <p style="font-size: 16px;">Exportar arquivo para o BPA</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-file-export" style="font-size: 36px;"></i>
                                        </div>
                                        <a href="transporta_sus/exporta_arquivo_bpa.php" class="small-box-footer">Exportar <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <!-- Footer Opcional -->
                        </div>
                        <!-- /.card-footer-->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</main> <!-- Fechamento do main --> <!-- ATENÇÃO: Adicione esta linha para fechar corretamente o main -->

<!-- Inclua o Footer -->
<?php include '../includes/footer.php'; ?>
