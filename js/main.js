document.addEventListener('DOMContentLoaded', () => {
  const themeToggle = document.getElementById('loginThemeToggle');

  const applyTheme = (theme) => {
    document.body.classList.toggle('dark-theme', theme === 'dark');
    if (themeToggle) {
      themeToggle.innerHTML = theme === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    }
  };

  applyTheme(localStorage.getItem('theme_preference') || 'light');

  themeToggle?.addEventListener('click', () => {
    const nextTheme = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
    localStorage.setItem('theme_preference', nextTheme);
    applyTheme(nextTheme);
  });
});
