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
    menuToggle.addEventListener('click', () => {
      menu.classList.toggle('hidden');
    });
  }
});
