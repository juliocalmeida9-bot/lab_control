document.addEventListener('DOMContentLoaded', () => {
  const shell = document.getElementById('appShell');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const themeToggle = document.getElementById('themeToggle');
  const userDropdown = document.getElementById('userDropdown');
  const userMenuToggle = document.getElementById('userMenuToggle');

  const applyTheme = (theme) => {
    document.body.classList.toggle('dark-theme', theme === 'dark');
    if (themeToggle) {
      themeToggle.innerHTML = theme === 'dark'
        ? '<i class="bi bi-sun"></i>'
        : '<i class="bi bi-moon-stars"></i>';
    }
  };

  applyTheme(localStorage.getItem('theme_preference') || 'light');

  sidebarToggle?.addEventListener('click', () => {
    shell?.classList.toggle('sidebar-open');
  });

  themeToggle?.addEventListener('click', () => {
    const nextTheme = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
    localStorage.setItem('theme_preference', nextTheme);
    applyTheme(nextTheme);
  });

  userMenuToggle?.addEventListener('click', () => userDropdown?.classList.toggle('open'));

  document.addEventListener('click', (event) => {
    if (userDropdown && !userDropdown.contains(event.target)) {
      userDropdown.classList.remove('open');
    }
  });
});
