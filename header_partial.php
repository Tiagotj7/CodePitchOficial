<?php
// header_partial.php
require_once __DIR__ . '/auth.php';
?>
<header id="navbar">
  <div class="navbar">
    <h1>CodePitch</h1>

    <!-- Botão hambúrguer (visível só no mobile via CSS) -->
    <button class="mobile-toggle" type="button">
      ☰
    </button>

    <nav class="nav-links">
      <a href="index.php">Home</a>
      <a href="project.php">Explorar Projetos</a>
    </nav>

    <div class="user-actions">
      <?php if (!isLoggedIn()): ?>
        <button id="loginBtn" class="cta-button" onclick="openAuthModal()">
          <span class="cta-text">Entrar</span>
          <span class="cta-icon">🚀</span>
        </button>
      <?php else: ?>
        <button id="createPostBtn" class="cta-button" onclick="openCreatePostModal()">
          <span class="cta-text">Criar Post</span>
          <span class="cta-icon">✏️</span>
        </button>
        <div id="userDropdown" class="user-dropdown">
          <button class="user-button" onclick="toggleUserMenu()">
            👤 
            <span id="userNameDisplay">
              <?= htmlspecialchars(currentUserName()) ?>
              <?php if (isAdmin()): ?>
                <span style="font-size:0.75rem;color:#ffd33d;">(ADM)</span>
              <?php endif; ?>
            </span>
          </button>
          <div id="dropdownContent" class="dropdown-content">
            <button type="button" onclick="location.href='profile.php'">Meu Perfil</button>
            <form method="post" action="logout.php">
              <button type="submit">Sair</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>