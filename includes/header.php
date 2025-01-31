<?php
// Verifica se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sessão
}

// Verificar se o usuário está logado, ou seja, se o ID do usuário está definido na sessão
if (!isset($_SESSION['id_usuario'])) {
    // Redirecionar para a página de login, caso o usuário não esteja logado
    header("Location: /public/index.php");
    exit(); // Parar a execução do script após o redirecionamento
}

// ID do usuário logado
$usuario_id = $_SESSION['id_usuario'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <!-- Meta Tags para responsividade e SEO -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="G-SUS | Gestão de Exames, Consultas e Cirurgias do SUS">
    <meta name="author" content="ColorlibHQ">
    <meta name="description" content="G-SUS é um sistema de Gestão de Exames, Consultas e Cirurgias do SUS, proporcionando controle e organização no atendimento de saúde pública.">
    <meta name="keywords" content="bootstrap 5, admin dashboard, saúde pública">
    <meta name="robots" content="index, follow">
    <title>G-SUS - Gestão de Exames, Consultas e Cirurgias do SUS</title>

    <!-- Estilos locais -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome (versão mais recente) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Pace Progress -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/pace-progress/themes/black/pace-theme-flat-top.css">

    <!-- DateRange Picker -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.css">

    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

    <!-- Bootstrap Color Picker -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">



    <!-- Bootstrap4 Duallistbox (verificar compatibilidade com BS5) -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">

    <!-- BS Stepper -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/bs-stepper/css/bs-stepper.min.css">

    <!-- Dropzonejs -->
    <link rel="stylesheet" href="/vendor/almasaeed2010/adminlte/plugins/dropzone/min/dropzone.min.css">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap 5 e AdminLTE -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/adminlte.css">
    <link rel="stylesheet" href="/assets/css/overlayscrollbars.min.css">

    <!-- Select2 CSS com Tema Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Tempus Dominus (DateTime Picker) CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/pt-br.min.js"></script>

    <!-- Tempus Dominus (DateTime Picker) JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- icones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Outros scripts que não dependem de plugins jQuery podem ficar aqui -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>

<body class="layout-fixed sidebar-mini sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- Header Navbar -->
    <header class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <!-- Toggle Sidebar -->
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <!-- User Menu Dropdown -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.25rem;">
                        <!-- Ícone de Usuário -->
                        <i class="bi bi-person-circle" style="font-size: 1.55rem;"></i>
                        <span class="d-none d-md-inline" style="font-size: 1.05rem;"><?= $_SESSION['usuario'] ?></span>
                    </a>

                    <!-- Dropdown Menu -->
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end" aria-labelledby="userDropdown">
                        <li class="user-header text-center">
                            <i class="bi bi-person-circle" style="font-size: 5.5rem;"></i>
                            <p style="font-size: 1.25rem;"><?= $_SESSION['usuario'] ?></p>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a href="perfil.php" class="dropdown-item">
                                <i class="bi bi-person"></i> Visualizar Perfil
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Recarregar
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="dropdown-item">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </header>
