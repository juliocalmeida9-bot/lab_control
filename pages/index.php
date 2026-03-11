<?php
session_start();
require_once(__DIR__ . '/../includes/bootstrap.php');
ensure_schema($conn);

if (isset($_SESSION['usuario_id'])) {
    if (($_SESSION['usuario_perfil'] ?? 'usuario') === 'admin') {
        header('Location: home.php');
        exit();
    }
    if (profile_is_professor($_SESSION['usuario_perfil'] ?? '')) {
        header('Location: home.php');
        exit();
    }
    header('Location: home.php');
    exit();
}
$logoControl = logo_control_lab_path();
$adminLoginSalvo = $_COOKIE['admin_login'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Control Lab - Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="home-nav">
    <a href="#login" class="active">Login</a>
    <a href="#cadastro">Cadastro</a>
    <a href="#about">Sobre</a>
    <a href="#contact">Contato</a>
    <button type="button" id="darkToggle" class="dark-toggle">Modo Escuro</button>
</nav>
<div class="login-container">
    <div class="login-card" id="login">
        <div class="login-logos">
            <img src="../imagens/logo-senai.png" alt="SENAI" class="logo senai">
            <?php if ($logoControl): ?><img src="<?php echo htmlspecialchars($logoControl); ?>" class="logo control" alt="Control Lab"><?php else: ?><span class="control-badge">CONTROL LAB</span><?php endif; ?>
        </div>
        <h1>CONTROL LAB</h1>
        <p>Sistema administrativo de controle de equipamentos</p>
        <form action="login.php" method="POST">
            <input type="text" name="id_acesso" placeholder="Login" value="<?php echo htmlspecialchars($adminLoginSalvo); ?>" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="text" name="turma" placeholder="Turma (obrigatória para professor)">
            <button type="submit" class="btn">Entrar</button>
        </form>
        
        <?php if (isset($_GET['erro']) && $_GET['erro'] !== 'turma'): ?><p class="erro">Credenciais inválidas.</p><?php endif; ?>
        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'turma'): ?><p class="erro">Professor: selecione uma turma válida para entrar.</p><?php endif; ?>
        <?php if (isset($_GET['bloqueado'])): ?><p class="erro">Usuário bloqueado.</p><?php endif; ?>
    </div>

    <div class="login-card" id="cadastro" style="display: none;">
        <div class="login-logos">
            <img src="../imagens/logo-senai.png" alt="SENAI" class="logo senai">
            <?php if ($logoControl): ?><img src="<?php echo htmlspecialchars($logoControl); ?>" class="logo control" alt="Control Lab"><?php else: ?><span class="control-badge">CONTROL LAB</span><?php endif; ?>
        </div>
        <h1>CADASTRO</h1>
        <p>Registre-se para acessar o sistema</p>
        <form action="create_user.php" method="POST">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="id_acesso" placeholder="ID de acesso" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <select name="perfil" id="perfilSelect" required>
                <option value="">Selecione o perfil</option>
                <option value="aluno">Aluno</option>
                <option value="professor">Professor</option>
            </select>
            <input type="password" name="chave_professor" id="chaveProfessor" placeholder="Chave de acesso do professor" style="display: none;">
            <input type="text" name="turma" placeholder="Turma/Aula" required>
            <input type="text" name="equipe" placeholder="Equipe" required>
            <button type="submit" class="btn">Cadastrar</button>
        </form>
        <?php if (isset($_GET['cadastro_sucesso'])): ?><p class="sucesso">Cadastro realizado com sucesso!</p><?php endif; ?>
        <?php if (isset($_GET['cadastro_erro']) && $_GET['cadastro_erro'] === 'chave'): ?><p class="erro">Chave de professor inválida.</p><?php endif; ?>
        <?php if (isset($_GET['cadastro_erro']) && $_GET['cadastro_erro'] !== 'chave'): ?><p class="erro">Erro no cadastro. Tente novamente.</p><?php endif; ?>
    </div>
</div>

<section class="summary" id="about">

    <h2>Sobre o Control Lab</h2>
    <p>O Control Lab é um sistema desenvolvido pelo SENAI para gerenciamento eficiente de equipamentos em laboratórios. Permite o controle de empréstimos, devoluções e manutenção de itens, garantindo organização e rastreabilidade. Desenvolvido com tecnologias modernas para facilitar o dia a dia dos usuários e administradores.</p>
</section>

<section class="summary" id="contact">
    <h2>Contato</h2>
    <p>Para dúvidas ou suporte, entre em contato com a equipe do SENAI através do email: suporte@senai.com.br</p>
</section>

<script src="../js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.home-nav a');
    const loginCard = document.getElementById('login');
    const cadastroCard = document.getElementById('cadastro');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href').substring(1);

            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            if (target === 'login') {
                loginCard.style.display = 'block';
                cadastroCard.style.display = 'none';
            } else if (target === 'cadastro') {
                loginCard.style.display = 'none';
                cadastroCard.style.display = 'block';
            }
        });
    });

    const perfilSelect = document.getElementById('perfilSelect');
    const chaveProfessor = document.getElementById('chaveProfessor');

    function toggleProfessorKey() {
        const isProfessor = perfilSelect && perfilSelect.value === 'professor';
        if (!chaveProfessor) {
            return;
        }
        chaveProfessor.style.display = isProfessor ? 'block' : 'none';
        chaveProfessor.required = isProfessor;
        if (!isProfessor) {
            chaveProfessor.value = '';
        }
    }

    if (perfilSelect) {
        perfilSelect.addEventListener('change', toggleProfessorKey);
        toggleProfessorKey();
    }

    // Check URL hash on load
    if (window.location.hash === '#cadastro') {
        loginCard.style.display = 'none';
        cadastroCard.style.display = 'block';
    }

    const darkToggle = document.getElementById('darkToggle');
    if (darkToggle) {
        function setDarkMode(enabled) {
            document.body.classList.toggle('dark', enabled);
            darkToggle.textContent = enabled ? 'Modo Claro' : 'Modo Escuro';
            localStorage.setItem('claro_escuro', enabled ? 'dark' : 'light');
        }

        const stored = localStorage.getItem('claro_escuro');
        setDarkMode(stored === 'dark');

        darkToggle.addEventListener('click', function() {
            setDarkMode(!document.body.classList.contains('dark'));
        });
    }
});
</script>
</body>
</html>

