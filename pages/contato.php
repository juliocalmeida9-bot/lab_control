<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Contato';
$currentPage = 'contato.php';

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading"><h1>Contato</h1></section>
<div class="summary-card">
    <p><strong>Telefone:</strong> (11) 4002-8922</p>
    <p><strong>Email:</strong> controllab@senaisp.edu.br</p>
    <p><strong>Endereço:</strong> Av. Paulista, 1313 - São Paulo/SP</p>
</div>
<form class="contact-form">
    <input type="text" placeholder="Seu nome" required>
    <input type="email" placeholder="Seu email" required>
    <textarea rows="4" placeholder="Mensagem" required></textarea>
    <button type="submit">Enviar contato</button>
</form>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
