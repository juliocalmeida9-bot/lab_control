# Control Lab

Projeto PHP para gerenciar retirada e devolução de equipamentos em laboratório.

## Estrutura de pastas

- `pages/` – contém todas as páginas e scripts PHP (login, dashboard, retirada, etc.).
- `includes/` – arquivos incluídos, como `config.php`.
- `css/` – folhas de estilo (atualmente apenas `style.css`).
- `js/` – scripts JavaScript (`main.js`).
- `imagens/` – imagens usadas pelo site.
- `index.php` – redireciona automaticamente para `pages/index.php`.

## Configuração

1. Aponte o document root do Apache/XAMPP para a pasta `control_lab`.
2. Acesse `http://localhost/control_lab/pages/init_db.php` para criar o banco e tabelas.
3. Use `http://localhost/control_lab/pages/create_user.php` para cadastrar um usuário inicial.
4. Abra `http://localhost/control_lab/` (será redirecionado para a tela de login).

## Notas

- Os arquivos `init_db.php` e `create_user.php` são utilitários e podem ser removidos após a instalação.
- Todas as referências a CSS/JS foram ajustadas para refletir a nova hierarquia de pastas.
- As páginas PHP dependem de `requires` para `../includes/config.php`.
