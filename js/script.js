document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const themeBtn = document.getElementById('toggleTheme');
  const userDrop = document.getElementById('userDropdown');
  const userToggle = document.getElementById('userMenuToggle');

  function applyTheme(theme) {
    body.classList.toggle('dark-theme', theme === 'dark');
    if (themeBtn) {
      themeBtn.innerHTML = theme === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    }
  }

  const storedTheme = localStorage.getItem('theme_preference') || 'light';
  applyTheme(storedTheme);

  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const next = body.classList.contains('dark-theme') ? 'light' : 'dark';
      localStorage.setItem('theme_preference', next);
      applyTheme(next);
      toast(`Tema ${next === 'dark' ? 'escuro' : 'claro'} ativado.`, 'success');
    });
  }

  if (userToggle) {
    userToggle.addEventListener('click', () => userDrop.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (!userDrop.contains(e.target)) userDrop.classList.remove('open');
    });
  }

  document.querySelectorAll('[data-scroll]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.querySelector(button.dataset.scroll);
      if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
  });

  document.querySelectorAll('[data-export="pdf"]').forEach((button) => {
    button.addEventListener('click', () => {
      window.print();
    });
  });

  enhanceTables();
  handleForms();
});

function toast(message, type = 'success') {
  const container = document.querySelector('.toast-container') || createToastContainer();
  const item = document.createElement('div');
  item.className = `toast ${type}`;
  item.textContent = message;
  container.appendChild(item);
  setTimeout(() => item.remove(), 2800);
}

function createToastContainer() {
  const container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

function handleForms() {
  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      const overlay = document.createElement('div');
      overlay.className = 'loading-overlay';
      overlay.innerHTML = '<div class="spinner"></div>';
      document.body.appendChild(overlay);
      setTimeout(() => overlay.remove(), 1200);
    });
  });
}

function enhanceTables() {
  document.querySelectorAll('.data-table').forEach((table) => {
    const wrapper = table.closest('.card') || table.parentElement;
    const search = wrapper.querySelector('.table-search');
    const pageSizeSelect = wrapper.querySelector('.table-page-size');
    let rows = Array.from(table.querySelectorAll('tbody tr'));
    let currentPage = 1;

    const render = () => {
      const perPage = parseInt(pageSizeSelect?.value || '10', 10);
      const query = (search?.value || '').toLowerCase();
      const filtered = rows.filter((row) => row.innerText.toLowerCase().includes(query));
      const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
      currentPage = Math.min(currentPage, totalPages);

      rows.forEach((r) => (r.style.display = 'none'));
      filtered.slice((currentPage - 1) * perPage, currentPage * perPage).forEach((r) => (r.style.display = ''));

      let pager = wrapper.querySelector('.table-pagination');
      if (!pager) {
        pager = document.createElement('div');
        pager.className = 'table-pagination';
        table.insertAdjacentElement('afterend', pager);
      }
      pager.innerHTML = `<button class="btn secondary" ${currentPage === 1 ? 'disabled' : ''}>Anterior</button><span>${currentPage}/${totalPages}</span><button class="btn secondary" ${currentPage === totalPages ? 'disabled' : ''}>Próximo</button>`;
      const [prev, next] = pager.querySelectorAll('button');
      prev.onclick = () => { currentPage -= 1; render(); };
      next.onclick = () => { currentPage += 1; render(); };
    };

    table.querySelectorAll('th').forEach((th, idx) => {
      th.addEventListener('click', () => {
        rows.sort((a, b) => a.children[idx].innerText.localeCompare(b.children[idx].innerText, 'pt-BR', { numeric: true }));
        rows.forEach((r) => table.querySelector('tbody').appendChild(r));
        render();
      });
    });

    search?.addEventListener('input', () => { currentPage = 1; render(); });
    pageSizeSelect?.addEventListener('change', () => { currentPage = 1; render(); });
    render();
  });
}
