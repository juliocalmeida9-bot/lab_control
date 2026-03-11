<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth(['admin']);

$pageTitle = 'Usuários';
$currentPage = 'usuarios.php';
$usuarios = $conn->query("SELECT nome, id_acesso, perfil, turma FROM usuarios ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>Gerenciamento de usuários do sistema</h1>
    <p>Administração de acessos por perfil institucional.</p>
</section>
<section class="card table-card">
    <div class="table-wrap"><table>
        <thead><tr><th>Nome</th><th>Email</th><th>Tipo de usuário</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['nome']); ?></td>
                    <td><?php echo htmlspecialchars($u['id_acesso']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($u['perfil'])); ?></td>
                    <td><button class="btn btn-light" type="button">Editar</button> <button class="btn btn-light" type="button">Excluir</button> <button class="btn btn-light" type="button">Redefinir senha</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
