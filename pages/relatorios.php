<?php
session_start();
require_once(__DIR__ . '/../includes/auth.php');
ensure_schema($conn);
require_auth(['admin']);

$pageTitle = 'Relatórios';
$currentPage = 'relatorios.php';

$equipMais = $conn->query("SELECT eq.nome, COUNT(*) total FROM emprestimos e JOIN equipamentos eq ON eq.id = e.equipamento_id GROUP BY eq.nome ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$porTurma = $conn->query("SELECT turma, COUNT(*) total FROM emprestimos GROUP BY turma ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$indisp = $conn->query("SELECT status, COUNT(*) total FROM equipamentos WHERE status IN ('Emprestado','Manutenção','Em uso') GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);

$max = function(array $arr): int {
    $vals = array_map(fn($i) => (int) $i['total'], $arr);
    return max($vals ?: [1]);
};

include(__DIR__ . '/../includes/header.php');
include(__DIR__ . '/../includes/sidebar.php');
?>
<section class="page-heading">
    <h1>Relatórios</h1>
    <p>Indicadores de uso do laboratório em estilo dashboard.</p>
</section>

<section class="summary-grid">
    <article class="card"><h3>Equipamentos mais utilizados</h3><div class="chart-list"><?php $m=$max($equipMais); foreach($equipMais as $i): $p=(int)(($i['total']/$m)*100); ?><div class="chart-bar"><span><?php echo htmlspecialchars($i['nome']); ?></span><div class="chart-track"><div class="chart-fill" style="width: <?php echo $p; ?>%"></div></div><strong><?php echo (int)$i['total']; ?></strong></div><?php endforeach; ?></div></article>
    <article class="card"><h3>Empréstimos por turma</h3><div class="chart-list"><?php $m=$max($porTurma); foreach($porTurma as $i): $p=(int)(($i['total']/$m)*100); ?><div class="chart-bar"><span><?php echo htmlspecialchars($i['turma'] ?: 'Sem turma'); ?></span><div class="chart-track"><div class="chart-fill" style="width: <?php echo $p; ?>%"></div></div><strong><?php echo (int)$i['total']; ?></strong></div><?php endforeach; ?></div></article>
    <article class="card"><h3>Equipamentos indisponíveis</h3><div class="chart-list"><?php $m=$max($indisp); foreach($indisp as $i): $p=(int)(($i['total']/$m)*100); ?><div class="chart-bar"><span><?php echo htmlspecialchars($i['status']); ?></span><div class="chart-track"><div class="chart-fill" style="width: <?php echo $p; ?>%"></div></div><strong><?php echo (int)$i['total']; ?></strong></div><?php endforeach; ?></div></article>
</section>
<?php include(__DIR__ . '/../includes/footer.php'); ?>
