<?php
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../components/navbar/navbar.php');

function render_app_header(string $tituloPagina, string $active = 'dashboard'): void
{
    $nome = $_SESSION['usuario_nome'] ?? 'Usuário';
    $perfil = $_SESSION['usuario_perfil'] ?? 'usuario';
    $logoControl = logo_control_lab_path();
    $iniciais = strtoupper(substr($nome, 0, 1));

    $menu = [
        'home' => ['Home', 'index.php', 'bi-house-door'],
        'dashboard' => ['Dashboard', 'dashboard.php', 'bi-speedometer2'],
        'emprestimos' => ['Empréstimos', 'emprestimos.php', 'bi-box-arrow-up-right'],
        'equipamentos' => ['Equipamentos', 'equipamentos.php', 'bi-pc-display'],
        'usuarios' => ['Usuários', 'admin.php', 'bi-people'],
        'relatorios' => ['Relatórios', 'relatorios.php', 'bi-bar-chart'],
        'perfil' => ['Perfil', 'perfil.php', 'bi-person'],
        'config' => ['Configurações', 'admin.php', 'bi-gear']
    ];

    echo '<header class="app-header">';
    echo '<div class="brand-area">';
    echo '<img src="../imagens/logo-senai.png" class="logo senai" alt="SENAI">';
    if ($logoControl) {
        echo '<img src="' . htmlspecialchars($logoControl) . '" class="logo control" alt="Control Lab">';
    } else {
        echo '<div class="control-badge">CONTROL LAB</div>';
    }
    echo '<div><h1>CONTROL LAB</h1><p>' . htmlspecialchars($tituloPagina) . '</p></div>';
    echo '</div>';

    echo '<nav class="top-nav">';
    foreach ($menu as $key => $item) {
        if (($key === 'equipamentos' || $key === 'usuarios' || $key === 'relatorios' || $key === 'config') && $perfil !== 'admin') {
            continue;
        }
        $class = $active === $key ? 'active' : '';
        echo '<a class="' . $class . '" href="' . $item[1] . '"><i class="bi ' . $item[2] . '"></i> ' . $item[0] . '</a>';
    }
    echo '</nav>';

    echo '<div class="user-bar">';
    echo '<div class="search-wrap"><input type="search" placeholder="Buscar..."></div>';
    echo '<button class="icon-btn" type="button" aria-label="Notificações"><i class="bi bi-bell"></i></button>';
    echo '<button class="theme-toggle" id="toggleTheme" type="button" aria-label="Alternar tema"><i class="bi bi-moon-stars"></i></button>';
    echo '<div class="user-dropdown" id="userDropdown">';
    echo '<button type="button" class="avatar-btn" id="userMenuToggle">' . htmlspecialchars($iniciais) . '</button>';
    echo render_user_dropdown($perfil);
    echo '</div>';
    echo '</div>';
    echo '</header>';

    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script src="../js/script.js" defer></script>';
}
