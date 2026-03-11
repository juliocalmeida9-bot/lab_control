<?php
$role = current_user_role();
$currentPage = $currentPage ?? '';

$menus = [
    'admin' => [
        ['Home', 'home.php', 'bi-house-door'],
        ['Equipamentos', 'equipamentos.php', 'bi-pc-display-horizontal'],
        ['Empréstimos', 'emprestimos.php', 'bi-box-arrow-up-right'],
        ['Usuários', 'usuarios.php', 'bi-people'],
        ['Relatórios', 'relatorios.php', 'bi-bar-chart'],
    ],
    'professor' => [
        ['Home', 'home.php', 'bi-house-door'],
        ['Equipamentos disponíveis', 'equipamentos.php', 'bi-pc-display-horizontal'],
        ['Solicitar empréstimo', 'emprestimos.php', 'bi-journal-plus'],
        ['Meus empréstimos', 'emprestimos.php', 'bi-clipboard-check'],
    ],
    'aluno' => [
        ['Home', 'home.php', 'bi-house-door'],
        ['Equipamentos disponíveis', 'equipamentos.php', 'bi-pc-display-horizontal'],
        ['Solicitar empréstimo', 'emprestimos.php', 'bi-journal-plus'],
        ['Meus empréstimos', 'emprestimos.php', 'bi-clipboard-check'],
    ],
];
?>
        <aside class="sidebar" id="sidebar">
            <nav>
                <?php foreach ($menus[$role] as $item): ?>
                    <a href="<?php echo $item[1]; ?>" class="sidebar-link <?php echo $currentPage === $item[1] ? 'active' : ''; ?>">
                        <i class="bi <?php echo $item[2]; ?>"></i><span><?php echo $item[0]; ?></span>
                    </a>
                <?php endforeach; ?>

                <div class="sidebar-separator"></div>
                <a href="contato.php" class="sidebar-link <?php echo $currentPage === 'contato.php' ? 'active' : ''; ?>"><i class="bi bi-telephone"></i><span>Contato</span></a>
                <a href="sobre.php" class="sidebar-link <?php echo $currentPage === 'sobre.php' ? 'active' : ''; ?>"><i class="bi bi-info-circle"></i><span>Sobre</span></a>
            </nav>

            <div class="sidebar-footer">
                <a href="perfil.php" class="sidebar-link <?php echo $currentPage === 'perfil.php' ? 'active' : ''; ?>"><i class="bi bi-person"></i><span>Perfil</span></a>
                <a href="logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Sair</span></a>
            </div>
        </aside>
        <main class="content-area">
