<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth();

$pageTitle = 'Equipamentos';
$currentPage = 'equipamentos.php';

$q = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT id, nome, codigo_equipamento, tipo, localizacao, status FROM equipamentos WHERE 1=1";
$params = [];
if ($q !== '') { $sql .= " AND (nome LIKE :q OR codigo_equipamento LIKE :q)"; $params[':q'] = "%$q%"; }
if ($categoria !== '') { $sql .= " AND tipo = :tipo"; $params[':tipo'] = $categoria; }
if ($status !== '') { $sql .= " AND status = :status"; $params[':status'] = $status; }
$sql .= " ORDER BY id DESC LIMIT 100";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categorias = $conn->query("SELECT DISTINCT tipo FROM equipamentos WHERE tipo IS NOT NULL AND tipo <> '' ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="row-between page-heading">
    <div>
        <h1>Gerenciamento de Equipamentos</h1>
        <p>Controle de cadastro, status e histórico dos ativos de laboratório.</p>
    </div>
    <a href="admin.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Equipamento</a>
</section>

<section class="card table-card">
    <form class="controls" method="GET">
        <input type="search" name="q" placeholder="Buscar equipamento" value="<?php echo htmlspecialchars($q); ?>">
        <select name="categoria">
            <option value="">Categoria</option>
            <?php foreach ($categorias as $cat): ?><option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option><?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">Status</option>
            <?php foreach (['Disponível','Emprestado','Manutenção','Em uso'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit">Filtrar</button>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>Nome</th><th>Código</th><th>Categoria</th><th>Sala</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($lista as $eq): ?>
            <tr>
                <td><?php echo htmlspecialchars($eq['nome']); ?></td>
                <td><?php echo htmlspecialchars($eq['codigo_equipamento']); ?></td>
                <td><?php echo htmlspecialchars($eq['tipo'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($eq['localizacao'] ?: '-'); ?></td>
                <td><?php echo htmlspecialchars($eq['status']); ?></td>
                <td><button class="btn btn-light" type="button">Editar</button> <button class="btn btn-light" type="button">Excluir</button> <button class="btn btn-light" type="button">Ver histórico</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
