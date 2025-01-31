<?php
// Array contendo os itens do menu, agora usando Bootstrap Icons
$menuItems = [
    ['link' => '/pages/inicio.php', 'icon' => 'bi bi-house', 'label' => 'Início'],
    ['link' => '/pages/agendamento/listar_agendamento.php', 'icon' => 'bi bi-calendar-check', 'label' => 'Agendar Paciente'],
    ['link' => '/pages/agendamento/listar_agendados.php', 'icon' => 'bi bi-list-check', 'label' => 'Pacientes Agendados'],
    ['link' => '/pages/transporte/gerenciamento_pacientes.php', 'icon' => 'bi bi-truck', 'label' => 'Transporte'],
    ['link' => '/pages/listar_unidade_prestadora.php', 'icon' => 'bi bi-building', 'label' => 'Unidade Prestadora'],
    ['link' => '/pages/regulacao/regulacao.php', 'icon' => 'bi bi-plus-square', 'label' => 'Regulação'],
    ['link' => '/pages/listar_cidadao.php', 'icon' => 'bi bi-person', 'label' => 'Cidadão'],
    ['link' => '/pages/listar_procedimento.php', 'icon' => 'bi bi-file-earmark-text', 'label' => 'Procedimento'],
    ['link' => '/pages/listar_medico.php', 'icon' => 'bi bi-person-badge', 'label' => 'Médico'],
];
?>

<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
    <?php foreach ($menuItems as $item): ?>
        <li class="nav-item">
            <a href="<?php echo $item['link']; ?>" class="nav-link">
                <i class="nav-icon <?php echo $item['icon']; ?>"></i>
                <p><?php echo $item['label']; ?></p>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
