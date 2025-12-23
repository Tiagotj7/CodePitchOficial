<?php
require 'db.php';
require 'auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('Projeto inválido.');
}

// ===== BUSCA PROJETO ATIVO =====
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.name AS author_name
        FROM projects p
        JOIN users u ON u.id = p.user_id
        WHERE p.id = ? AND p.status = 1 AND u.status = 1
    ");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
} catch (PDOException $e) {
    error_log('ERRO PROJECT_VIEW SELECT PROJECT: ' . $e->getMessage());
    die('Erro ao carregar projeto.');
}

if (!$project) {
    die("Projeto não encontrado ou desativado.");
}

// ===== MÍDIAS DO PROJETO (IMAGENS) =====
$mediaList = [];
if (!empty($project['media_json'])) {
    $decoded = json_decode($project['media_json'], true);
    if (is_array($decoded)) {
        $mediaList = $decoded;
    }
}

if (empty($mediaList) && !empty($project['image_url'])) {
    $mediaList = [$project['image_url']];
}

$mediaList = array_slice($mediaList, 0, 5);
$mainMedia = !empty($mediaList) ? $mediaList[0] : '';

// ===== COMENTÁRIOS ATIVOS =====
try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.name AS author_name
        FROM comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.project_id = ? AND c.status = 1 AND u.status = 1
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$id]);
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('ERRO PROJECT_VIEW SELECT COMMENTS: ' . $e->getMessage());
    $comments = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($project['title']) ?> - CodePitch</title>

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

<!-- IMPORTANTE: MESMA ESTRUTURA DO INDEX -->
<section class="projects-section project-view-page">
  <h2><?= htmlspecialchars($project['title']) ?></h2>

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
             onclick="return confirm('Excluir este projeto?');">🗑️</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($mainMedia)): ?>
      <div class="project-image">
        <img src="<?= htmlspecialchars($mainMedia) ?>"
             alt="<?= htmlspecialchars($project['title']) ?>"
             onerror="this.src='https://via.placeholder.com/400x200?text=Imagem+indisponível'">
      </div>
    <?php endif; ?>

    <?php if (count($mediaList) > 1): ?>
      <div class="project-media-grid">
        <?php for ($i = 1; $i < count($mediaList); $i++): ?>
          <div class="project-media-item">
            <img src="<?= htmlspecialchars($mediaList[$i]) ?>"
                 alt="Imagem do projeto"
                 onerror="this.src='https://via.placeholder.com/150x90?text=Imagem'">
          </div>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <div class="project-description" style="margin-top:1rem;">
      <?= nl2br(htmlspecialchars($project['description'])) ?>
    </div>

    <div class="project-tags">
      <?php foreach (explode(',', $project['tags']) as $tag): ?>
        <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <h3 style="margin-top:2rem;">Comentários</h3>

  <?php if (isLoggedIn()): ?>
    <form method="post" action="add_comment.php" class="comment-form">
      <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
      <textarea name="comment" id="commentText" placeholder="Adicione um comentário..." required></textarea>
      <button type="submit" class="btn">Enviar Comentário</button>
    </form>
  <?php else: ?>
    <p>Faça login para comentar.</p>
  <?php endif; ?>

  <div class="comments-list" id="commentsList">
    <?php if (empty($comments)): ?>
      <p class="no-comments">Nenhum comentário ainda. Seja o primeiro!</p>
    <?php else: ?>
      <?php foreach ($comments as $c): ?>
        <div class="comment">
          <div class="comment-header">
            <span class="comment-author">
              <a href="profile.php?id=<?= (int)$c['user_id'] ?>" class="profile-link-inline">
                <?= htmlspecialchars($c['author_name']) ?>
              </a>
            </span>
            <?php if (isLoggedIn() && (currentUserId() == $c['user_id'] || isAdmin())): ?>
              <a class="delete-comment-btn"
                 href="delete_comment.php?id=<?= $c['id'] ?>&project_id=<?= $project['id'] ?>"
                 onclick="return confirm('Excluir este comentário?');">🗑️</a>
            <?php endif; ?>
          </div>
          <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
          <small class="comment-date"><?= htmlspecialchars($c['created_at']) ?></small>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<script src="assets/js/script.js"></script>
</body>
</html>