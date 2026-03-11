<?php
function render_user_dropdown(string $perfil): string
{
    $configPage = $perfil === 'admin' ? 'admin.php' : 'perfil.php';
    return '<div class="dropdown-menu">'
        . '<a href="perfil.php"><i class="bi bi-person-circle"></i> Perfil</a>'
        . '<a href="' . $configPage . '"><i class="bi bi-gear"></i> Configurações</a>'
        . '<a href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>'
        . '</div>';
}
