<?php
$role = current_user_role();
$currentPage = $currentPage ?? '';

$menus = [
    ['Dashboard', 'home.php', 'bi-speedometer2'],
    ['Empréstimos', 'emprestimos.php', 'bi-arrow-left-right'],
    ['Equipamentos', 'equipamentos.php', 'bi-pc-display-horizontal'],
    ['Usuários', 'usuarios.php', 'bi-people'],
    ['Turmas', 'turmas.php', 'bi-mortarboard'],
    ['Relatórios', 'relatorios.php', 'bi-bar-chart'],
    ['Configurações', 'admin.php', 'bi-gear'],
];
?>
<aside class="sidebar" id="sidebar">
    <nav>
        <div class="sidebar-title">Menu principal</div>
        <?php foreach ($menus as $item): ?>
            <?php if (($item[1] === 'usuarios.php' || $item[1] === 'admin.php' || $item[1] === 'relatorios.php') && $role !== 'admin') continue; ?>
            <a href="<?php echo $item[1]; ?>" class="sidebar-link <?php echo $currentPage === $item[1] ? 'active' : ''; ?>">
                <i class="bi <?php echo $item[2]; ?>"></i><span><?php echo $item[0]; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div>
        <a href="perfil.php" class="sidebar-link <?php echo $currentPage === 'perfil.php' ? 'active' : ''; ?>"><i class="bi bi-person-circle"></i><span>Perfil</span></a>
        <a href="logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Sair</span></a>
    </div>
</aside>
<main class="content-area">
