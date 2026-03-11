<?php
require_once(__DIR__ . '/bootstrap.php');

function current_user_role(): string
{
    $perfil = strtolower((string) ($_SESSION['usuario_perfil'] ?? 'aluno'));
    if (in_array($perfil, ['admin', 'professor', 'aluno'], true)) {
        return $perfil;
    }
    return 'aluno';
}

function require_auth(array $roles = ['admin', 'professor', 'aluno']): void
{
    require_login();
    $role = current_user_role();
    if (!in_array($role, $roles, true)) {
        header('Location: home.php?erro=sem_permissao');
        exit();
    }
}

function user_display_name(): string
{
    return (string) ($_SESSION['usuario_nome'] ?? 'Usuário');
}
