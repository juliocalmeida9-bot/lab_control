<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once(__DIR__ . '/auth.php');

$pageTitle = $pageTitle ?? 'CONTROL LAB';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - CONTROL LAB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="app-shell" id="appShell">
    <header class="topbar">
        <div class="topbar-left">
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu"><i class="bi bi-list"></i></button>
            <img src="../imagens/logo-senai.png" alt="SENAI" class="brand-logo">
            <div class="system-title">CONTROL LAB</div>
        </div>
        <div class="topbar-right">
            <label class="search-box">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Buscar no sistema">
            </label>
            <button class="icon-action" type="button" aria-label="Notificações"><i class="bi bi-bell"></i></button>
            <button class="icon-action" type="button" id="themeToggle" aria-label="Alternar tema"><i class="bi bi-moon-stars"></i></button>
            <div class="user-dropdown" id="userDropdown">
                <button type="button" class="avatar-btn" id="userMenuToggle"><?php echo strtoupper(substr(user_display_name(), 0, 1)); ?></button>
                <div class="dropdown-menu">
                    <a href="perfil.php"><i class="bi bi-person"></i> Perfil</a>
                    <a href="admin.php"><i class="bi bi-gear"></i> Configurações</a>
                    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>
    <div class="layout-grid">
