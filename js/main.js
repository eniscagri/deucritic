document.addEventListener('DOMContentLoaded', () => {
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('hidden');
  }
  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
  }

  document.querySelectorAll('[data-modal-target]').forEach(button => {
    button.addEventListener('click', () => {
      const modalId = button.getAttribute('data-modal-target');
      openModal(modalId);
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(button => {
    button.addEventListener('click', () => {
      const modalId = button.getAttribute('data-modal-close');
      closeModal(modalId);
    });
  });

  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  });

  const createTopicForm = document.getElementById('create-topic-form');
  if (createTopicForm) {
    createTopicForm.addEventListener('submit', e => {
      const titleInput = createTopicForm.querySelector('input[name="title"]');
      if (!titleInput.value.trim()) {
        e.preventDefault();
        alert('Lütfen bir başlık giriniz.');
      }
    });
  }

  const menuToggle = document.getElementById('menu-toggle');
  const menu = document.getElementById('menu');
  if (menuToggle && menu) {
    const closeMenu = () => {
      menu.classList.add('hidden');
      menuToggle.setAttribute('aria-expanded', 'false');
      menuToggle.setAttribute('aria-label', 'Menüyü aç');
      document.getElementById('menu-toggle-icon')?.classList.replace('fa-xmark', 'fa-bars-staggered');
      document.body.classList.remove('dc-menu-open');
    };
    menuToggle.addEventListener('click', () => {
      const open = menu.classList.contains('hidden');
      menu.classList.toggle('hidden', !open);
      menuToggle.setAttribute('aria-expanded', String(open));
      menuToggle.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
      const icon = document.getElementById('menu-toggle-icon');
      if (icon) icon.classList.toggle('fa-xmark', open), icon.classList.toggle('fa-bars-staggered', !open);
      document.body.classList.toggle('dc-menu-open', open);
    });
    menu.querySelectorAll('[data-menu-close], a').forEach(control => control.addEventListener('click', closeMenu));
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && !menu.classList.contains('hidden')) closeMenu();
    });
  }

  const searchToggle = document.getElementById('search-toggle');
  const searchPanel = document.getElementById('search-panel');
  if (searchToggle && searchPanel) {
    const searchInput = searchPanel.querySelector('input[type="search"]');
    const closeSearch = () => {
      searchPanel.classList.add('hidden');
      searchToggle.setAttribute('aria-expanded', 'false');
      searchToggle.setAttribute('aria-label', 'Aramayı aç');
      document.getElementById('search-toggle-icon')?.classList.replace('fa-xmark', 'fa-magnifying-glass');
    };
    searchToggle.addEventListener('click', () => {
      const open = searchPanel.classList.contains('hidden');
      searchPanel.classList.toggle('hidden', !open);
      searchToggle.setAttribute('aria-expanded', String(open));
      searchToggle.setAttribute('aria-label', open ? 'Aramayı kapat' : 'Aramayı aç');
      const icon = document.getElementById('search-toggle-icon');
      if (icon) icon.classList.toggle('fa-xmark', open), icon.classList.toggle('fa-magnifying-glass', !open);
      if (open) searchInput?.focus();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closeSearch();
    });
  }
});
