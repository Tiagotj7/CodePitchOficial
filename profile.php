<?php
require 'db.php';
require 'auth.php';

// id do perfil: se não vier na URL, usa o usuário logado
if (isset($_GET['id'])) {
    $profileId = (int)$_GET['id'];
} else {
    $profileId = currentUserId();
}

if (!$profileId) {
    // ninguém logado e sem id → volta para home
    header("Location: index.php");
    exit;
}

// Busca usuário
$stmt = $pdo->prepare("
    SELECT id, name, email, status, is_admin, bio, github, linkedin, instagram, website, created_at
    FROM users
    WHERE id = ? AND status = 1
");
$stmt->execute(array($profileId));
$userProfile = $stmt->fetch();

if (!$userProfile) {
    die("Usuário não encontrado ou desativado.");
}

// Busca projetos do usuário
$stmt = $pdo->prepare("
    SELECT p.*, u.name AS author_name
    FROM projects p
    JOIN users u ON u.id = p.user_id
    WHERE p.user_id = ? AND p.status = 1
    ORDER BY p.created_at DESC
");
$stmt->execute(array($profileId));
$userProjects = $stmt->fetchAll();

$isOwnProfile = isLoggedIn() && currentUserId() === (int)$userProfile['id'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Perfil de <?= htmlspecialchars($userProfile['name']) ?> - CodePitch</title>

  <link rel="apple-touch-icon" sizes="180x180" href="src/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="src/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="src/favicon-16x16.png">
  <link rel="manifest" href="src/site.webmanifest">
  <link rel="mask-icon" href="src/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="src/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'header_partial.php'; ?>

<main class="container">
  <section class="profile-section">
    <div class="profile-header-card">
      <div class="profile-avatar">
        <?= strtoupper(substr($userProfile['name'], 0, 1)) ?>
      </div>
      <div class="profile-info">
        <h2>
          <?= htmlspecialchars($userProfile['name']) ?>
          <?php if ((int)$userProfile['is_admin'] === 1): ?>
            <span class="profile-badge-admin">ADM</span>
          <?php endif; ?>
        </h2>
        <p class="profile-email"><?= htmlspecialchars($userProfile['email']) ?></p>
        <p class="profile-bio">
          <?= $userProfile['bio'] ? nl2br(htmlspecialchars($userProfile['bio'])) : 'Nenhuma bio adicionada ainda.' ?>
        </p>
        <p class="profile-meta">
          Membro desde: <?= date('d/m/Y', strtotime($userProfile['created_at'])) ?>
        </p>
        <div class="profile-social">
          <?php if (!empty($userProfile['github'])): ?>
            <a href="<?= htmlspecialchars($userProfile['github']) ?>" target="_blank" rel="noopener" class="social-link">GitHub</a>
          <?php endif; ?>
          <?php if (!empty($userProfile['linkedin'])): ?>
            <a href="<?= htmlspecialchars($userProfile['linkedin']) ?>" target="_blank" rel="noopener" class="social-link">LinkedIn</a>
          <?php endif; ?>
          <?php if (!empty($userProfile['instagram'])): ?>
            <a href="<?= htmlspecialchars($userProfile['instagram']) ?>" target="_blank" rel="noopener" class="social-link">Instagram</a>
          <?php endif; ?>
          <?php if (!empty($userProfile['website'])): ?>
            <a href="<?= htmlspecialchars($userProfile['website']) ?>" target="_blank" rel="noopener" class="social-link">Site</a>
          <?php endif; ?>
        </div>

        <?php if ($isOwnProfile || isAdmin()): ?>
          <div style="margin-top:0.75rem;">
            <a href="edit_profile.php?id=<?= $userProfile['id'] ?>" class="btn" style="text-decoration:none;">
              Editar Perfil
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <h3 style="margin-top:2rem;">Projetos de <?= htmlspecialchars($userProfile['name']) ?></h3>

    <?php if (empty($userProjects)): ?>
      <p>Nenhum projeto publicado ainda.</p>
    <?php else: ?>
      <div class="projects-grid">
        <?php foreach ($userProjects as $project): ?>
          <?php $mediaUrl = htmlspecialchars($project['image_url']); ?>
          <div class="project-card">
            <div class="project-header">
              <div class="project-avatar">
                <?= strtoupper(substr($project['author_name'], 0, 2)) ?>
              </div>
              <div>
                <h3><?= htmlspecialchars($project['title']) ?></h3>
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
              <img src="<?= $mediaUrl ?>"
                   alt="<?= htmlspecialchars($project['title']) ?>"
                   onerror="this.src='https://via.placeholder.com/400x200?text=Imagem+indisponível'">
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
      </div>
    <?php endif; ?>

  </section>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>