<?php
require_once(__DIR__ . '/bootstrap.php');

function render_app_header(string $tituloPagina, string $active = 'dashboard'): void
{
    $nome = $_SESSION['usuario_nome'] ?? 'Usuário';
    $perfil = $_SESSION['usuario_perfil'] ?? 'usuario';
    $logoControl = logo_control_lab_path();

    $menu = [
        'dashboard' => ['Dashboard', 'dashboard.php'],
        'emprestimos' => ['Empréstimos', 'emprestimos.php'],
        'devolucoes' => ['Devoluções', 'devolucao.php'],
        'relatorios' => ['Relatórios', 'relatorios.php'],
        'historico' => ['Histórico de Eventos', 'historico.php'],
        'equipamentos' => ['Equipamentos', 'equipamentos.php'],
        'professor' => ['Painel do Professor', 'professor.php'],
        'admin' => ['Administração', 'admin.php'],
        'perfil' => ['Perfil do Administrador', 'perfil_admin.php']
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
        if (($key === 'admin' || $key === 'perfil' || $key === 'equipamentos' || $key === 'relatorios' || $key === 'historico') && $perfil !== 'admin') {
            continue;
        }
        if ($key === 'professor' && !profile_is_professor($perfil)) {
            continue;
        }
        if (profile_is_professor($perfil) && in_array($key, ['dashboard', 'devolucoes', 'relatorios', 'historico', 'equipamentos', 'admin', 'perfil'], true)) {
            continue;
        }
        $class = $active === $key ? 'active' : '';
        echo '<a class="' . $class . '" href="' . $item[1] . '">' . $item[0] . '</a>';
    }
    echo '</nav>';

    echo '<div class="user-bar">';
    echo '<span>' . htmlspecialchars($nome) . ' (' . htmlspecialchars($perfil) . ')</span>';
    echo '<a class="logout-btn" href="logout.php">Sair</a>';
    echo '</div>';
    echo '</header>';
}

