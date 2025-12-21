<?php
require 'db.php';
require 'auth.php';

// ===== MENSAGENS DE ERRO/SUCESSO DE CRIAÇÃO DE PROJETO =====
$postErrorMsg   = '';
$uploadFeedback = array();
$maxUploadSize  = ini_get('upload_max_filesize'); // valor padrão

if (isset($_SESSION['post_error_msg'])) {
  $postErrorMsg = $_SESSION['post_error_msg'];
  unset($_SESSION['post_error_msg']);
}

if (isset($_SESSION['upload_feedback']) && is_array($_SESSION['upload_feedback'])) {
  $uploadFeedback = $_SESSION['upload_feedback'];
  unset($_SESSION['upload_feedback']);
}

if (isset($_SESSION['max_upload_size']) && $_SESSION['max_upload_size'] !== '') {
  $maxUploadSize = $_SESSION['max_upload_size'];
  unset($_SESSION['max_upload_size']);
}

// ===== BUSCA ÚLTIMOS PROJETOS ATIVOS =====
$stmt = $pdo->query("
    SELECT p.*, u.name AS author_name
    FROM projects p
    JOIN users u ON u.id = p.user_id
    WHERE p.status = 1 AND u.status = 1
    ORDER BY p.created_at DESC
    LIMIT 6
");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CodePitch</title>

  <link rel="apple-touch-icon" sizes="180x180" href="src/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="src/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="src/favicon-16x16.png">
  <link rel="manifest" href="src/site.webmanifest">
  <link rel="mask-icon" href="src/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="src/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">

  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <?php include 'header_partial.php'; ?>

  <section class="hero">
    <h2>Bem-vindo ao CodePitch</h2>
    <p>Conectando desenvolvedores através de projetos inovadores na tecnologia</p>
  </section>

  <section class="carousel-container">
    <div class="carousel-track" id="carouselTrack">
      <div class="carousel-slide">
        <h3>Projetos Web</h3>
        <p>Descubra aplicações web inovadoras criadas pela comunidade.</p>
      </div>
      <div class="carousel-slide">
        <h3>Apps Mobile</h3>
        <p>Explore aplicativos móveis desenvolvidos por talentos emergentes.</p>
      </div>
      <div class="carousel-slide">
        <h3>Análise de Dados</h3>
        <p>Veja projetos de ciência de dados e machine learning.</p>
      </div>
    </div>
    <div class="carousel-buttons">
      <button id="prevBtn">&#10094;</button>
      <button id="nextBtn">&#10095;</button>
    </div>
    <div class="carousel-nav">
      <span class="nav-dot active" data-slide="0"></span>
      <span class="nav-dot" data-slide="1"></span>
      <span class="nav-dot" data-slide="2"></span>
    </div>
  </section>

  <section class="projects-section">
    <h2>Projetos em Destaque</h2>
    <div class="projects-grid" id="projectsGrid">
      <?php if (empty($projects)): ?>
        <p class="no-projects">Nenhum projeto encontrado.</p>
      <?php else: ?>
        <?php foreach ($projects as $project): ?>
          <?php
          $mediaUrl = htmlspecialchars($project['image_url']);
          $ext      = strtolower(pathinfo($project['image_url'], PATHINFO_EXTENSION));
          $isVideo  = in_array($ext, array('mp4', 'webm', 'ogg', 'mov'));
          ?>
          <div class="project-card">
            <div class="project-header">
              <div class="project-avatar">
                <?= strtoupper(substr($project['author_name'], 0, 2)) ?>
              </div>
              <div>
                <h3><?= htmlspecialchars($project['title']) ?></h3>
                <div class="project-author">
                  Por <a href="profile.php?id=<?= (int)$project['user_id'] ?>" class="profile-link-inline">
                    <?= htmlspecialchars($project['author_name']) ?>
                  </a>
                </div>
                <div class="project-location">📍 <?= htmlspecialchars($project['location']) ?></div>
              </div>
              <?php if (isLoggedIn() && (currentUserId() == $project['user_id'] || isAdmin())): ?>
                <div class="project-actions-menu">
                  <a class="menu-btn" href="edit_project.php?id=<?= $project['id'] ?>">✏️</a>
                  <a class="menu-btn"
                    href="delete_project.php?id=<?= $project['id'] ?>"
                    onclick="return confirm('Tem certeza que deseja excluir este projeto?');">🗑️</a>
                </div>
              <?php endif; ?>
            </div>

            <div class="project-image">
              <?php if ($isVideo): ?>
                <video controls style="width:100%;height:100%;object-fit:cover;">
                  <source src="<?= $mediaUrl ?>" type="video/<?= $ext === 'ogv' ? 'ogg' : $ext ?>">
                  Seu navegador não suporta vídeo.
                </video>
              <?php else: ?>
                <img src="<?= $mediaUrl ?>"
                  alt="<?= htmlspecialchars($project['title']) ?>"
                  onerror="this.src='https://via.placeholder.com/400x200?text=Imagem+indisponível'">
              <?php endif; ?>
            </div>

            <div class="project-description">
              <?= nl2br(htmlspecialchars($project['description'])) ?>
            </div>
            <div class="project-actions">
              <a class="comment-btn" href="project_view.php?id=<?= $project['id'] ?>">
                💬 Comentários
              </a>
              <div class="project-tags">
                <?php foreach (explode(',', $project['tags']) as $tag): ?>
                  <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal de Criar Post -->
  <div id="createPostModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeCreatePostModal()">&times;</span>
      <h2>Criar Novo Projeto</h2>

      <?php if (!empty($postErrorMsg)): ?>
        <p style="color:#f85149;margin-bottom:0.25rem;">
          <?= htmlspecialchars($postErrorMsg) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($uploadFeedback)): ?>
        <p style="font-size:0.85rem;color:#8b949e;margin-bottom:0.25rem;">
          Tamanho máximo por arquivo: <?= htmlspecialchars($maxUploadSize) ?>.
        </p>
        <ul class="upload-feedback">
          <?php foreach ($uploadFeedback as $f): ?>
            <?php
            $status = $f['status'];
            $name   = $f['name'];
            $msg    = $f['msg'];
            if ($status === 'ok' || $status === 'url') {
              $icon  = '✅';
              $class = 'ok';
            } else {
              $icon  = '❌';
              $class = 'error';
            }
            ?>
            <li class="<?= $class ?>">
              <span class="icon"><?= $icon ?></span>
              <strong><?= htmlspecialchars($name) ?></strong> – <?= htmlspecialchars($msg) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if (!isLoggedIn()): ?>
        <p>Você precisa estar logado para criar um projeto.</p>
      <?php else: ?>
        <form id="postForm" method="post" action="create_project.php" enctype="multipart/form-data">
          <div class="input-group">
            <input type="text" id="postTitle" name="title" placeholder=" " required>
            <label>Título do Projeto</label>
          </div>
          <div class="input-group">
            <input type="text" id="postLocation" name="location" placeholder=" " required>
            <label>Localização (ex: São Paulo, SP)</label>
          </div>
          <div class="input-group">
            <input type="url" id="postImage" name="image_url" placeholder=" ">
            <label>URL de Imagem (opcional se usar upload)</label>
          </div>
          <div class="upload-row">
            <input type="file" id="mediaFile" name="media_files[]" accept="image/*" hidden multiple>
            <button type="button" class="upload-btn" onclick="document.getElementById('mediaFile').click()">
              📁 Enviar imagem (até 5)
            </button>
            <span id="uploadFileName" class="upload-file-name"></span>
          </div>
          <div class="input-group">
            <textarea id="postDescription" name="description" placeholder=" " required></textarea>
            <label>Descrição do Projeto</label>
          </div>
          <div class="input-group">
            <input type="text" id="postTags" name="tags" placeholder=" " required>
            <label>Tags (separadas por vírgula)</label>
          </div>
          <button type="submit" class="btn">Publicar Projeto</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal de Login/Cadastro -->
  <div id="authModal" class="auth-modal">
    <div class="auth-container">
      <span class="auth-close" onclick="closeAuthModal()">&times;</span>
      <div class="forms-container">
        <div class="form-section login-section active" id="loginSection">
          <div class="form-box">
            <h2 class="form-title animation" style="--i:1">Entrar</h2>
            <form id="login-form" method="post" action="login.php">
              <div class="input-group animation" style="--i:4">
                <input type="email" name="email" required />
                <label>Email</label>
              </div>
              <div class="input-group animation" style="--i:5">
                <input type="password" name="password" required />
                <label>Senha</label>
              </div>
              <button type="submit" class="btn animation" style="--i:7">Entrar</button>
            </form>
          </div>
          <div class="welcome-box">
            <h2 class="animation" style="--i:1">Olá!</h2>
            <p class="animation" style="--i:2">
              Registre-se com seus dados pessoais para usar todos os recursos.
            </p>
            <button class="btn secondary animation" style="--i:3" onclick="showRegister()">
              CADASTRAR
            </button>
          </div>
        </div>

        <div class="form-section register-section" id="registerSection">
          <div class="welcome-box">
            <h2 class="animation" style="--i:1">Bem-vindo de Volta!</h2>
            <p class="animation" style="--i:2">
              Entre com seus dados pessoais para usar todos os recursos.
            </p>
            <button class="btn secondary animation" style="--i:3" onclick="showLogin()">
              ENTRAR
            </button>
          </div>
          <div class="form-box">
            <h2 class="form-title animation" style="--i:1">Criar Conta</h2>
            <form id="register-form" method="post" action="register.php">
              <div class="input-group animation" style="--i:4">
                <input type="text" name="name" required />
                <label>Nome</label>
              </div>
              <div class="input-group animation" style="--i:5">
                <input type="email" name="email" required />
                <label>Email</label>
              </div>
              <div class="input-group animation" style="--i:6">
                <input type="password" name="password" required />
                <label>Senha</label>
              </div>
              <button type="submit" class="btn animation" style="--i:7">CADASTRAR</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/script.js"></script>

  <?php if (isset($_GET['login'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        openAuthModal();
        showLogin();
      });
    </script>
  <?php endif; ?>

  <?php if (isset($_GET['post_error'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        openCreatePostModal();
      });
    </script>
  <?php endif; ?>

</body>

</html>