<!-- notificacoes.php -->
<?php
// Verificar se a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exibir mensagem de alerta, se houver
if (isset($_SESSION['mensagem'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo_mensagem']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['mensagem']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                var alertElement = document.querySelector('.alert');
                if (alertElement) {
                    var bsAlert = bootstrap.Alert.getInstance(alertElement) || new bootstrap.Alert(alertElement);
                    bsAlert.close();
                }
            }, 2600); // Tempo em milissegundos
        });
    </script>
    <?php
    // Limpar a mensagem após exibir para evitar exibi-la novamente em outras páginas
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
endif;
?>
