// assets/js/script.js

document.addEventListener('DOMContentLoaded', () => {
  initCarousel();
  setupGlobalEvents();
  setupMediaFileLabel();
  setupDeleteMediaCheckboxes();
  setupTagInput();
});

// ================= CARROSSEL =================
function initCarousel() {
  const track = document.getElementById('carouselTrack');
  if (!track) return;

  const slides  = Array.from(track.children);
  const dots    = Array.from(document.querySelectorAll('.nav-dot'));
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  let currentSlide = 0;

  function updateCarousel() {
    track.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => {
      dot.classList.toggle('active', index === currentSlide);
    });
  }

  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateCarousel();
  }

  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    updateCarousel();
  }

  if (nextBtn) nextBtn.addEventListener('click', nextSlide);
  if (prevBtn) prevBtn.addEventListener('click', prevSlide);

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      currentSlide = index;
      updateCarousel();
    });
  });

  setInterval(nextSlide, 5000);
}

// ================= MODAIS (AUTH / CRIAR POST) =================
function openAuthModal() {
  const modal = document.getElementById('authModal');
  if (modal) {
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
}

function closeAuthModal() {
  const modal = document.getElementById('authModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
}

function showRegister() {
  const loginSection    = document.getElementById('loginSection');
  const registerSection = document.getElementById('registerSection');
  if (loginSection && registerSection) {
    loginSection.classList.add('register-active');
    registerSection.classList.add('active');
  }
}

function showLogin() {
  const loginSection    = document.getElementById('loginSection');
  const registerSection = document.getElementById('registerSection');
  if (loginSection && registerSection) {
    loginSection.classList.remove('register-active');
    registerSection.classList.remove('active');
  }
}

function openCreatePostModal() {
  const modal = document.getElementById('createPostModal');
  if (modal) {
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
}

function closeCreatePostModal() {
  const modal = document.getElementById('createPostModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
}

// ================= MENU DO USUÁRIO =================
function toggleUserMenu() {
  const dropdown = document.getElementById('dropdownContent');
  if (!dropdown) return;
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// ================= EVENTOS GLOBAIS =================
function setupGlobalEvents() {
  window.addEventListener('click', (e) => {
    const authModal   = document.getElementById('authModal');
    const createModal = document.getElementById('createPostModal');

    if (e.target === authModal) closeAuthModal();
    if (e.target === createModal) closeCreatePostModal();

    if (!e.target.closest('.user-dropdown')) {
      const dropdown = document.getElementById('dropdownContent');
      if (dropdown) dropdown.style.display = 'none';
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeAuthModal();
      closeCreatePostModal();
    }
  });

  window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (!header) return;
    if (window.scrollY > 10) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

    // Botão de menu mobile (hambúrguer)
  const mobileToggleBtn = document.querySelector('.mobile-toggle');
  if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', toggleMobileMenu);
  }

}

// ================= LABEL DO INPUT DE MÍDIA =================
function setupMediaFileLabel() {
  const fileInput    = document.getElementById('mediaFile');
  const fileNameSpan = document.getElementById('uploadFileName');

  if (!fileInput || !fileNameSpan) return;

  fileInput.addEventListener('change', () => {
    const files = fileInput.files;

    if (!files || files.length === 0) {
      fileNameSpan.textContent = '';
      return;
    }

    if (files.length > 5) {
      alert('Você pode enviar no máximo 5 arquivos (imagens ou vídeos) por projeto.');
      fileInput.value = '';
      fileNameSpan.textContent = '';
      return;
    }

    const names = Array.from(files).map(f => f.name);
    fileNameSpan.textContent = names.join(', ');
  });
}

// ================= MARCAR CARD DE MÍDIA PARA EXCLUSÃO =================
function setupDeleteMediaCheckboxes() {
  const checkboxes = document.querySelectorAll('.delete-media-checkbox');
  if (!checkboxes.length) return;

  checkboxes.forEach(cb => {
    const item = cb.closest('.project-media-item');
    if (!item) return;

    // Estado inicial (se algum vier marcado por algum motivo)
    if (cb.checked) {
      item.classList.add('marked-delete');
    }

    cb.addEventListener('change', () => {
      if (cb.checked) {
        item.classList.add('marked-delete');
      } else {
        item.classList.remove('marked-delete');
      }
    });
  });
}

// ================= INPUT DE HASHTAGS (CHIPS) =================
function setupTagInput() {
  const groups = document.querySelectorAll('.tag-group');
  if (!groups.length) return;

  groups.forEach(group => {
    const container = group.querySelector('.tag-input');
    const textInput = group.querySelector('input[type="text"]');
    const hidden    = group.querySelector('input[type="hidden"]');

    if (!container || !textInput || !hidden) return;

    // Lê tags iniciais do campo hidden (ex.: "js,php,react")
    let tags = [];
    if (hidden.value.trim() !== '') {
      tags = hidden.value
        .split(',')
        .map(t => t.trim())
        .filter(Boolean);
    }

    function renderTags() {
      // Remove chips antigos
      container.querySelectorAll('.tag-chip').forEach(chip => chip.remove());

      // Cria chips novos antes do textInput
      tags.forEach(tag => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `
          #${tag}
          <button type="button" class="tag-remove" data-tag="${tag}">&times;</button>
        `;
        container.insertBefore(chip, textInput);
      });

      // Atualiza valor do hidden em formato "tag1,tag2,tag3"
      hidden.value = tags.join(',');
    }

    function addTag(raw) {
      let tag = raw.replace('#', '').trim();
      if (!tag) return;

      const normalized = tag.toLowerCase();
      if (tags.map(t => t.toLowerCase()).includes(normalized)) {
        textInput.value = '';
        return;
      }

      tags.push(tag);
      textInput.value = '';
      renderTags();
    }

    // Adiciona tag ao pressionar Enter ou vírgula
    textInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag(textInput.value);
      } else if (e.key === 'Backspace' && textInput.value === '' && tags.length > 0) {
        // Backspace com campo vazio remove a última tag
        tags.pop();
        renderTags();
      }
    });

    // Adiciona tag ao sair do campo, se tiver texto
    textInput.addEventListener('blur', () => {
      if (textInput.value.trim() !== '') {
        addTag(textInput.value);
      }
    });

    // Foca no input ao clicar no container
    container.addEventListener('click', () => {
      textInput.focus();
    });

    // Remover tag ao clicar no X
    container.addEventListener('click', (e) => {
      if (e.target.classList.contains('tag-remove')) {
        const value = e.target.getAttribute('data-tag');
        tags = tags.filter(t => t !== value);
        renderTags();
      }
    });

    // Render inicial (para exibir tags existentes no editar)
    renderTags();
  });
}

// Exporta funções pro HTML (onclick)
window.openAuthModal        = openAuthModal;
window.closeAuthModal       = closeAuthModal;
window.showRegister         = showRegister;
window.showLogin            = showLogin;
window.openCreatePostModal  = openCreatePostModal;
window.closeCreatePostModal = closeCreatePostModal;
window.toggleUserMenu       = toggleUserMenu;
