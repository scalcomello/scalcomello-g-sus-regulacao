<!-- Footer -->
<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Versão 2.0
    </div>
    <strong>&copy; 2024 Prefeitura Municipal de Bandeira do Sul, MG - Secretaria Municipal de Saúde. Todos os direitos reservados.</strong>
</footer>
</div> <!-- Fechamento do .app-wrapper -->






<!-- pace-progress -->
<script src="/vendor/almasaeed2010/adminlte/plugins/pace-progress/pace.min.js"></script>

<!-- jQuery -->
<script src="/vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="/vendor/almasaeed2010/adminlte/plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="/vendor/almasaeed2010/adminlte/plugins/moment/moment.min.js"></script>
<script src="/vendor/almasaeed2010/adminlte/plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="/vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="/vendor/almasaeed2010/adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Bootstrap Switch -->
<script src="/vendor/almasaeed2010/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="/vendor/almasaeed2010/adminlte/plugins/bs-stepper/js/bs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="/vendor/almasaeed2010/adminlte/plugins/dropzone/min/dropzone.min.js"></script>
<!-- AdminLTE App -->
<script src="/vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js"></script>





<!-- Script para gerenciamento do estado da sidebar -->
<script>
    // Ao carregar a página, verificar o estado armazenado do menu lateral (sidebar)
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;

        // Verifica o estado armazenado da sidebar e ajusta
        if (localStorage.getItem('sidebar-collapse') === 'true') {
            body.classList.add('sidebar-collapse');
        }

        // Lógica de clique para armazenar o estado
        document.querySelector('[data-lte-toggle="sidebar"]').addEventListener('click', function() {
            if (body.classList.contains('sidebar-collapse')) {
                localStorage.setItem('sidebar-collapse', 'true');
            } else {
                localStorage.setItem('sidebar-collapse', 'false');
            }
        });
    });
</script>

<!-- Script para expandir as linhas da tabela (opcional se for usado em várias partes do projeto) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Expandable table rows
        document.querySelectorAll('tbody tr[data-widget="expandable-table"]').forEach(function(row) {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('.btn')) {
                    this.nextElementSibling.classList.toggle('d-none');
                }
            });
        });
    });
</script>

</body>
</html>
