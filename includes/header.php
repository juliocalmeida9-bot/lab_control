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
        <div class="brand">
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Recolher menu">
                <i class="bi bi-list"></i>
            </button>
            <img src="../imagens/logo-senai.png" alt="SENAI" class="brand-logo">
            <div>
                <strong>CONTROL LAB</strong>
            </div>
        </div>
        <div class="topbar-actions">
            <label class="search-box">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Buscar no sistema">
            </label>
            <button class="icon-action" type="button" aria-label="Notificações"><i class="bi bi-bell"></i></button>
            <div class="avatar"><?php echo strtoupper(substr(user_display_name(), 0, 1)); ?></div>
        </div>
    </header>
    <div class="layout-grid">
