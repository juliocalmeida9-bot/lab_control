<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Sobre';
$currentPage = 'sobre.php';

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Sobre</h1></section>
<div class="summary-card">
    <p><strong>Descrição:</strong> O CONTROL LAB centraliza o gerenciamento de equipamentos e empréstimos dos laboratórios.</p>
    <p><strong>Objetivo:</strong> Organizar retiradas, devoluções e disponibilidade com segurança e rastreabilidade.</p>
    <p><strong>Institucional:</strong> Projeto acadêmico com identidade visual inspirada no portal SENAI-SP.</p>
</div>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
