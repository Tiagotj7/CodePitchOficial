// assets/js/script.js

document.addEventListener('DOMContentLoaded', () => {
  initCarousel();
  setupGlobalEvents();
});

// ================= CARROSSEL =================
function initCarousel() {
  const track = document.getElementById('carouselTrack');
  if (!track) return;

  const slides = Array.from(track.children);
  const dots = Array.from(document.querySelectorAll('.nav-dot'));
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
  const loginSection = document.getElementById('loginSection');
  const registerSection = document.getElementById('registerSection');
  if (loginSection && registerSection) {
    loginSection.classList.add('register-active');
    registerSection.classList.add('active');
  }
}

function showLogin() {
  const loginSection = document.getElementById('loginSection');
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
  // Fecha modais ao clicar fora
  window.addEventListener('click', (e) => {
    const authModal = document.getElementById('authModal');
    const createModal = document.getElementById('createPostModal');

    if (e.target === authModal) closeAuthModal();
    if (e.target === createModal) closeCreatePostModal();

    // Fecha dropdown de usuário ao clicar fora
    if (!e.target.closest('.user-dropdown')) {
      const dropdown = document.getElementById('dropdownContent');
      if (dropdown) dropdown.style.display = 'none';
    }
  });

  // ESC fecha modais
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeAuthModal();
      closeCreatePostModal();
    }
  });

  // Efeito de header escuro ao scroll
  window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (!header) return;
    if (window.scrollY > 10) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
}

// Exporta funções pro HTML (onclick)
window.openAuthModal = openAuthModal;
window.closeAuthModal = closeAuthModal;
window.showRegister = showRegister;
window.showLogin = showLogin;
window.openCreatePostModal = openCreatePostModal;
window.closeCreatePostModal = closeCreatePostModal;
window.toggleUserMenu = toggleUserMenu;

// Mostrar nome do arquivo selecionado no upload
document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('mediaFile');
  const fileNameSpan = document.getElementById('uploadFileName');

  if (fileInput && fileNameSpan) {
    fileInput.addEventListener('change', () => {
      if (fileInput.files && fileInput.files.length > 0) {
        fileNameSpan.textContent = fileInput.files[0].name;
      } else {
        fileNameSpan.textContent = '';
      }
    });
  }
});