<?php
require 'db.php';
require 'auth.php';

$stmt = $pdo->query("
    SELECT p.*, u.name AS author_name
    FROM projects p
    JOIN users u ON u.id = p.user_id
    WHERE p.status = 1 AND u.status = 1
    ORDER BY p.created_at DESC
");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Explorar Projetos</title>

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

  <main class="container">
    <section class="projects-section">
      <h2>Projetos em Destaque</h2>
      <div class="projects-grid" id="projectsGrid">
        <?php if (empty($projects)): ?>
          <p class="no-projects">Nenhum projeto encontrado.</p>
        <?php else: ?>
          <?php foreach ($projects as $project): ?>
            <?php
            $mediaUrl = htmlspecialchars($project['image_url']);
            $ext = strtolower(pathinfo($project['image_url'], PATHINFO_EXTENSION));
            $isVideo = in_array($ext, array('mp4', 'webm', 'ogg', 'mov'));
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
  </main>

  <script src="assets/js/script.js"></script>
</body>

</html>