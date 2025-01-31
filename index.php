<!-- /public/index.php -->
<?php
session_start();

include 'includes/dbconnect.php'; // Inclui a conexão com o banco de dados

$erroLogin = false;  // Variável para controlar o erro de login

// Verifica se o formulário foi submetido via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senhaDigitada = $_POST['senha'];

    // Consulta SQL para verificar o usuário e a senha
    $sql = "SELECT id_usuario, senha FROM usuario WHERE usuario = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Erro na preparação da consulta: ' . $conn->error);
    }

    // Vincula os parâmetros e executa a consulta
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    // Se o usuário for encontrado
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $senhaHash = $row['senha']; // Obtém a senha armazenada no banco

        // Verifica se a senha digitada corresponde ao hash
        if (password_verify($senhaDigitada, $senhaHash)) {
            // Inicia a sessão do usuário
            session_regenerate_id(true); // Regenera o ID da sessão por segurança
            $_SESSION['loggedin'] = true;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['id_usuario'] = $row['id_usuario'];

            // Redireciona para a página 'inicio.php'
            header("Location: ../pages/inicio.php");
            exit;
        } else {
            $erroLogin = true; // Senha incorreta
        }
    } else {
        $erroLogin = true; // Usuário não encontrado
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - G-SUS</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/fontawesome-free/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="assets/css/adminlte.css">

    <!-- Custom CSS -->
    <style>
        .input-group-text span.fas {
            font-size: 1.60rem; /* Tamanho dos ícones */
        }

        /* Fundo animado com gradiente */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #6a89cc, #b8e994);
            background-size: 200% 200%;
            animation: backgroundMove 10s ease infinite;
        }

        /* Animação do fundo */
        @keyframes backgroundMove {
            0% { background-position: 0 0; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0 0; }
        }

        .login-box {
            width: 400px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: white;
            margin-top: 50px;
        }

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo img {
            display: inline-block;
            background-color: transparent;
        }

        .login-logo {
            text-align: center;
        }

        .login-logo a {
            font-size: 36px;  /* Título GESUS */
            font-weight: bold;
            color: #333;
        }

        .login-logo small {
            display: block;
            font-size: 16px;  /* Subtítulo */
            color: #555;
        }

        .card {
            padding: 20px;
            border-radius: 10px;
        }

        /* Animação suave no botão */
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
            transition: transform 0.2s ease-in-out, background-color 0.2s ease-in-out;
        }

        .btn-primary:hover {
            transform: scale(1.05); /* Aumenta levemente o botão */
            background-color: #2980b9; /* Cor mais escura ao hover */
        }

        .alert {
            margin-bottom: 15px;
        }

        .version {
            font-size: 12px;
            color: #888;
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body class="hold-transition">
<div class="login-box">
    <!-- Logos do Sistema e SUS -->
    <div class="logo">
        <img src="../assets/img/logo.png" alt="Logo do Sistema" style="width: 100px; margin-right: 10px;">
        <img src="../assets/img/sus.jpg" alt="Logo SUS" style="width: 80px;">
    </div>
    <!-- Título do Sistema -->
    <div class="login-logo">
        <a href="#">G-SUS</a>
        <small>Gestão de Exames, Consultas e Cirurgias do SUS</small>
    </div>
    <!-- Box de login -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Faça login para iniciar sua sessão</p>

            <!-- Exibição de mensagem de erro -->
            <?php if ($erroLogin): ?>
                <div class="alert alert-danger">Nome de usuário ou senha incorretos</div>
            <?php endif; ?>

            <!-- Formulário de login -->
            <form action="index.php" method="post">
                <!-- Campo do Nome de Usuário -->
                <div class="input-group mb-3">
                    <input type="text" name="usuario" class="form-control" placeholder="Usuário" required aria-label="Nome de usuário">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <!-- Campo da Senha -->
                <div class="input-group mb-3">
                    <input type="password" name="senha" class="form-control" placeholder="Senha" required aria-label="Senha">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <!-- Botão de login com animação suave -->
                <div class="row">
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Versão do sistema -->
<div class="version">
    Versão 2.0
</div>

<!-- jQuery -->
<script src="assets/js/jquery.min.js" defer></script>
<!-- Bootstrap 4 -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/js/adminlte.js" defer></script>
</body>
</html>
