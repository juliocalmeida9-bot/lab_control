document.addEventListener('DOMContentLoaded', () => {
  const toggleButton = document.getElementById('sidebarToggle');
  const shell = document.getElementById('appShell');

  if (!toggleButton || !shell) return;

  toggleButton.addEventListener('click', () => {
    shell.classList.toggle('sidebar-collapsed');
  });
});
